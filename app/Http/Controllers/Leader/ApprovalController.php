<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\KartuApar;
use App\Models\Signature;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    private function getViewingUnitId(): ?int
    {
        $user = auth()->user();

        if ($user && $user->unit_id) {
            return (int) $user->unit_id;
        }

        $sessionUnitId = session('viewing_unit_id');
        if ($user && !$user->unit_id && $sessionUnitId) {
            return (int) $sessionUnitId;
        }

        return null;
    }
    public function index(Request $request)
    {
        $user = auth()->user();
        $unitId = $this->getViewingUnitId();
        $moduleFilter = $request->get('module');

        // Define all kartu models with their equipment relationships
        $models = [
            'apar' => [
                'model' => \App\Models\KartuApar::class,
                'equipment' => 'apar',
                'label' => 'APAR'
            ],
            'apat' => [
                'model' => \App\Models\KartuApat::class,
                'equipment' => 'apat',
                'label' => 'APAT'
            ],
            'apab' => [
                'model' => \App\Models\KartuApab::class,
                'equipment' => 'apab',
                'label' => 'APAB'
            ],
            'fire_alarm' => [
                'model' => \App\Models\KartuFireAlarm::class,
                'equipment' => 'fireAlarm',
                'label' => 'Fire Alarm'
            ],
            'box_hydrant' => [
                'model' => \App\Models\KartuBoxHydrant::class,
                'equipment' => 'boxHydrant',
                'label' => 'Box Hydrant'
            ],
            'rumah_pompa' => [
                'model' => \App\Models\KartuRumahPompa::class,
                'equipment' => 'rumahPompa',
                'label' => 'Rumah Pompa'
            ],
            'p3k_pemeriksaan' => [
                'model' => \App\Models\KartuP3kPemeriksaan::class,
                'equipment' => 'p3k',
                'label' => 'P3K Pemeriksaan'
            ],
            'p3k_pemakaian' => [
                'model' => \App\Models\KartuP3kPemakaian::class,
                'equipment' => 'p3k',
                'label' => 'P3K Pemakaian'
            ],
            'p3k_stock' => [
                'model' => \App\Models\KartuP3kStock::class,
                'equipment' => 'p3k',
                'label' => 'P3K Stock'
            ],
        ];

        // Collect pending approvals from all modules (or filtered module)
        $allPending = collect();

        foreach ($models as $moduleKey => $config) {
            // Skip if filter is active and doesn't match
            if ($moduleFilter && $moduleFilter !== $moduleKey) {
                continue;
            }

            $model = $config['model'];
            $equipment = $config['equipment'];
            $query = $model::with([$equipment, 'user', 'approver'])
                ->whereNull('approved_at')
                ->whereNull('rejected_at')
                ->whereNull('leader_approved_at')
                ->whereNull('leader_rejected_at');

            // P3K kartu punya unit_id langsung, tidak perlu whereHas
            if ($unitId) {
                if (str_starts_with($moduleKey, 'p3k_')) {
                    $query->where('unit_id', $unitId);
                } else {
                    $query->whereHas($equipment, function ($q) use ($unitId) {
                        $q->where('unit_id', $unitId);
                    });
                }
            }

            $pending = $query->get()
                ->map(function ($item) use ($moduleKey, $config) {
                    $item->module_type = $moduleKey;
                    $item->module_label = $config['label'];
                    return $item;
                });

            $allPending = $allPending->merge($pending);
        }

        // Sort by created_at DESC and paginate
        $pendingApprovals = $allPending
            ->sortByDesc('created_at')
            ->values();

        // Manual pagination
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;

        $paginatedItems = $pendingApprovals->slice($offset, $perPage)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $pendingApprovals->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $signatures = \App\Models\Signature::where('is_active', true)
            ->when($unitId, function ($query) use ($unitId) {
                return $query->where('unit_id', $unitId);
            })
            ->get();

        return view('leader.approvals.index', [
            'pendingApprovals' => $paginator,
            'moduleFilter' => $moduleFilter,
            'modules' => $models,
            'signatures' => $signatures,
        ]);
    }

    public function batchApprove(Request $request)
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer'],
            'items.*.module' => ['required', 'string'],
            'signature_id' => ['required', 'exists:signatures,id'],
        ]);

        $user = auth()->user();
        $unitId = $this->getViewingUnitId();
        $approvedCount = 0;

        foreach ($request->items as $item) {
            $module = $item['module'];
            $id = $item['id'];

            try {
                $config = $this->getModelConfig($module);
                $modelClass = $config['model'];
                $equipmentRelation = $config['equipment'];

                $kartu = $modelClass::with([$equipmentRelation])->find($id);
                if (!$kartu) continue;

                // Pastikan kartu ini dari unit leader
                // P3K kartu punya unit_id langsung
                if ($unitId) {
                    if (str_starts_with($module, 'p3k_')) {
                        if ($kartu->unit_id !== $unitId) {
                            continue;
                        }
                    } else {
                        if ($kartu->{$equipmentRelation}->unit_id !== $unitId) {
                            continue;
                        }
                    }
                }

                if (!$kartu->leader_approved_at) {
                    $kartu->update([
                        'leader_signature_id' => $request->signature_id,
                        'leader_approved_by' => auth()->id(),
                        'leader_approved_at' => now(),
                        'leader_rejected_by' => null,
                        'leader_rejected_at' => null,
                        'leader_rejection_reason' => null,
                    ]);
                    $approvedCount++;
                }
            } catch (\Exception $e) {
                // Ignore missing module or other errors for individual items
                continue;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menyetujui {$approvedCount} kartu kendali."
        ]);
    }

    private function getModelConfig($module)
    {
        $models = [
            'apar' => ['model' => \App\Models\KartuApar::class, 'equipment' => 'apar'],
            'apat' => ['model' => \App\Models\KartuApat::class, 'equipment' => 'apat'],
            'apab' => ['model' => \App\Models\KartuApab::class, 'equipment' => 'apab'],
            'fire_alarm' => ['model' => \App\Models\KartuFireAlarm::class, 'equipment' => 'fireAlarm'],
            'box_hydrant' => ['model' => \App\Models\KartuBoxHydrant::class, 'equipment' => 'boxHydrant'],
            'rumah_pompa' => ['model' => \App\Models\KartuRumahPompa::class, 'equipment' => 'rumahPompa'],
            'p3k_pemeriksaan' => ['model' => \App\Models\KartuP3kPemeriksaan::class, 'equipment' => 'p3k'],
            'p3k_pemakaian' => ['model' => \App\Models\KartuP3kPemakaian::class, 'equipment' => 'p3k'],
            'p3k_stock' => ['model' => \App\Models\KartuP3kStock::class, 'equipment' => 'p3k'],
        ];

        if (!isset($models[$module])) {
            abort(404, 'Module not found');
        }

        return $models[$module];
    }

    public function show($module, $id)
    {
        $user = auth()->user();
        $unitId = $this->getViewingUnitId();
        $config = $this->getModelConfig($module);
        $modelClass = $config['model'];
        $equipmentRelation = $config['equipment'];

        $kartu = $modelClass::with([$equipmentRelation, 'user', 'approver'])->findOrFail($id);

        // Pastikan kartu ini dari unit leader
        // P3K kartu punya unit_id langsung
        if ($unitId) {
            if (str_starts_with($module, 'p3k_')) {
                if ($kartu->unit_id !== $unitId) {
                    abort(403, 'Unauthorized action.');
                }
            } else {
                if ($kartu->{$equipmentRelation}->unit_id !== $unitId) {
                    abort(403, 'Unauthorized action.');
                }
            }
        }

        $signatures = Signature::where('is_active', true)
            ->when($unitId, function ($query) use ($unitId) {
                return $query->where('unit_id', $unitId);
            })
            ->get();

        return view('leader.approvals.show', compact('kartu', 'signatures', 'module', 'equipmentRelation'));
    }

    public function approve(Request $request, $module, $id)
    {
        $request->validate([
            'signature_id' => 'required|exists:signatures,id',
        ]);

        $user = auth()->user();
        $unitId = $this->getViewingUnitId();
        $config = $this->getModelConfig($module);
        $modelClass = $config['model'];
        $equipmentRelation = $config['equipment'];

        $kartu = $modelClass::with([$equipmentRelation])->findOrFail($id);

        // Pastikan kartu ini dari unit leader
        // P3K kartu punya unit_id langsung
        if ($unitId) {
            if (str_starts_with($module, 'p3k_')) {
                if ($kartu->unit_id !== $unitId) {
                    abort(403, 'Unauthorized action.');
                }
            } else {
                if ($kartu->{$equipmentRelation}->unit_id !== $unitId) {
                    abort(403, 'Unauthorized action.');
                }
            }
        }

        $kartu->update([
            'leader_signature_id' => $request->signature_id,
            'leader_approved_by' => auth()->id(),
            'leader_approved_at' => now(),
            'leader_rejected_by' => null,
            'leader_rejected_at' => null,
            'leader_rejection_reason' => null,
        ]);

        return redirect()
            ->route('leader.approvals.index')
            ->with('success', 'Kartu kendali berhasil di-approve');
    }

    public function reject(Request $request, $module, $id)
    {
        $user = auth()->user();
        $unitId = $this->getViewingUnitId();
        $config = $this->getModelConfig($module);
        $modelClass = $config['model'];
        $equipmentRelation = $config['equipment'];

        $kartu = $modelClass::with([$equipmentRelation])->findOrFail($id);

        // Pastikan kartu ini dari unit leader
        // P3K kartu punya unit_id langsung
        if ($unitId) {
            if (str_starts_with($module, 'p3k_')) {
                if ($kartu->unit_id !== $unitId) {
                    abort(403, 'Unauthorized action.');
                }
            } else {
                if ($kartu->{$equipmentRelation}->unit_id !== $unitId) {
                    abort(403, 'Unauthorized action.');
                }
            }
        }

        // Validasi rejection reason
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi',
            'rejection_reason.min' => 'Alasan penolakan minimal 10 karakter',
            'rejection_reason.max' => 'Alasan penolakan maksimal 500 karakter',
        ]);

        // Increment revisi (00 -> 01 -> 02...)
        $currentRevisi = isset($kartu->revisi) ? (int) $kartu->revisi : 0;
        $newRevisi = str_pad($currentRevisi + 1, 2, '0', STR_PAD_LEFT);

        $kartu->update([
            'revisi' => $newRevisi,
            'leader_rejected_by' => auth()->id(),
            'leader_rejected_at' => now(),
            'leader_rejection_reason' => $validated['rejection_reason'],
            'leader_signature_id' => null,
            'leader_approved_by' => null,
            'leader_approved_at' => null,
        ]);

        return redirect()
            ->route('leader.approvals.index')
            ->with('success', "Kartu kendali ditolak. Revisi sekarang: {$newRevisi}");
    }
}







