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
        'status',
        'notes',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
