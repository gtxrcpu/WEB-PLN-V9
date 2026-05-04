<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitSwitchController extends Controller
{
    /**
     * Switch ke unit tertentu (untuk admin)
     */
    public function switch(Request $request)
    {
        $user = auth()->user();
        
        // Hanya Admin (yang tidak terikat unit) yang boleh switch unit
        if ($user && $user->unit_id) {
            return back()->with('error', 'Petugas tidak diperbolehkan mengganti unit kerja.');
        }

        $unitId = $request->input('unit_id');
        
        // Validasi unit exists
        if ($unitId && !Unit::find($unitId)) {
            return back()->with('error', 'Unit tidak ditemukan');
        }
        
        // Simpan ke session
        if ($unitId) {
            session(['viewing_unit_id' => $unitId]);
            $unit = Unit::find($unitId);
            $message = 'Sekarang melihat data unit: ' . $unit->code;
        } else {
            session()->forget('viewing_unit_id');
            $message = 'Sekarang melihat semua unit';
        }
        
        return back()->with('success', $message);
    }
    
    /**
     * Clear unit filter (kembali ke view all)
     */
    public function clear()
    {
        $user = auth()->user();
        if ($user && $user->unit_id) {
            return back()->with('error', 'Petugas tidak diperbolehkan mengganti unit kerja.');
        }

        session()->forget('viewing_unit_id');
        return back()->with('success', 'Sekarang melihat semua unit');
    }
}
