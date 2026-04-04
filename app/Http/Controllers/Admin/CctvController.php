<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cctv;
use Illuminate\Http\Request;

class CctvController extends Controller
{
    /**
     * Display a listing of the CCTV.
     */
    public function index(Request $request)
    {
        $query = Cctv::query();
        
        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
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
        
        return view('admin.cctvs.index', compact('cctvs'));
    }

    /**
     * Show the form for creating a new CCTV.
     */
    public function create()
    {
        return view('admin.cctvs.create');
    }

    /**
     * Store a newly created CCTV.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'location_code' => 'required|string|max:100',
            'status' => 'required|in:Baik,Jelek',
            'notes' => 'nullable|string',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        Cctv::create($data);

        return redirect()
            ->route('admin.cctvs.index')
            ->with('success', 'CCTV berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified CCTV.
     */
    public function edit(Cctv $cctv)
    {
        return view('admin.cctvs.edit', compact('cctv'));
    }

    /**
     * Update the specified CCTV.
     */
    public function update(Request $request, Cctv $cctv)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'location_code' => 'required|string|max:100',
            'status' => 'required|in:Baik,Jelek',
            'notes' => 'nullable|string',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        $cctv->update($data);

        return redirect()
            ->route('admin.cctvs.index')
            ->with('success', 'CCTV berhasil diperbarui');
    }

    /**
     * Remove the specified CCTV.
     */
    public function destroy(Cctv $cctv)
    {
        $cctv->delete();

        return redirect()
            ->route('admin.cctvs.index')
            ->with('success', 'CCTV berhasil dihapus');
    }

    /**
     * API Endpoint to toggle CCTV status (for AJAX).
     * Accessible only by superadmin.
     */
    public function toggleStatus(Request $request, Cctv $cctv)
    {
        $request->validate([
            'status' => 'required|in:Baik,Jelek'
        ]);

        $cctv->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status CCTV berhasil diubah menjadi ' . $request->status,
            'data' => $cctv
        ]);
    }
}
