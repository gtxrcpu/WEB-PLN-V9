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
        $nextSerial = FireAlarm::generateNextSerial(null, false);

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
        $serial = FireAlarm::generateNextSerial(null, true);
        $barcode = $serial;

        $fireAlarm = FireAlarm::create([
            'user_id' => Auth::id(),
            'unit_id' => $this->getAuthUserUnitId(), // Auto-assign unit
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

        $kartu = \App\Models\KartuFireAlarm::with(['signature', 'user', 'approver'])->findOrFail($kartuId);

        // Get template for Fire Alarm module
        $template = \App\Models\KartuTemplate::getTemplate('fire-alarm');

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
