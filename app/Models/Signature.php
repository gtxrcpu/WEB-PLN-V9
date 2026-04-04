<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Signature extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'position',
        'nip',
        'signature_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getSignatureUrlAttribute()
    {
        if (empty($this->signature_path)) {
            return null;
        }

        return Storage::url($this->signature_path);
    }
}
