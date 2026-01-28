<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FiltersByUnit;
use App\Models\RumahPompa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RumahPompaController extends Controller
{
    use FiltersByUnit;

    public function index()
    {
        $rumahPompas = $this->getQueryForAuthUser(RumahPompa::class)
            ->orderBy('id')
            ->get();
        return view('rumah-pompa.index', compact('rumahPompas'));
    }

    public function create()
    {
        // Preview serial without incrementing counter
        $nextSerial = RumahPompa::generateNextSerial(null, false);

        // Default values
        $default = [
            'serial_no' => $nextSerial,
            'barcode' => $nextSerial,
            'status' => 'BAIK',
            'location_code' => 'Area Pompa / Basement',
            'type' => 'Electric Pump / Diesel Pump',
            'zone' => 'Zone A',
        ];

        return view('rumah-pompa.create', compact('nextSerial', 'default'));
    }

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
        $serial = RumahPompa::generateNextSerial(null, true);
        $barcode = $serial;

        $rumahPompa = RumahPompa::create([
            'user_id' => Auth::id(),
            'unit_id' => $this->getAuthUserUnitId(),
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

        $rumahPompa->generateQrSvg(true);

        return redirect()
            ->route('rumah-pompa.index')
            ->with('success', 'Rumah Pompa baru berhasil ditambahkan dengan serial ' . $rumahPompa->serial_no);
    }

    public function edit(RumahPompa $rumahPompa)
    {
        return view('rumah-pompa.edit', compact('rumahPompa'));
    }

    public function update(Request $request, RumahPompa $rumahPompa)
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

        $rumahPompa->update([
            'location_code' => $request->location_code,
            'type' => $request->type,
            'zone' => $request->zone,
            'status' => $request->status,
            'notes' => $request->notes,
            'floor_plan_id' => $request->floor_plan_id,
            'floor_plan_x' => $request->floor_plan_x,
            'floor_plan_y' => $request->floor_plan_y,
        ]);

        $rumahPompa->generateQrSvg(true);

        return redirect()
            ->route('rumah-pompa.index')
            ->with('success', 'Data Rumah Pompa ' . $rumahPompa->serial_no . ' berhasil diperbarui.');
    }

    public function riwayat(Request $request, RumahPompa $rumahPompa)
    {
        // Check unit access
        $userUnitId = $this->getAuthUserUnitId();

        if (!auth()->user()->hasAnyRole(['superadmin', 'inspector'])) {
            if ($rumahPompa->unit_id != $userUnitId) {
                abort(403, 'Anda tidak memiliki akses ke Rumah Pompa dari unit lain. QR Code ini untuk unit: ' .
                    ($rumahPompa->unit ? $rumahPompa->unit->name : 'Induk'));
            }
        }

        $query = $rumahPompa->kartuInspeksi()->with(['user', 'approver', 'signature']);

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

        return view('rumah-pompa.riwayat', compact('rumahPompa', 'riwayatInspeksi'));
    }

    public function viewKartu($rumahPompaId, $kartuId)
    {
        $rumahPompa = RumahPompa::findOrFail($rumahPompaId);

        // Check unit access
        $userUnitId = $this->getAuthUserUnitId();

        if (!auth()->user()->hasAnyRole(['superadmin', 'inspector'])) {
            if ($rumahPompa->unit_id != $userUnitId) {
                abort(403, 'Anda tidak memiliki akses ke Rumah Pompa dari unit lain. Kartu ini untuk unit: ' .
                    ($rumahPompa->unit ? $rumahPompa->unit->name : 'Induk'));
            }
        }

        $kartu = \App\Models\KartuRumahPompa::with(['signature', 'user', 'approver'])->findOrFail($kartuId);
        $template = \App\Models\KartuTemplate::getTemplate('rumah-pompa');

        if ($template) {
            $labelMap = [
                'No. Dokumen' => 'RP-' . str_pad($kartu->id, 4, '0', STR_PAD_LEFT),
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

        return view('rumah-pompa.view-kartu', compact('rumahPompa', 'kartu', 'template'));
    }
}
