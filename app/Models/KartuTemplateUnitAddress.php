<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuTemplateUnitAddress extends Model
{
    protected $fillable = [
        'kartu_template_id',
        'unit_id',
        'address',
    ];

    public function template()
    {
        return $this->belongsTo(KartuTemplate::class, 'kartu_template_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
