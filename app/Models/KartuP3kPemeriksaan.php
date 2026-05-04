<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KartuP3kPemeriksaan extends Model
{
    use HasFactory;

    protected $table = 'kartu_p3k_pemeriksaan';

    protected $fillable = [
        'nomor_kartu',
        'unit_id',
        'p3k_id',
        'user_id',
        'unit_kerja',
        'bulan_tahun',
        'revisi',
        'inspection_items',
        'checklist_items',
        'kesimpulan',
        'tgl_periksa',
        'petugas',
        'catatan',
        'approved_by',
        'approved_at',
        'signature_id',
        'leader_approved_by',
        'leader_approved_at',
        'leader_signature_id',
        'leader_rejected_by',
        'leader_rejected_at',
        'leader_rejection_reason',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'tgl_periksa' => 'date',
        'approved_at' => 'datetime',
        'checklist_items' => 'array',
        'inspection_items' => 'array',
    ];

    public function p3k()
    {
        return $this->belongsTo(P3k::class, 'p3k_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function signature()
    {
        return $this->belongsTo(Signature::class);
    }

    public function isApproved()
    {
        return !is_null($this->approved_at);
    }
}
