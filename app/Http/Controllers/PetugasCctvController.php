<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cctv;
use App\Models\FloorPlan;
use App\Models\Unit;
use Illuminate\Http\Request;

class PetugasCctvController extends Controller
{
    /**
     * Get the effective unit_id for the currently logged-in user.
     */
    private function getUnitId(): ?int
    {
        $user = auth()->user();
        return $user->unit_id ?? null;
    }

    /**
     * Display a listing of the CCTV for the user's unit.
     */
    public function index(Request $request)
    {
        $unitId = $this->getUnitId();

        $query = Cctv::query();

        // Always scope to user's unit if they have one
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location_code', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $cctvs = $query->latest()->get();

        return view('petugas.cctvs.index', compact('cctvs'));
    }

    /**
     * Show form to add a new CCTV.
     */
    public function create()
    {
        $unitId = $this->getUnitId();
        return view('petugas.cctvs.create', compact('unitId'));
    }

    /**
     * Store a newly created CCTV.
     */
    public function store(Request $request)
    {
        $unitId = $this->getUnitId();

        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'location_code'=> 'required|string|max:100',
            'status'       => 'required|in:Baik,Jelek',
            'notes'        => 'nullable|string',
        ]);

        // Force unit_id to the user's own unit
        $data['unit_id'] = $unitId;

        Cctv::create($data);

        return redirect()
            ->route('petugas.cctvs.index')
            ->with('success', 'CCTV berhasil ditambahkan');
    }

    /**
     * Show form to edit a CCTV (must belong to user's unit).
     */
    public function edit(Cctv $cctv)
    {
        $unitId = $this->getUnitId();

        // Security: only edit if cctv belongs to user's unit
        if ($unitId && $cctv->unit_id !== $unitId) {
            abort(403, 'CCTV ini tidak berada dalam unit Anda.');
        }

        return view('petugas.cctvs.edit', compact('cctv', 'unitId'));
    }

    /**
     * Update a CCTV.
     */
    public function update(Request $request, Cctv $cctv)
    {
        $unitId = $this->getUnitId();

        if ($unitId && $cctv->unit_id !== $unitId) {
            abort(403, 'CCTV ini tidak berada dalam unit Anda.');
        }

        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'location_code'=> 'required|string|max:100',
            'status'       => 'required|in:Baik,Jelek',
            'notes'        => 'nullable|string',
        ]);

        // Keep unit_id locked
        $data['unit_id'] = $unitId;

        $cctv->update($data);

        return redirect()
            ->route('petugas.cctvs.index')
            ->with('success', 'CCTV berhasil diperbarui');
    }

    /**
     * Toggle CCTV status via AJAX (same as admin but unit-scoped).
     */
    public function toggleStatus(Request $request, Cctv $cctv)
    {
        $unitId = $this->getUnitId();

        if ($unitId && $cctv->unit_id !== $unitId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:Baik,Jelek'
        ]);

        $cctv->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status CCTV berhasil diubah menjadi ' . $request->status,
            'data'    => $cctv
        ]);
    }
}
