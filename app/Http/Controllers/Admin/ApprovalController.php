<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FiltersByUnit;
use App\Models\KartuApab;
use App\Models\KartuApar;
use App\Models\KartuApat;
use App\Models\KartuBoxHydrant;
use App\Models\KartuFireAlarm;
use App\Models\KartuP3kPemeriksaan;
use App\Models\KartuP3kPemakaian;
use App\Models\KartuP3kStock;
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
            'p3k-pemeriksaan', 'p3k-pemakaian', 'p3k-stock' => 'p3k',
            'p3k' => 'p3k', // backward compatibility
            default => 'apar',
        };
    }

    public function index()
    {
        $unitId = $this->getAuthUserUnitId();

        $units = Unit::orderBy('code')->get();
        $currentViewingUnit = $this->getCurrentViewingUnit();

        $aparKartu = KartuApar::with(['apar.unit', 'user', 'approver'])->whereHas('apar')
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
                // Tandai jenis: pemeriksaan jika catatan diawali "[PMK]"
                $kartu->jenis_kartu = ($kartu->catatan && str_starts_with($kartu->catatan, '[PMK]'))
                    ? 'pemeriksaan' : 'kendali';

                return $kartu;
            });

        $apatKartu = KartuApat::with(['apat.unit', 'user', 'approver'])->whereHas('apat')
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
                $kartu->jenis_kartu = 'kendali';

                return $kartu;
            });

        $apabKartu = KartuApab::with(['apab.unit', 'user', 'approver'])->whereHas('apab')
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
                $kartu->jenis_kartu = 'kendali';

                return $kartu;
            });

        $fireAlarmKartu = KartuFireAlarm::with(['fireAlarm.unit', 'user', 'approver'])->whereHas('fireAlarm')
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
                $kartu->jenis_kartu = 'kendali';

                return $kartu;
            });

        $boxHydrantKartu = KartuBoxHydrant::with(['boxHydrant.unit', 'user', 'approver'])->whereHas('boxHydrant')
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
                $kartu->jenis_kartu = 'kendali';

                return $kartu;
            });

        $rumahPompaKartu = KartuRumahPompa::with(['rumahPompa.unit', 'user', 'approver'])->whereHas('rumahPompa')
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
                $kartu->jenis_kartu = 'kendali';

                return $kartu;
            });

        $p3kPemeriksaanKartu = KartuP3kPemeriksaan::with(['p3k', 'user', 'approver'])
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->whereNull('leader_rejected_at')
            ->when($unitId, function ($query) use ($unitId) {
                $query->where('unit_id', $unitId);
            })
            ->get()
            ->map(function ($kartu) {
                $kartu->module = 'P3K';
                $kartu->equipment_name = $kartu->p3k ? ($kartu->p3k->serial_no ?? '-') : $kartu->nomor_kartu;
                $unit = $kartu->p3k?->unit ?? \App\Models\Unit::find($kartu->unit_id);
                $kartu->unit_label = $unit ? "{$unit->code} - {$unit->name}" : '-';
                $kartu->route_show = route('admin.approvals.show', ['id' => $kartu->id, 'type' => 'p3k-pemeriksaan']);
                $kartu->jenis_kartu = 'pemeriksaan';

                return $kartu;
            });

        $p3kPemakaianKartu = KartuP3kPemakaian::with(['p3k', 'user', 'approver'])
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->whereNull('leader_rejected_at')
            ->when($unitId, function ($query) use ($unitId) {
                $query->where('unit_id', $unitId);
            })
            ->get()
            ->map(function ($kartu) {
                $kartu->module = 'P3K';
                $kartu->equipment_name = $kartu->p3k ? ($kartu->p3k->serial_no ?? '-') : $kartu->nomor_kartu;
                $unit = $kartu->p3k?->unit ?? \App\Models\Unit::find($kartu->unit_id);
                $kartu->unit_label = $unit ? "{$unit->code} - {$unit->name}" : '-';
                $kartu->route_show = route('admin.approvals.show', ['id' => $kartu->id, 'type' => 'p3k-pemakaian']);
                $kartu->jenis_kartu = 'pemakaian';

                return $kartu;
            });

        $p3kStockKartu = KartuP3kStock::with(['p3k', 'user', 'approver'])
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->whereNull('leader_rejected_at')
            ->when($unitId, function ($query) use ($unitId) {
                $query->where('unit_id', $unitId);
            })
            ->get()
            ->map(function ($kartu) {
                $kartu->module = 'P3K';
                $kartu->equipment_name = $kartu->p3k ? ($kartu->p3k->serial_no ?? '-') : $kartu->nomor_kartu;
                $unit = $kartu->p3k?->unit ?? \App\Models\Unit::find($kartu->unit_id);
                $kartu->unit_label = $unit ? "{$unit->code} - {$unit->name}" : '-';
                $kartu->route_show = route('admin.approvals.show', ['id' => $kartu->id, 'type' => 'p3k-stock']);
                $kartu->jenis_kartu = 'stock';

                return $kartu;
            });

        // Filter jenis (kendali / pemeriksaan) dan modul dari request
        $filterJenis = request('jenis_kartu');
        $filterModul = request('filter_modul');

        $pendingApprovals = $aparKartu
            ->concat($apatKartu)
            ->concat($apabKartu)
            ->concat($fireAlarmKartu)
            ->concat($boxHydrantKartu)
            ->concat($rumahPompaKartu)
            ->concat($p3kPemeriksaanKartu)
            ->concat($p3kPemakaianKartu)
            ->concat($p3kStockKartu)
            ->when($filterJenis, fn($c) => $c->filter(fn($k) => ($k->jenis_kartu ?? 'kendali') === $filterJenis))
            ->when($filterModul, fn($c) => $c->filter(fn($k) => strtolower($k->module) === strtolower($filterModul)))
            ->sortByDesc('created_at');

        $signatures = \App\Models\Signature::where('is_active', true)->get();

        return view('admin.approvals.index', compact('pendingApprovals', 'units', 'currentViewingUnit', 'unitId', 'signatures'));
    }

    public function batchApprove(Request $request)
    {
        try {
            $validated = $request->validate([
                'items' => ['required', 'array', 'min:1'],
                'items.*.id' => ['required', 'integer'],
                'items.*.type' => ['required', 'string'],
                'signature_id' => ['required', 'exists:signatures,id'],
            ]);

            $unitId = $this->getAuthUserUnitId();
            $approvedCount = 0;
            $skippedCount = 0;
            $failedCount = 0;
            $skippedReasons = [];
            $failedItems = [];

            // Use database transaction for data consistency
            \DB::beginTransaction();

            foreach ($validated['items'] as $item) {
                try {
                    $type = $item['type'];
                    $id = $item['id'];
                    $equipmentRelation = $this->equipmentRelationForType($type);

                    $kartu = match ($type) {
                        'apar' => \App\Models\KartuApar::with([$equipmentRelation])->find($id),
                        'apat' => \App\Models\KartuApat::with([$equipmentRelation])->find($id),
                        'apab' => \App\Models\KartuApab::with([$equipmentRelation])->find($id),
                        'fire-alarm' => \App\Models\KartuFireAlarm::with([$equipmentRelation])->find($id),
                        'box-hydrant' => \App\Models\KartuBoxHydrant::with([$equipmentRelation])->find($id),
                        'rumah-pompa' => \App\Models\KartuRumahPompa::with([$equipmentRelation])->find($id),
                        'p3k-pemeriksaan' => \App\Models\KartuP3kPemeriksaan::with([$equipmentRelation])->find($id),
                        'p3k-pemakaian' => \App\Models\KartuP3kPemakaian::with([$equipmentRelation])->find($id),
                        'p3k-stock' => \App\Models\KartuP3kStock::with([$equipmentRelation])->find($id),
                        'p3k' => \App\Models\KartuP3kPemeriksaan::with([$equipmentRelation])->find($id), // backward compatibility
                        default => \App\Models\KartuApar::with([$equipmentRelation])->find($id),
                    };

                    // Check if kartu exists
                    if (!$kartu) {
                        $skippedCount++;
                        $skippedReasons[] = "ID #{$id} ({$type}): Kartu tidak ditemukan";
                        \Log::warning("Batch Approve: Kartu not found", ['id' => $id, 'type' => $type]);
                        continue;
                    }

                    // Check unit access (superadmin bypass)
                    if ($unitId && !auth()->user()->hasRole('superadmin')) {
                        // P3K kartu punya unit_id langsung
                        if (str_starts_with($type, 'p3k-')) {
                            if ((int) $kartu->unit_id !== (int) $unitId) {
                                $skippedCount++;
                                $skippedReasons[] = "ID #{$id} ({$type}): Tidak memiliki akses ke unit ini";
                                \Log::warning("Batch Approve: Unit access denied", [
                                    'id' => $id,
                                    'type' => $type,
                                    'user_unit' => $unitId,
                                    'kartu_unit' => $kartu->unit_id
                                ]);
                                continue;
                            }
                        } else {
                            if ((int) $kartu->{$equipmentRelation}->unit_id !== (int) $unitId) {
                                $skippedCount++;
                                $skippedReasons[] = "ID #{$id} ({$type}): Tidak memiliki akses ke unit ini";
                                \Log::warning("Batch Approve: Unit access denied", [
                                    'id' => $id,
                                    'type' => $type,
                                    'user_unit' => $unitId,
                                    'kartu_unit' => $kartu->{$equipmentRelation}->unit_id
                                ]);
                                continue;
                            }
                        }
                    }

                    // Check if rejected by leader
                    if ($kartu->leader_rejected_at) {
                        $skippedCount++;
                        $skippedReasons[] = "ID #{$id} ({$type}): Sudah di-reject oleh leader";
                        \Log::info("Batch Approve: Already rejected by leader", ['id' => $id, 'type' => $type]);
                        continue;
                    }

                    // Check if already approved
                    if ($kartu->approved_at) {
                        $skippedCount++;
                        $skippedReasons[] = "ID #{$id} ({$type}): Sudah di-approve sebelumnya";
                        \Log::info("Batch Approve: Already approved", ['id' => $id, 'type' => $type]);
                        continue;
                    }

                    // Perform approval
                    $kartu->update([
                        'signature_id' => $validated['signature_id'],
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'rejected_by' => null,
                        'rejected_at' => null,
                        'rejection_reason' => null,
                    ]);

                    $approvedCount++;
                    \Log::info("Batch Approve: Success", [
                        'id' => $id,
                        'type' => $type,
                        'approved_by' => auth()->id()
                    ]);

                } catch (\Exception $e) {
                    $failedCount++;
                    $failedItems[] = "ID #{$id} ({$type}): {$e->getMessage()}";
                    \Log::error("Batch Approve: Item failed", [
                        'id' => $id,
                        'type' => $type,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Continue with other items even if one fails
                    continue;
                }
            }

            // Commit transaction
            \DB::commit();

            // Prepare response message
            $message = "Berhasil meng-approve {$approvedCount} kartu kendali.";
            
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} item di-skip.";
            }
            
            if ($failedCount > 0) {
                $message .= " {$failedCount} item gagal diproses.";
            }

            // Log summary
            \Log::info("Batch Approve: Summary", [
                'total_items' => count($validated['items']),
                'approved' => $approvedCount,
                'skipped' => $skippedCount,
                'failed' => $failedCount,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'details' => [
                    'approved' => $approvedCount,
                    'skipped' => $skippedCount,
                    'failed' => $failedCount,
                    'total' => count($validated['items']),
                    'skipped_reasons' => $skippedReasons,
                    'failed_items' => $failedItems
                ]
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            
            \Log::error("Batch Approve: Fatal error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses batch approval: ' . $e->getMessage()
            ], 500);
        }
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
            'p3k-pemeriksaan' => KartuP3kPemeriksaan::with([$equipmentRelation, 'user', 'approver'])->findOrFail($id),
            'p3k-pemakaian' => KartuP3kPemakaian::with([$equipmentRelation, 'user', 'approver'])->findOrFail($id),
            'p3k-stock' => KartuP3kStock::with([$equipmentRelation, 'user', 'approver'])->findOrFail($id),
            'p3k' => KartuP3kPemeriksaan::with([$equipmentRelation, 'user', 'approver'])->findOrFail($id),
            default => KartuApar::with([$equipmentRelation, 'user', 'approver'])->findOrFail($id),
        };

        // Superadmin bisa akses semua unit; unit filter hanya berlaku untuk non-superadmin
        if ($unitId && !auth()->user()->hasRole('superadmin')) {
            // P3K kartu punya unit_id langsung
            if (str_starts_with($type, 'p3k-')) {
                if ((int) $kartu->unit_id !== (int) $unitId) {
                    abort(403, 'Unauthorized action.');
                }
            } else {
                if ((int) $kartu->{$equipmentRelation}->unit_id !== (int) $unitId) {
                    abort(403, 'Unauthorized action.');
                }
            }
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

        // Superadmin bisa akses semua unit
        if ($unitId && !auth()->user()->hasRole('superadmin')) {
            if ((int) $kartu->{$equipmentRelation}->unit_id !== (int) $unitId) {
                abort(403, 'Unauthorized action.');
            }
        }

        if ($kartu->leader_rejected_at) {
            return back()->with('error', 'Kartu sudah di-reject oleh leader.');
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

        // Superadmin bisa akses semua unit
        if ($unitId && !auth()->user()->hasRole('superadmin')) {
            if ((int) $kartu->{$equipmentRelation}->unit_id !== (int) $unitId) {
                abort(403, 'Unauthorized action.');
            }
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
        try {
            $lastChecked = $request->query('last_checked');
            
            // Validate and parse the timestamp
            if (!$lastChecked) {
                return response()->json([
                    'has_new' => false,
                    'count' => 0,
                    'total_pending' => 0,
                    'timestamp' => now()->toIso8601String()
                ]);
            }

            // Clean up timestamp - replace space with + if needed (URL decoding issue)
            $lastChecked = str_replace(' ', '+', $lastChecked);

            // Try to parse the timestamp with error handling
            try {
                $lastCheckedTime = \Carbon\Carbon::parse($lastChecked);
            } catch (\Exception $e) {
                \Log::warning('Invalid timestamp format in checkNew', [
                    'last_checked' => $lastChecked,
                    'original' => $request->query('last_checked'),
                    'error' => $e->getMessage()
                ]);
                
                // Return success with no new data instead of error to prevent frontend errors
                return response()->json([
                    'has_new' => false,
                    'count' => 0,
                    'total_pending' => 0,
                    'timestamp' => now()->toIso8601String()
                ]);
            }

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
                try {
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
                } catch (\Exception $e) {
                    \Log::error("Error counting new {$type} approvals", [
                        'error' => $e->getMessage(),
                        'type' => $type
                    ]);
                    // Continue with other models even if one fails
                    continue;
                }
            }

            // Calculate total pending for the badge
            $totalPending = 0;
            foreach ($models as $type => [$modelClass, $relation]) {
                try {
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
                } catch (\Exception $e) {
                    \Log::error("Error counting total pending {$type} approvals", [
                        'error' => $e->getMessage(),
                        'type' => $type
                    ]);
                    // Continue with other models
                    continue;
                }
            }

            return response()->json([
                'has_new' => $newCount > 0,
                'count' => $newCount,
                'total_pending' => $totalPending,
                'timestamp' => now()->toIso8601String()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Unexpected error in checkNew', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'has_new' => false,
                'count' => 0,
                'total_pending' => 0,
                'timestamp' => now()->toIso8601String(),
                'error' => 'Internal server error'
            ], 500);
        }
    }
}
