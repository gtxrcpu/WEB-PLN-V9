<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FloorPlan extends Model
{
    protected $fillable = [
        'unit_id',
        'name',
        'image_path',
        'width',
        'height',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'width' => 'integer',
        'height' => 'integer',
    ];

    /**
     * Get the unit that owns the floor plan
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the image URL attribute.
     *
     * Simplified logic: images are ALWAYS stored in storage/app/public/floor-plans/
     * and served via the public disk URL (which respects APP_URL/ASSET_URL for subpath).
     *
     * Fallback order:
     * 1. Storage disk (storage/app/public/{image_path}) — primary
     * 2. Public folder (public/{image_path}) — legacy uploads
     * 3. Placeholder image
     */
    public function getImageUrlAttribute()
    {
        if (empty($this->image_path)) {
            return asset('images/placeholder-floor-plan.png');
        }

        // Primary: check storage disk (handles subpath via filesystems.php 'url' config)
        if (Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->url($this->image_path);
        }

        // Legacy: check public folder directly (for old uploads before storage refactor)
        if (file_exists(public_path($this->image_path))) {
            return asset($this->image_path);
        }

        // Also check with 'storage/' prefix stripped (in case path was saved with it)
        $strippedPath = str_starts_with($this->image_path, 'storage/')
            ? substr($this->image_path, 8)
            : $this->image_path;

        if ($strippedPath !== $this->image_path && Storage::disk('public')->exists($strippedPath)) {
            return Storage::disk('public')->url($strippedPath);
        }

        return asset('images/placeholder-floor-plan.png');
    }

    /**
     * Get all equipment for this floor plan
     */
    public function getAllEquipment()
    {
        $equipment = [];

        $equipment['apar'] = Apar::where('floor_plan_id', $this->id)
            ->whereNotNull('floor_plan_x')
            ->whereNotNull('floor_plan_y')
            ->get();

        $equipment['apat'] = Apat::where('floor_plan_id', $this->id)
            ->whereNotNull('floor_plan_x')
            ->whereNotNull('floor_plan_y')
            ->get();

        $equipment['fire_alarm'] = FireAlarm::where('floor_plan_id', $this->id)
            ->whereNotNull('floor_plan_x')
            ->whereNotNull('floor_plan_y')
            ->get();

        $equipment['box_hydrant'] = BoxHydrant::where('floor_plan_id', $this->id)
            ->whereNotNull('floor_plan_x')
            ->whereNotNull('floor_plan_y')
            ->get();

        $equipment['rumah_pompa'] = RumahPompa::where('floor_plan_id', $this->id)
            ->whereNotNull('floor_plan_x')
            ->whereNotNull('floor_plan_y')
            ->get();

        $equipment['apab'] = Apab::where('floor_plan_id', $this->id)
            ->whereNotNull('floor_plan_x')
            ->whereNotNull('floor_plan_y')
            ->get();

        $equipment['p3k'] = P3k::where('floor_plan_id', $this->id)
            ->whereNotNull('floor_plan_x')
            ->whereNotNull('floor_plan_y')
            ->get();

        $equipment['cctv'] = Cctv::where('floor_plan_id', $this->id)
            ->whereNotNull('floor_plan_x')
            ->whereNotNull('floor_plan_y')
            ->get();

        return $equipment;
    }

    /**
     * Get all CCTV on this floor plan
     */
    public function cctvs()
    {
        return $this->hasMany(Cctv::class);
    }

    /**
     * Get all APAR equipment on this floor plan
     */
    public function apars()
    {
        return $this->hasMany(Apar::class);
    }

    /**
     * Get all APAT equipment on this floor plan
     */
    public function apats()
    {
        return $this->hasMany(Apat::class);
    }

    /**
     * Get all Fire Alarm equipment on this floor plan
     */
    public function fireAlarms()
    {
        return $this->hasMany(FireAlarm::class);
    }

    /**
     * Get all Box Hydrant equipment on this floor plan
     */
    public function boxHydrants()
    {
        return $this->hasMany(BoxHydrant::class);
    }

    /**
     * Get all Rumah Pompa equipment on this floor plan
     */
    public function rumahPompas()
    {
        return $this->hasMany(RumahPompa::class);
    }

    /**
     * Get all APAB equipment on this floor plan
     */
    public function apabs()
    {
        return $this->hasMany(Apab::class);
    }

    /**
     * Get all P3K equipment on this floor plan
     */
    public function p3ks()
    {
        return $this->hasMany(P3k::class);
    }
}
