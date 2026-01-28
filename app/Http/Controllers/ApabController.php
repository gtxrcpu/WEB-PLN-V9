<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FiltersByUnit;
use App\Models\Apab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApabController extends Controller
{
    use FiltersByUnit;

    /**
     * List semua APAB (filtered by unit)
     */
    public function index()
    {
        $apabs = $this->getQueryForAuthUser(Apab::class)
            ->orderBy('id')
            ->get();

        return view('apab.index', compact('apabs'));
    }

    /**
     * Tampilkan form tambah APAB.
     */
    public function create()
    {
        // Preview serial without incrementing counter
        $nextSerial = Apab::generateNextSerial(null, false);

        // Default values
        $default = [
            'serial_no' => $nextSerial,
            'barcode' => $nextSerial,
            'status' => 'BAIK',
            'lokasi' => 'Lobby Utama / Ruang Panel',
            'jenis' => 'Karung Goni Isi Pasir',
            'kapasitas' => '1 Karung',
        ];

        return view('apab.create', compact('nextSerial', 'default'));
    }

    /**
     * Simpan APAB baru.
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
        $serial = Apab::generateNextSerial(null, true);
        $barcode = $serial;

        $apab = Apab::create([
            'user_id' => Auth::id(),
            'unit_id' => $this->getAuthUserUnitId(), // Auto-assign unit
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
        $apab->generateQrSvg(true);

        return redirect()
            ->route('apab.index')
            ->with('success', 'APAB baru berhasil ditambahkan dengan barcode ' . $apab->serial_no);
    }

    /**
     * Tampilkan form edit APAB.
     */
    public function edit(Apab $apab)
    {
        return view('apab.edit', compact('apab'));
    }

    /**
     * Update APAB yang sudah ada.
     */
    public function update(Request $request, Apab $apab)
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

        $apab->update([
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
        $apab->generateQrSvg(true);

        return redirect()
            ->route('apab.index')
            ->with('success', 'Data APAB ' . $apab->serial_no . ' berhasil diperbarui.');
    }

    /**
     * Tampilkan riwayat inspeksi APAB
     * 
     * UNIT ACCESS CONTROL: Only allow access to APAB from same unit
     */
    public function riwayat(Request $request, Apab $apab)
    {
        // Check unit access
        $userUnitId = $this->getAuthUserUnitId();

        // Superadmin dan Inspector bisa akses semua unit
        if (!auth()->user()->hasAnyRole(['superadmin', 'inspector'])) {
            if ($apab->unit_id != $userUnitId) {
                abort(403, 'Anda tidak memiliki akses ke APAB dari unit lain. QR Code ini untuk unit: ' .
                    ($apab->unit ? $apab->unit->name : 'Induk'));
            }
        }

        $query = $apab->kartuApabs()->with(['user', 'approver', 'signature']);

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

        $kartuKendali = $query->orderBy('tgl_periksa', 'desc')->get();

        return view('apab.riwayat', compact('apab', 'kartuKendali'));
    }

    /**
     * View detail kartu kendali APAB (untuk print/view).
     * 
     * UNIT ACCESS CONTROL: Only allow access to APAB from same unit
     */
    public function viewKartu(Apab $apab, $kartuId)
    {
        // Check unit access
        $userUnitId = $this->getAuthUserUnitId();

        // Superadmin dan Inspector bisa akses semua unit
        if (!auth()->user()->hasAnyRole(['superadmin', 'inspector'])) {
            if ($apab->unit_id != $userUnitId) {
                abort(403, 'Anda tidak memiliki akses ke APAB dari unit lain. Kartu ini untuk unit: ' .
                    ($apab->unit ? $apab->unit->name : 'Induk'));
            }
        }

        $kartu = \App\Models\KartuApab::with(['user', 'approver', 'signature'])->findOrFail($kartuId);
        $template = \App\Models\KartuTemplate::getTemplate('apab');

        return view('apab.view-kartu', compact('apab', 'kartu', 'template'));
    }
}
