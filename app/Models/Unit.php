<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot method to add model events
     */
    protected static function boot()
    {
        parent::boot();
        
        // Prevent duplicate codes on create/update
        static::saving(function ($unit) {
            // Check if code already exists (excluding current record on update)
            $query = static::where('code', $unit->code);
            
            if ($unit->exists) {
                $query->where('id', '!=', $unit->id);
            }
            
            if ($query->exists()) {
                throw new \Exception("Unit dengan code '{$unit->code}' sudah ada. Duplikasi tidak diperbolehkan.");
            }
        });
    }

    // Relasi ke users
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Get leader dari unit ini
    public function leader()
    {
        return $this->hasOne(User::class)->where('position', 'leader');
    }

    // Get petugas dari unit ini
    public function petugas()
    {
        return $this->hasMany(User::class)->where('position', 'petugas');
    }
}
