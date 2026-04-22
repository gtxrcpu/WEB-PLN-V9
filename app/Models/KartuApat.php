<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KartuApat extends Model
{
    use HasFactory;

    protected $table = 'kartu_apats';

    protected $fillable = [
        'apat_id',
        'user_id',
        'kondisi_fisik',
        'drum',
        'aduk_pasir',
        'sekop',
        'fire_blanket',
        'ember',
        'kesimpulan',
        'tgl_periksa',
        'revisi',
        'tgl_surat',
        'petugas',
        'pengawas',
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

    public function apat()
    {
        return $this->belongsTo(Apat::class);
    }

    /**
     * Relasi ke User yang menginput
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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

    /**
     * Relasi ke Signature
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
     * Check if approved (by leader OR admin/superadmin)
     */
    public function isApproved()
    {
        // Approved if EITHER leader OR admin has approved
        return !is_null($this->leader_approved_at) || !is_null($this->approved_at);
    }
}
