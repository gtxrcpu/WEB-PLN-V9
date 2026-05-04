<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasUnit;

class Cctv extends Model
{
    use HasUnit;

    protected $table = 'cctvs';

    protected $fillable = [
        'unit_id',
        'name',
        'location_code',
        'stream_url',
        'is_online',
        'last_seen_at',
        'floor_plan_id',
        'floor_plan_x',
        'floor_plan_y',
        'status',
        'notes',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_seen_at' => 'datetime',
        'floor_plan_x' => 'float',
        'floor_plan_y' => 'float',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function floorPlan()
    {
        return $this->belongsTo(FloorPlan::class);
    }
}
