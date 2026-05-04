<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FiltersByUnit;
use App\Models\BoxHydrant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoxHydrantController extends Controller
{
    use FiltersByUnit;

    public function index()
    {
        $boxHydrants = $this->getQueryForAuthUser(BoxHydrant::class)
            ->orderBy('id')
            ->get();
        return view('box-hydrant.index', compact('boxHydrants'));
    }

    public function create()
    {
        // Preview serial without incrementing counter
        $unitId = $this->getAuthUserUnitId();
        $nextSerial = BoxHydrant::generateNextSerial($unitId, false);

        // Default values
        $default = [
            'serial_no' => $nextSerial,
            'barcode' => $nextSerial,
            'status' => 'BAIK',
            'location_code' => 'Lobby Utama / Koridor',
            'type' => 'Indoor / Outdoor',
        ];

        return view('box-hydrant.create', compact('nextSerial', 'default'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_code' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'floor_plan_id' => 'nullable|exists:floor_plans,id',
            'floor_plan_x' => 'nullable|numeric|min:0|max:100',
            'floor_plan_y' => 'nullable|numeric|min:0|max:100',
        ]);

        // Generate serial and increment counter
        $unitId = $this->getAuthUserUnitId();
        $serial = BoxHydrant::generateNextSerial($unitId, true);
        $barcode = $serial;

        $boxHydrant = BoxHydrant::create([
            'user_id' => Auth::id(),
            'unit_id' => $unitId,
            'barcode' => $barcode,
            'serial_no' => $serial,
            'location_code' => $request->location_code,
            'type' => $request->type,
            'status' => $request->status,
            'notes' => $request->notes,
            'floor_plan_id' => $request->floor_plan_id,
            'floor_plan_x' => $request->floor_plan_x,
            'floor_plan_y' => $request->floor_plan_y,
        ]);

        $boxHydrant->generateQrSvg(true);

        return redirect()
            ->route('box-hydrant.index')
            ->with('success', 'Box Hydrant baru berhasil ditambahkan dengan barcode ' . $boxHydrant->serial_no);
    }

    public function edit(BoxHydrant $boxHydrant)
    {
        return view('box-hydrant.edit', compact('boxHydrant'));
    }

    public function update(Request $request, BoxHydrant $boxHydrant)
    {
        $request->validate([
            'location_code' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'floor_plan_id' => 'nullable|exists:floor_plans,id',
            'floor_plan_x' => 'nullable|numeric|min:0|max:100',
            'floor_plan_y' => 'nullable|numeric|min:0|max:100',
        ]);

        $boxHydrant->update([
            'location_code' => $request->location_code,
            'type' => $request->type,
            'status' => $request->status,
            'notes' => $request->notes,
            'floor_plan_id' => $request->floor_plan_id,
            'floor_plan_x' => $request->floor_plan_x,
            'floor_plan_y' => $request->floor_plan_y,
        ]);

        $boxHydrant->generateQrSvg(true);

        return redirect()
            ->route('box-hydrant.index')
            ->with('success', 'Data Box Hydrant ' . $boxHydrant->serial_no . ' berhasil diperbarui.');
    }

    public function riwayat(Request $request, BoxHydrant $boxHydrant)
    {
        // Check unit access
        $userUnitId = $this->getAuthUserUnitId();

        if (!auth()->user()->hasAnyRole(['superadmin', 'inspector'])) {
            if ($boxHydrant->unit_id != $userUnitId) {
                abort(403, 'Anda tidak memiliki akses ke Box Hydrant dari unit lain. QR Code ini untuk unit: ' .
                    ($boxHydrant->unit ? $boxHydrant->unit->name : 'Induk'));
            }
        }

        $query = $boxHydrant->kartuInspeksi()->with(['user', 'approver', 'signature']);

        if ($request->filled('creator')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->creator . '%');
            });
        }

        if ($request->filled('approver')) {
            $query->whereHas('approver', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->approver . '%');
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->whereNotNull('approved_at');
            } elseif ($request->status === 'pending') {
                $query->whereNull('approved_at');
            }
        }

        $riwayatInspeksi = $query->orderBy('tgl_periksa', 'desc')->get();

        return view('box-hydrant.riwayat', compact('boxHydrant', 'riwayatInspeksi'));
    }

    public function viewKartu($boxHydrantId, $kartuId)
    {
        $boxHydrant = BoxHydrant::findOrFail($boxHydrantId);

        // Check unit access
        $userUnitId = $this->getAuthUserUnitId();

        if (!auth()->user()->hasAnyRole(['superadmin', 'inspector'])) {
            if ($boxHydrant->unit_id != $userUnitId) {
                abort(403, 'Anda tidak memiliki akses ke Box Hydrant dari unit lain. Kartu ini untuk unit: ' .
                    ($boxHydrant->unit ? $boxHydrant->unit->name : 'Induk'));
            }
        }

        $kartu = \App\Models\KartuBoxHydrant::with(['signature', 'user', 'approver', 'leaderSignature', 'leaderApprover'])->findOrFail($kartuId);
        $template = \App\Models\KartuTemplate::getTemplate('box-hydrant', $boxHydrant->unit_id);

        if ($template) {
            $labelMap = [
                'No. Dokumen' => 'BH-' . str_pad($kartu->id, 4, '0', STR_PAD_LEFT),
                'Revisi' => str_pad((string) ($kartu->revisi ?? '0'), 2, '0', STR_PAD_LEFT),
                'Tanggal' => \Carbon\Carbon::parse($kartu->tgl_periksa)->format('d F Y'),
                'Halaman' => '1 dari 1',
            ];

            $headerFields = collect($template->header_fields)->map(function ($field) use ($labelMap) {
                if (isset($labelMap[$field['label']])) {
                    $field['value'] = $labelMap[$field['label']];
                }
                return $field;
            })->toArray();

            $template->header_fields = $headerFields;
        }

        return view('box-hydrant.view-kartu', compact('boxHydrant', 'kartu', 'template'));
    }

    /**
     * Form kartu pemeriksaan Box Hydrant
     */
    public function createPemeriksaan(Request $request)
    {
        $boxHydrantId = $request->query('box_hydrant_id');
        $boxHydrant   = BoxHydrant::findOrFail($boxHydrantId);

        $template = \App\Models\KartuTemplate::getTemplate('box-hydrant', $boxHydrant->unit_id);

        $latestKartu = \App\Models\KartuBoxHydrant::where('box_hydrant_id', $boxHydrantId)
            ->where(function ($q) {
                $q->whereNotNull('catatan')->where('catatan', 'like', '[PMK]%');
            })
            ->orderBy('id', 'desc')
            ->first();

        if ($latestKartu && ($latestKartu->leader_rejected_at || $latestKartu->rejected_at)) {
            $nextRevisi = str_pad((int)($latestKartu->revisi ?? 0) + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $nextRevisi = '00';
        }

        return view('box-hydrant.kartu-pemeriksaan', compact('boxHydrant', 'template', 'nextRevisi'));
    }

    /**
     * Simpan kartu pemeriksaan Box Hydrant
     */
    public function storePemeriksaan(Request $request)
    {
        $request->validate([
            'box_hydrant_id' => ['required', 'exists:box_hydrants,id'],
            'tgl_periksa'    => ['required', 'date'],
            'petugas'        => ['required', 'string', 'max:100'],
            'rows'           => ['required', 'array', 'min:1'],
            'rows.*.nama_barang' => ['nullable', 'string', 'max:255'],
            'rows.*.lokasi'      => ['nullable', 'string', 'max:255'],
            'rows.*.no_seri'     => ['nullable', 'string', 'max:100'],
            'rows.*.kondisi'     => ['nullable', 'string', 'max:50'],
            'rows.*.keterangan'  => ['nullable', 'string', 'max:255'],
        ]);

        $boxHydrant = BoxHydrant::findOrFail($request->box_hydrant_id);

        $latestKartu = \App\Models\KartuBoxHydrant::where('box_hydrant_id', $boxHydrant->id)
            ->where(function ($q) {
                $q->whereNotNull('catatan')->where('catatan', 'like', '[PMK]%');
            })
            ->orderBy('id', 'desc')
            ->first();

        $revisi = ($latestKartu && ($latestKartu->leader_rejected_at || $latestKartu->rejected_at))
            ? str_pad((int)($latestKartu->revisi ?? 0) + 1, 2, '0', STR_PAD_LEFT)
            : '00';

        $saved = 0;
        foreach ($request->rows as $row) {
            if (empty($row['kondisi'])) continue;

            $parts = [];
            if (!empty($row['nama_barang'])) $parts[] = 'Nama Barang: ' . $row['nama_barang'];
            if (!empty($row['lokasi']))      $parts[] = 'Lokasi: '      . $row['lokasi'];
            if (!empty($row['no_seri']))     $parts[] = 'No. Seri: '    . $row['no_seri'];
            if (!empty($row['keterangan']))  $parts[] = 'Ket: '         . $row['keterangan'];

            $catatan = '[PMK] ' . implode(' | ', $parts);

            \App\Models\KartuBoxHydrant::create([
                'box_hydrant_id'  => $boxHydrant->id,
                'user_id'         => auth()->id(),
                'pilar_hydrant'   => $row['kondisi'],
                'box_hydrant'     => $row['kondisi'],
                'nozzle'          => $row['kondisi'],
                'selang'          => $row['kondisi'],
                'uji_fungsi'      => $row['kondisi'],
                'kesimpulan'      => $row['kondisi'],
                'tgl_periksa'     => $request->tgl_periksa,
                'petugas'         => $request->petugas,
                'catatan'         => $catatan,
                'revisi'          => $revisi,
            ]);
            $saved++;
        }

        return redirect()
            ->route('box-hydrant.index')
            ->with('success', "Kartu Pemeriksaan Box Hydrant {$boxHydrant->serial_no} berhasil disimpan ({$saved} baris).");
    }
}
