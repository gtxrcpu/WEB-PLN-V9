<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FiltersByUnit;
use App\Models\KartuApab;
use App\Models\KartuApar;
use App\Models\KartuApat;
use App\Models\KartuBoxHydrant;
use App\Models\KartuFireAlarm;
use App\Models\KartuP3k;
use App\Models\KartuRumahPompa;
use App\Models\Signature;
use App\Models\Unit;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    use FiltersByUnit;

    private function equipmentRelationForType(string $type): string
    {
        return match ($type) {
            'apar' => 'apar',
            'apat' => 'apat',
            'apab' => 'apab',
            'fire-alarm' => 'fireAlarm',
            'box-hydrant' => 'boxHydrant',
            'rumah-pompa' => 'rumahPompa',
            'p3k' => 'p3k',
            default => 'apar',
        };
    }

    public function index()
    {
        $unitId = $this->getAuthUserUnitId();

        $units = Unit::orderBy('code')->get();
        $currentViewingUnit = $this->getCurrentViewingUnit();

        $aparKartu = KartuApar::with(['apar.unit', 'user', 'approver'])
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->whereNull('leader_rejected_at')
            ->when($unitId, function ($query) use ($unitId) {
                $query->whereHas('apar', function ($q) use ($unitId) {
                    $q->where('unit_id', $unitId);
                });
            })
            ->get()
            ->map(function ($kartu) {
                $kartu->module = 'APAR';
                $kartu->equipment_name = $kartu->apar->serial_no ?? '-';
                $unit = $kartu->apar?->unit;
                $kartu->unit_label = $unit ? "{$unit->code} - {$unit->name}" : '-';
                $kartu->route_show = route('admin.approvals.show', ['id' => $kartu->id, 'type' => 'apar']);

                return $kartu;
            });

        $apatKartu = KartuApat::with(['apat.unit', 'user', 'approver'])
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->whereNull('leader_rejected_at')
            ->when($unitId, function ($query) use ($unitId) {
                $query->whereHas('apat', function ($q) use ($unitId) {
                    $q->where('unit_id', $unitId);
                });
            })
            ->get()
            ->map(function ($kartu) {
                $kartu->module = 'APAT';
                $kartu->equipment_name = $kartu->apat->barcode ?? $kartu->apat->serial_no ?? '-';
                $unit = $kartu->apat?->unit;
                $kartu->unit_label = $unit ? "{$unit->code} - {$unit->name}" : '-';
                $kartu->route_show = route('admin.approvals.show', ['id' => $kartu->id, 'type' => 'apat']);

                return $kartu;
            });

        $apabKartu = KartuApab::with(['apab.unit', 'user', 'approver'])
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->whereNull('leader_rejected_at')
            ->when($unitId, function ($query) use ($unitId) {
                $query->whereHas('apab', function ($q) use ($unitId) {
                    $q->where('unit_id', $unitId);
                });
            })
            ->get()
            ->map(function ($kartu) {
                $kartu->module = 'APAB';
                $kartu->equipment_name = $kartu->apab->barcode ?? $kartu->apab->serial_no ?? '-';
                $unit = $kartu->apab?->unit;
                $kartu->unit_label = $unit ? "{$unit->code} - {$unit->name}" : '-';
                $kartu->route_show = route('admin.approvals.show', ['id' => $kartu->id, 'type' => 'apab']);

                return $kartu;
            });

        $fireAlarmKartu = KartuFireAlarm::with(['fireAlarm.unit', 'user', 'approver'])
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->whereNull('leader_rejected_at')
            ->when($unitId, function ($query) use ($unitId) {
                $query->whereHas('fireAlarm', function ($q) use ($unitId) {
                    $q->where('unit_id', $unitId);
                });
            })
            ->get()
            ->map(function ($kartu) {
                $kartu->module = 'Fire Alarm';
                $kartu->equipment_name = $kartu->fireAlarm->barcode ?? $kartu->fireAlarm->serial_no ?? '-';
                $unit = $kartu->fireAlarm?->unit;
                $kartu->unit_label = $unit ? "{$unit->code} - {$unit->name}" : '-';
                $kartu->route_show = route('admin.approvals.show', ['id' => $kartu->id, 'type' => 'fire-alarm']);

                return $kartu;
            });

        $boxHydrantKartu = KartuBoxHydrant::with(['boxHydrant.unit', 'user', 'approver'])
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->whereNull('leader_rejected_at')
            ->when($unitId, function ($query) use ($unitId) {
                $query->whereHas('boxHydrant', function ($q) use ($unitId) {
                    $q->where('unit_id', $unitId);
                });
            })
            ->get()
            ->map(function ($kartu) {
                $kartu->module = 'Box Hydrant';
                $kartu->equipment_name = $kartu->boxHydrant->barcode ?? $kartu->boxHydrant->serial_no ?? '-';
                $unit = $kartu->boxHydrant?->unit;
                $kartu->unit_label = $unit ? "{$unit->code} - {$unit->name}" : '-';
                $kartu->route_show = route('admin.approvals.show', ['id' => $kartu->id, 'type' => 'box-hydrant']);

                return $kartu;
            });

        $rumahPompaKartu = KartuRumahPompa::with(['rumahPompa.unit', 'user', 'approver'])
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->whereNull('leader_rejected_at')
            ->when($unitId, function ($query) use ($unitId) {
                $query->whereHas('rumahPompa', function ($q) use ($unitId) {
                    $q->where('unit_id', $unitId);
                });
            })
            ->get()
            ->map(function ($kartu) {
                $kartu->module = 'Rumah Pompa';
                $kartu->equipment_name = $kartu->rumahPompa->barcode ?? $kartu->rumahPompa->serial_no ?? '-';
                $unit = $kartu->rumahPompa?->unit;
                $kartu->unit_label = $unit ? "{$unit->code} - {$unit->name}" : '-';
                $kartu->route_show = route('admin.approvals.show', ['id' => $kartu->id, 'type' => 'rumah-pompa']);

                return $kartu;
            });

        $p3kKartu = KartuP3k::with(['p3k.unit', 'user', 'approver'])
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->whereNull('leader_rejected_at')
            ->when($unitId, function ($query) use ($unitId) {
                $query->whereHas('p3k', function ($q) use ($unitId) {
                    $q->where('unit_id', $unitId);
                });
            })
            ->get()
            ->map(function ($kartu) {
                $kartu->module = 'P3K';
                $kartu->equipment_name = $kartu->p3k->barcode ?? $kartu->p3k->serial_no ?? '-';
                $unit = $kartu->p3k?->unit;
                $kartu->unit_label = $unit ? "{$unit->code} - {$unit->name}" : '-';
                $kartu->route_show = route('admin.approvals.show', ['id' => $kartu->id, 'type' => 'p3k']);

                return $kartu;
            });

        $pendingApprovals = $aparKartu
            ->concat($apatKartu)
            ->concat($apabKartu)
            ->concat($fireAlarmKartu)
            ->concat($boxHydrantKartu)
            ->concat($rumahPompaKartu)
            ->concat($p3kKartu)
            ->sortByDesc('created_at');

        return view('admin.approvals.index', compact('pendingApprovals', 'units', 'currentViewingUnit', 'unitId'));
    }

    public function show(Request $request, $id)
    {
        $type = $request->query('type', 'apar');
        $unitId = $this->getAuthUserUnitId();
        $equipmentRelation = $this->equipmentRelationForType($type);

        $kartu = match ($type) {
            'apar' => KartuApar::with([$equipmentRelation, 'user', 'approver'])->findOrFail($id),
            'apat' => KartuApat::with([$equipmentRelation, 'user', 'approver'])->findOrFail($id),
            'apab' => KartuApab::with([$equipmentRelation, 'user', 'approver'])->findOrFail($id),
            'fire-alarm' => KartuFireAlarm::with([$equipmentRelation, 'user', 'approver'])->findOrFail($id),
            'box-hydrant' => KartuBoxHydrant::with([$equipmentRelation, 'user', 'approver'])->findOrFail($id),
            'rumah-pompa' => KartuRumahPompa::with([$equipmentRelation, 'user', 'approver'])->findOrFail($id),
            'p3k' => KartuP3k::with([$equipmentRelation, 'user', 'approver'])->findOrFail($id),
            default => KartuApar::with([$equipmentRelation, 'user', 'approver'])->findOrFail($id),
        };

        if ($unitId && $kartu->{$equipmentRelation}->unit_id !== $unitId) {
            abort(403, 'Unauthorized action.');
        }

        $kartu->module_type = $type;
        $signatures = Signature::where('is_active', true)->get();

        return view('admin.approvals.show', compact('kartu', 'signatures', 'type'));
    }

    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'signature_id' => ['required', 'exists:signatures,id'],
            'type' => ['required', 'string'],
        ]);

        $type = $validated['type'];
        $unitId = $this->getAuthUserUnitId();
        $equipmentRelation = $this->equipmentRelationForType($type);

        $kartu = match ($type) {
            'apar' => KartuApar::with([$equipmentRelation])->findOrFail($id),
            'apat' => KartuApat::with([$equipmentRelation])->findOrFail($id),
            'apab' => KartuApab::with([$equipmentRelation])->findOrFail($id),
            'fire-alarm' => KartuFireAlarm::with([$equipmentRelation])->findOrFail($id),
            'box-hydrant' => KartuBoxHydrant::with([$equipmentRelation])->findOrFail($id),
            'rumah-pompa' => KartuRumahPompa::with([$equipmentRelation])->findOrFail($id),
            'p3k' => KartuP3k::with([$equipmentRelation])->findOrFail($id),
            default => KartuApar::with([$equipmentRelation])->findOrFail($id),
        };

        if ($unitId && $kartu->{$equipmentRelation}->unit_id !== $unitId) {
            abort(403, 'Unauthorized action.');
        }

        if (!$kartu->leader_approved_at || $kartu->leader_rejected_at) {
            return back()->with('error', 'Kartu belum di-approve oleh leader.');
        }

        $kartu->update([
            'signature_id' => $validated['signature_id'],
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        return redirect()
            ->route('admin.approvals.index')
            ->with('success', 'Kartu kendali berhasil di-approve');
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'rejection_reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi',
            'rejection_reason.min' => 'Alasan penolakan minimal 10 karakter',
            'rejection_reason.max' => 'Alasan penolakan maksimal 500 karakter',
        ]);

        $type = $validated['type'];
        $unitId = $this->getAuthUserUnitId();
        $equipmentRelation = $this->equipmentRelationForType($type);

        $kartu = match ($type) {
            'apar' => KartuApar::with([$equipmentRelation])->findOrFail($id),
            'apat' => KartuApat::with([$equipmentRelation])->findOrFail($id),
            'apab' => KartuApab::with([$equipmentRelation])->findOrFail($id),
            'fire-alarm' => KartuFireAlarm::with([$equipmentRelation])->findOrFail($id),
            'box-hydrant' => KartuBoxHydrant::with([$equipmentRelation])->findOrFail($id),
            'rumah-pompa' => KartuRumahPompa::with([$equipmentRelation])->findOrFail($id),
            'p3k' => KartuP3k::with([$equipmentRelation])->findOrFail($id),
            default => KartuApar::with([$equipmentRelation])->findOrFail($id),
        };

        if ($unitId && $kartu->{$equipmentRelation}->unit_id !== $unitId) {
            abort(403, 'Unauthorized action.');
        }

        $currentRevisi = isset($kartu->revisi) ? (int) $kartu->revisi : 0;
        $newRevisi = str_pad($currentRevisi + 1, 2, '0', STR_PAD_LEFT);

        $kartu->update([
            'revisi' => $newRevisi,
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
            'leader_signature_id' => null,
            'leader_approved_by' => null,
            'leader_approved_at' => null,
            'leader_rejected_by' => null,
            'leader_rejected_at' => null,
            'leader_rejection_reason' => null,
            'signature_id' => null,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()
            ->route('admin.approvals.index')
            ->with('success', "Kartu kendali ditolak. Revisi sekarang: {$newRevisi}");
    }

    public function checkNew(Request $request)
    {
        $lastChecked = $request->query('last_checked');
        if (!$lastChecked) {
            return response()->json(['has_new' => false]);
        }

        // Convert format ISO 8601 string to Carbon instance if needed,
        // or just use directly if the database driver supports comparison with ISO strings.
        // Usually safer to parse with Carbon to ensure timezone consistency.
        $lastCheckedTime = \Carbon\Carbon::parse($lastChecked);
        $unitId = $this->getAuthUserUnitId();

        $models = [
            'apar' => [KartuApar::class, 'apar'],
            'apat' => [KartuApat::class, 'apat'],
            'apab' => [KartuApab::class, 'apab'],
            'fire-alarm' => [KartuFireAlarm::class, 'fireAlarm'],
            'box-hydrant' => [KartuBoxHydrant::class, 'boxHydrant'],
            'rumah-pompa' => [KartuRumahPompa::class, 'rumahPompa'],
            'p3k' => [KartuP3k::class, 'p3k'],
        ];

        $newCount = 0;

        foreach ($models as $type => [$modelClass, $relation]) {
            $query = $modelClass::query()
                ->where('created_at', '>', $lastCheckedTime)
                ->whereNull('approved_at')
                ->whereNull('rejected_at')
                ->whereNull('leader_rejected_at');

            // Apply unit filter
            if ($unitId) {
                $query->whereHas($relation, function ($q) use ($unitId) {
                    $q->where('unit_id', $unitId);
                });
            }

            $count = $query->count();
            if ($count > 0) {
                $newCount += $count;
            }
        }

        // Calculate total pending for the badge
        $totalPending = 0;
        foreach ($models as $type => [$modelClass, $relation]) {
            $q = $modelClass::query()
                ->whereNull('approved_at')
                ->whereNull('rejected_at')
                ->whereNull('leader_rejected_at');

            if ($unitId) {
                $q->whereHas($relation, function ($sq) use ($unitId) {
                    $sq->where('unit_id', $unitId);
                });
            }
            $totalPending += $q->count();
        }

        return response()->json([
            'has_new' => $newCount > 0,
            'count' => $newCount,
            'total_pending' => $totalPending,
            'timestamp' => now()->toIso8601String()
        ]);
    }
}
