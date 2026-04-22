<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SignatureController extends Controller
{
    /**
     * Get the current effective unit ID for the leader
     */
    private function getUnitId()
    {
        $user = auth()->user();
        if ($user && $user->unit_id) {
            return $user->unit_id;
        }
        
        $sessionUnitId = session('viewing_unit_id');
        if ($user && !$user->unit_id && $sessionUnitId) {
            return (int) $sessionUnitId;
        }
        
        return null;
    }

    public function index()
    {
        $unitId = $this->getUnitId();
        
        if (!$unitId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki unit yang ditugaskan.');
        }

        $signatures = Signature::where('unit_id', $unitId)->latest()->paginate(20);
        return view('leader.signatures.index', compact('signatures'));
    }

    public function create()
    {
        if (!$this->getUnitId()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki unit yang ditugaskan.');
        }
        
        return view('leader.signatures.create');
    }

    public function store(Request $request)
    {
        $unitId = $this->getUnitId();
        
        if (!$unitId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki unit yang ditugaskan.');
        }

        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'position' => 'required|string|max:255',
                'nip' => 'nullable|string|max:255',
                'signature' => 'required|image|mimes:png,jpg,jpeg|max:2048',
            ]);

            // Upload signature
            if ($request->hasFile('signature')) {
                $file = $request->file('signature');
                $filename = 'signature_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('signatures', $filename, 'public');
                $data['signature_path'] = $path;
            } else {
                return back()->with('error', 'File tanda tangan tidak ditemukan')->withInput();
            }

            unset($data['signature']);
            $data['is_active'] = $request->has('is_active') ? true : false;
            $data['unit_id'] = $unitId; // Assign to Leader's unit

            $signature = Signature::create($data);

            Log::info('Signature created by leader', [
                'user_id' => auth()->id(),
                'unit_id' => $unitId,
                'signature_id' => $signature->id,
                'name' => $signature->name
            ]);

            return redirect()
                ->route('leader.signatures.index')
                ->with('success', 'Tanda tangan berhasil ditambahkan');
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit(Signature $signature)
    {
        $unitId = $this->getUnitId();
        
        if ($signature->unit_id !== $unitId && !auth()->user()->hasRole('superadmin')) {
            abort(403, 'Akses ditolak: Anda hanya dapat mengedit tanda tangan unit Anda sendiri.');
        }

        return view('leader.signatures.edit', compact('signature'));
    }

    public function update(Request $request, Signature $signature)
    {
        $unitId = $this->getUnitId();
        
        if ($signature->unit_id !== $unitId && !auth()->user()->hasRole('superadmin')) {
            abort(403, 'Akses ditolak: Anda hanya dapat mengubah tanda tangan unit Anda sendiri.');
        }

        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'position' => 'required|string|max:255',
                'nip' => 'nullable|string|max:255',
                'signature' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            ]);

            // Upload new signature if provided
            if ($request->hasFile('signature')) {
                // Delete old signature
                if ($signature->signature_path) {
                    Storage::disk('public')->delete($signature->signature_path);
                }

                $file = $request->file('signature');
                $filename = 'signature_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('signatures', $filename, 'public');
                $data['signature_path'] = $path;
            }

            unset($data['signature']);
            $data['is_active'] = $request->has('is_active') ? true : false;

            $signature->update($data);

            Log::info('Signature updated by leader', [
                'user_id' => auth()->id(),
                'unit_id' => $unitId,
                'signature_id' => $signature->id,
                'name' => $signature->name
            ]);

            return redirect()
                ->route('leader.signatures.index')
                ->with('success', 'Tanda tangan berhasil diupdate');
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Signature $signature)
    {
        $unitId = $this->getUnitId();
        
        if ($signature->unit_id !== $unitId && !auth()->user()->hasRole('superadmin')) {
            abort(403, 'Akses ditolak: Anda hanya dapat menghapus tanda tangan unit Anda sendiri.');
        }

        $id = $signature->id;
        $name = $signature->name;

        // Delete signature file
        if ($signature->signature_path) {
            Storage::disk('public')->delete($signature->signature_path);
        }

        $signature->delete();

        Log::info('Signature deleted by leader', [
            'user_id' => auth()->id(),
            'unit_id' => $unitId,
            'signature_id' => $id,
            'name' => $name
        ]);

        return redirect()
            ->route('leader.signatures.index')
            ->with('success', 'Tanda tangan berhasil dihapus');
    }
}
