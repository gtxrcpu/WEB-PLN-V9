<?php

namespace App\Models\Traits;

use App\Models\Unit;

trait HasUnit
{
    /**
     * Relasi ke unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Scope untuk filter by unit
     */
    public function scopeForUnit($query, $unitId)
    {
        if ($unitId) {
            return $query->where('unit_id', $unitId);
        }
        return $query;
    }

    /**
     * Scope untuk user yang punya unit
     * Mendukung viewing_unit_id untuk superadmin/leader
     */
    public function scopeForAuthUser($query)
    {
        $user = auth()->user();
        
        if ($user && $user->unit_id) {
            // Petugas: selalu pakai unit_id sendiri
            return $query->where('unit_id', $user->unit_id);
        }
        
        // Superadmin/Leader: cek session viewing_unit_id
        if ($user && !$user->unit_id && session('viewing_unit_id')) {
            return $query->where('unit_id', session('viewing_unit_id'));
        }
        
        return $query;
    }
}
