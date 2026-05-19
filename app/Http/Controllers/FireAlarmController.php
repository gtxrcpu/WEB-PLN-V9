<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FiltersByUnit;
use App\Models\FireAlarm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FireAlarmController extends Controller
{
    use FiltersByUnit;

    /**
     * List semua Fire Alarm (filtered by unit)
     */
    public function index()
    {
        $fireAlarms = $this->getQueryForAuthUser(FireAlarm::class)
            ->orderBy('id')
            ->get();

        return view('fire-alarm.index', compact('fireAlarms'));
    }

    /**
     * Tampilkan form tambah Fire Alarm.
     */
    public function create()
    {
        // Preview serial without incrementing counter
        $unitId = $this->getAuthUserUnitId();
        $nextSerial = FireAlarm::generateNextSerial($unitId, false);

        // Default values
        $default = [
            'serial_no' => $nextSerial,
            'barcode' => $nextSerial,
            'status' => 'BAIK',
            'location_code' => 'Lobby Utama / Koridor',
            'type' => 'Smoke Detector',
            'zone' => 'Zone A',
        ];

        return view('fire-alarm.create', compact('nextSerial', 'default'));
    }

    /**
     * Simpan Fire Alarm baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'location_code' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'zone' => 'nullable|string|max:255',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'floor_plan_id' => 'nullable|exists:floor_plans,id',
            'floor_plan_x' => 'nullable|numeric|min:0|max:100',
            'floor_plan_y' => 'nullable|numeric|min:0|max:100',
        ]);

        // Generate serial and increment counter
        $unitId = $this->getAuthUserUnitId();
        $serial = FireAlarm::generateNextSerial($unitId, true);
        $barcode = $serial;

        $fireAlarm = FireAlarm::create([
            'user_id' => Auth::id(),
            'unit_id' => $unitId, // Auto-assign unit
            'barcode' => $barcode,
            'serial_no' => $serial,
            'location_code' => $request->location_code,
            'type' => $request->type,
            'zone' => $request->zone,
            'status' => $request->status,
            'notes' => $request->notes,
            'floor_plan_id' => $request->floor_plan_id,
            'floor_plan_x' => $request->floor_plan_x,
            'floor_plan_y' => $request->floor_plan_y,
        ]);

        // Generate QR
        $fireAlarm->generateQrSvg(true);

        return redirect()
            ->route('fire-alarm.index')
            ->with('success', 'Fire Alarm baru berhasil ditambahkan dengan barcode ' . $fireAlarm->serial_no);
    }

    /**
     * Tampilkan form edit Fire Alarm.
     */
    public function edit(FireAlarm $fireAlarm)
    {
        return view('fire-alarm.edit', compact('fireAlarm'));
    }

    /**
     * Update Fire Alarm yang sudah ada.
     */
    public function update(Request $request, FireAlarm $fireAlarm)
    {
        $request->validate([
            'location_code' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'zone' => 'nullable|string|max:255',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'floor_plan_id' => 'nullable|exists:floor_plans,id',
            'floor_plan_x' => 'nullable|numeric|min:0|max:100',
            'floor_plan_y' => 'nullable|numeric|min:0|max:100',
        ]);

        $fireAlarm->update([
            'location_code' => $request->location_code,
            'type' => $request->type,
            'zone' => $request->zone,
            'status' => $request->status,
            'notes' => $request->notes,
            'floor_plan_id' => $request->floor_plan_id,
            'floor_plan_x' => $request->floor_plan_x,
            'floor_plan_y' => $request->floor_plan_y,
        ]);

        // Regenerate QR (optional)
        $fireAlarm->generateQrSvg(true);

        return redirect()
            ->route('fire-alarm.index')
            ->with('success', 'Data Fire Alarm ' . $fireAlarm->serial_no . ' berhasil diperbarui.');
    }

    /**
     * Tampilkan riwayat inspeksi Fire Alarm
     * 
     * UNIT ACCESS CONTROL: Only allow access to Fire Alarm from same unit
     */
    public function riwayat(Request $request, FireAlarm $fireAlarm)
    {
        // Check unit access
        $userUnitId = $this->getAuthUserUnitId();

        // Superadmin dan Inspector bisa akses semua unit
        if (!auth()->user()->hasAnyRole(['superadmin', 'inspector'])) {
            if ($fireAlarm->unit_id != $userUnitId) {
                abort(403, 'Anda tidak memiliki akses ke Fire Alarm dari unit lain. QR Code ini untuk unit: ' .
                    ($fireAlarm->unit ? $fireAlarm->unit->name : 'Induk'));
            }
        }

        $query = $fireAlarm->kartuInspeksi()->with(['user', 'approver', 'signature']);

        // Filter by creator
        if ($request->filled('creator')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->creator . '%');
            });
        }

        // Filter by approver
        if ($request->filled('approver')) {
            $query->whereHas('approver', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->approver . '%');
            });
        }

        // Filter by approval status
        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->whereNotNull('approved_at');
            } elseif ($request->status === 'pending') {
                $query->whereNull('approved_at');
            }
        }

        $riwayatInspeksi = $query->orderBy('tgl_periksa', 'desc')->get();

        return view('fire-alarm.riwayat', compact('fireAlarm', 'riwayatInspeksi'));
    }

    /**
     * Form kartu pemeriksaan Fire Alarm (tabel NO/LOKASI/NO.SERI/KONDISI/KETERANGAN)
     * Menerima fire_alarm_id untuk menampilkan form untuk Fire Alarm spesifik.
     */
    public function createPemeriksaan(Request $request)
    {
        $fireAlarmId = $request->query('fire_alarm_id');
        $fireAlarm   = FireAlarm::findOrFail($fireAlarmId);

        $template = \App\Models\KartuTemplate::getTemplate('fire-alarm', $fireAlarm->unit_id);

        // Hitung nextRevisi — sama persis dengan kartu kendali
        $latestKartu = \App\Models\KartuFireAlarm::where('fire_alarm_id', $fireAlarmId)
            ->where(function ($q) {
                // Hanya kartu pemeriksaan (catatan diawali "[PMK]")
                $q->whereNotNull('catatan')->where('catatan', 'like', '[PMK]%');
            })
            ->orderBy('id', 'desc')
            ->first();

        if ($latestKartu && ($latestKartu->leader_rejected_at || $latestKartu->rejected_at)) {
            $nextRevisi = str_pad((int)($latestKartu->revisi ?? 0) + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $nextRevisi = '00';
        }

        return view('fire-alarm.kartu-pemeriksaan', compact('fireAlarm', 'template', 'nextRevisi'));
    }

    /**
     * Simpan kartu pemeriksaan Fire Alarm untuk satu Fire Alarm.
     */
    public function storePemeriksaan(Request $request)
    {
        $request->validate([
            'fire_alarm_id' => ['required', 'exists:fire_alarms,id'],
            'tgl_periksa'   => ['required', 'date'],
            'petugas'       => ['required', 'string', 'max:100'],
            'rows'          => ['required', 'array', 'min:1'],
            'rows.*.lokasi'      => ['nullable', 'string', 'max:255'],
            'rows.*.no_seri'     => ['nullable', 'string', 'max:100'],
            'rows.*.kondisi'     => ['nullable', 'string', 'max:50'],
            'rows.*.keterangan'  => ['nullable', 'string', 'max:255'],
        ]);

        $fireAlarm = FireAlarm::findOrFail($request->fire_alarm_id);

        // Tentukan revisi berdasarkan kartu pemeriksaan terakhir yang ditolak
        $latestKartu = \App\Models\KartuFireAlarm::where('fire_alarm_id', $fireAlarm->id)
            ->where(function ($q) {
                $q->whereNotNull('catatan')->where('catatan', 'like', '[PMK]%');
            })
            ->orderBy('id', 'desc')
            ->first();

        $revisi = ($latestKartu && ($latestKartu->leader_rejected_at || $latestKartu->rejected_at))
            ? str_pad((int)($latestKartu->revisi ?? 0) + 1, 2, '0', STR_PAD_LEFT)
            : '00';

        // Simpan setiap baris yang kondisinya diisi
        $saved = 0;
        foreach ($request->rows as $row) {
            if (empty($row['kondisi'])) continue;

            // Gabungkan lokasi & no_seri ke dalam catatan agar tersimpan
            // Prefix [PMK] dipakai sebagai penanda kartu pemeriksaan (untuk query revisi)
            $catatan = '[PMK] ' . trim(
                ($row['lokasi']     ? 'Lokasi: ' . $row['lokasi'] . ' | '     : '') .
                ($row['no_seri']    ? 'No. Seri: ' . $row['no_seri'] . ' | '  : '') .
                ($row['keterangan'] ? 'Ket: ' . $row['keterangan']             : '')
            , ' |');

            \App\Models\KartuFireAlarm::create([
                'fire_alarm_id'     => $fireAlarm->id,
                'user_id'           => auth()->id(),
                'panel_kontrol'     => $row['kondisi'],
                'detector'          => $row['kondisi'],
                'manual_call_point' => $row['kondisi'],
                'alarm_bell'        => $row['kondisi'],
                'battery_backup'    => $row['kondisi'],
                'uji_fungsi'        => $row['kondisi'],
                'kesimpulan'        => $row['kondisi'],
                'tgl_periksa'       => $request->tgl_periksa,
                'petugas'           => $request->petugas,
                'catatan'           => $catatan ?: null,
                'revisi'            => $revisi,
            ]);
            $saved++;
        }

        return redirect()
            ->route('fire-alarm.index')
            ->with('success', "Kartu Pemeriksaan Fire Alarm {$fireAlarm->serial_no} berhasil disimpan ({$saved} baris).");
    }

    /**
     * View detail kartu kendali dengan TTD
     * 
     * UNIT ACCESS CONTROL: Only allow access to Fire Alarm from same unit
     */
    public function viewKartu($fireAlarmId, $kartuId)
    {
        $fireAlarm = FireAlarm::findOrFail($fireAlarmId);

        // Check unit access
        $userUnitId = $this->getAuthUserUnitId();

        // Superadmin dan Inspector bisa akses semua unit
        if (!auth()->user()->hasAnyRole(['superadmin', 'inspector'])) {
            if ($fireAlarm->unit_id != $userUnitId) {
                abort(403, 'Anda tidak memiliki akses ke Fire Alarm dari unit lain. Kartu ini untuk unit: ' .
                    ($fireAlarm->unit ? $fireAlarm->unit->name : 'Induk'));
            }
        }

        $kartu = \App\Models\KartuFireAlarm::with(['signature', 'user', 'approver', 'leaderSignature', 'leaderApprover'])->findOrFail($kartuId);

        // Verify kartu actually belongs to this Fire Alarm (prevent IDOR)
        if ((int) $kartu->fire_alarm_id !== (int) $fireAlarmId) {
            abort(404, 'Kartu tidak ditemukan untuk equipment ini.');
        }

        // Get template for Fire Alarm module with unit-specific address
        $template = \App\Models\KartuTemplate::getTemplate('fire-alarm', $fireAlarm->unit_id);

        // Fill template with real data
        if ($template) {
            // Map data berdasarkan label field
            $labelMap = [
                'No. Dokumen' => 'FA-' . str_pad($kartu->id, 4, '0', STR_PAD_LEFT),
                'Revisi' => str_pad((string) ($kartu->revisi ?? '0'), 2, '0', STR_PAD_LEFT),
                'Tanggal' => \Carbon\Carbon::parse($kartu->tgl_periksa)->format('d F Y'),
                'Halaman' => '1 dari 1',
            ];

            // Update header fields dengan data real
            $headerFields = collect($template->header_fields)->map(function ($field) use ($labelMap) {
                if (isset($labelMap[$field['label']])) {
                    $field['value'] = $labelMap[$field['label']];
                }
                return $field;
            })->toArray();

            $template->header_fields = $headerFields;
        }

        return view('fire-alarm.view-kartu', compact('fireAlarm', 'kartu', 'template'));
    }
}
