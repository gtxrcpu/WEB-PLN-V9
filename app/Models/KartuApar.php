<?php

namespace App\Models;

use App\Models\Traits\HasApprovalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KartuApar extends Model
{
    use HasFactory, HasApprovalStatus;

    protected $table = 'kartu_apars';

    protected $fillable = [
        'apar_id',
        'user_id',
        'pressure_gauge',
        'pin_segel',
        'selang',
        'tabung',
        'label',
        'kondisi_fisik',
        'kesimpulan',
        'tgl_periksa',
        'revisi',
        'petugas',
        'catatan',
        'leader_signature_id',
        'leader_approved_by',
        'leader_approved_at',
        'leader_rejected_by',
        'leader_rejected_at',
        'leader_rejection_reason',
        'signature_id',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'tgl_periksa' => 'date',
        'leader_approved_at' => 'datetime',
        'leader_rejected_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Relasi ke APAR
     */
    public function apar()
    {
        return $this->belongsTo(Apar::class, 'apar_id');
    }

    /**
     * Relasi ke User yang menginput
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Signature (TTD)
     */
    public function signature()
    {
        return $this->belongsTo(Signature::class);
    }

    public function leaderSignature()
    {
        return $this->belongsTo(Signature::class, 'leader_signature_id');
    }

    /**
     * Relasi ke User yang approve
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function leaderApprover()
    {
        return $this->belongsTo(User::class, 'leader_approved_by');
    }

    public function leaderRejector()
    {
        return $this->belongsTo(User::class, 'leader_rejected_by');
    }
}
