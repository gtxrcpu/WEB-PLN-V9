<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FiltersByUnit;
use App\Models\Apat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApatController extends Controller
{
    use FiltersByUnit;

    /**
     * List semua APAT (filtered by unit)
     */
    public function index()
    {
        $apats = $this->getQueryForAuthUser(Apat::class)
            ->orderBy('id')
            ->get();

        return view('apat.index', compact('apats'));
    }

    /**
     * Tampilkan form tambah APAT.
     */
    public function create()
    {
        // Preview serial without incrementing counter
        $unitId = $this->getAuthUserUnitId();
        $nextSerial = Apat::generateNextSerial($unitId, false);

        // Default values
        $default = [
            'serial_no' => $nextSerial,
            'barcode' => $nextSerial,
            'status' => 'BAIK',
            'lokasi' => 'Lobby Utama / Parkir Motor',
            'jenis' => 'Pasir, Tanah, dll.',
            'kapasitas' => '1 Drum / 1 Bak',
        ];

        return view('apat.create', compact('nextSerial', 'default'));
    }

    /**
     * Simpan APAT baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lokasi' => 'required|string|max:255',
            'jenis' => 'required|string|max:255',
            'kapasitas' => 'required|string|max:255',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'floor_plan_id' => 'nullable|exists:floor_plans,id',
            'floor_plan_x' => 'nullable|numeric|min:0|max:100',
            'floor_plan_y' => 'nullable|numeric|min:0|max:100',
        ]);

        // Generate serial and increment counter
        $unitId = $this->getAuthUserUnitId();
        $serial = Apat::generateNextSerial($unitId, true);
        $barcode = $serial;

        $apat = Apat::create([
            'user_id' => Auth::id(),
            'unit_id' => $unitId, // Auto-assign unit
            'barcode' => $barcode,
            'serial_no' => $serial,
            'lokasi' => $request->lokasi,
            'jenis' => $request->jenis,
            'kapasitas' => $request->kapasitas,
            'status' => $request->status,
            'notes' => $request->notes,
            'floor_plan_id' => $request->floor_plan_id,
            'floor_plan_x' => $request->floor_plan_x,
            'floor_plan_y' => $request->floor_plan_y,
        ]);

        // Generate QR
        $apat->generateQrSvg(true);

        return redirect()
            ->route('apat.index')
            ->with('success', 'APAT baru berhasil ditambahkan dengan barcode ' . $apat->serial_no);
    }

    /**
     * Tampilkan form edit APAT.
     */
    public function edit(Apat $apat)
    {
        return view('apat.edit', compact('apat'));
    }

    /**
     * Update APAT yang sudah ada.
     */
    public function update(Request $request, Apat $apat)
    {
        $request->validate([
            'lokasi' => 'required|string|max:255',
            'jenis' => 'required|string|max:255',
            'kapasitas' => 'required|string|max:255',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'floor_plan_id' => 'nullable|exists:floor_plans,id',
            'floor_plan_x' => 'nullable|numeric|min:0|max:100',
            'floor_plan_y' => 'nullable|numeric|min:0|max:100',
        ]);

        $apat->update([
            'lokasi' => $request->lokasi,
            'jenis' => $request->jenis,
            'kapasitas' => $request->kapasitas,
            'status' => $request->status,
            'notes' => $request->notes,
            'floor_plan_id' => $request->floor_plan_id,
            'floor_plan_x' => $request->floor_plan_x,
            'floor_plan_y' => $request->floor_plan_y,
        ]);

        // Regenerate QR (optional)
        $apat->generateQrSvg(true);

        return redirect()
            ->route('apat.index')
            ->with('success', 'Data APAT ' . $apat->serial_no . ' berhasil diperbarui.');
    }

    /**
     * Tampilkan riwayat inspeksi APAT
     * 
     * UNIT ACCESS CONTROL: Only allow access to APAT from same unit
     */
    public function riwayat(Request $request, Apat $apat)
    {
        // Check unit access
        $userUnitId = $this->getAuthUserUnitId();

        // Superadmin dan Inspector bisa akses semua unit
        if (!auth()->user()->hasAnyRole(['superadmin', 'inspector'])) {
            if ($apat->unit_id != $userUnitId) {
                abort(403, 'Anda tidak memiliki akses ke APAT dari unit lain. QR Code ini untuk unit: ' .
                    ($apat->unit ? $apat->unit->name : 'Induk'));
            }
        }

        $query = $apat->kartuApats()->with(['user', 'approver', 'signature']);

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

        return view('apat.riwayat', compact('apat', 'riwayatInspeksi'));
    }

    /**
     * View detail kartu kendali APAT (untuk print/view).
     * 
     * UNIT ACCESS CONTROL: Only allow access to APAT from same unit
     */
    public function viewKartu(Apat $apat, $kartuId)
    {
        // Check unit access
        $userUnitId = $this->getAuthUserUnitId();

        // Superadmin dan Inspector bisa akses semua unit
        if (!auth()->user()->hasAnyRole(['superadmin', 'inspector'])) {
            if ($apat->unit_id != $userUnitId) {
                abort(403, 'Anda tidak memiliki akses ke APAT dari unit lain. Kartu ini untuk unit: ' .
                    ($apat->unit ? $apat->unit->name : 'Induk'));
            }
        }

        $kartu = \App\Models\KartuApat::with(['user', 'approver', 'signature'])->findOrFail($kartuId);
        $template = \App\Models\KartuTemplate::getTemplate('apat', $apat->unit_id);

        return view('apat.view-kartu', compact('apat', 'kartu', 'template'));
    }
}
