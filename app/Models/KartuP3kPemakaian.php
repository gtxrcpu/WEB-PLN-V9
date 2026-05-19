<?php

namespace App\Models;

use App\Models\Traits\HasApprovalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KartuP3kPemakaian extends Model
{
    use HasFactory, HasApprovalStatus;

    protected $table = 'kartu_p3k_pemakaian';

    protected $fillable = [
        'nomor_kartu',
        'unit_id',
        'p3k_id',
        'user_id',
        'bulan',
        'nomor',
        'lokasi',
        'revisi',
        'usage_entries',
        'item_digunakan',
        'jumlah',
        'keperluan',
        'nama_pengguna',
        'kesimpulan',
        'tgl_pemakaian',
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
        'tgl_pemakaian' => 'date',
        'approved_at' => 'datetime',
        'leader_approved_at' => 'datetime',
        'leader_rejected_at' => 'datetime',
        'rejected_at' => 'datetime',
        'usage_entries' => 'array',
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

    public function leaderApprover()
    {
        return $this->belongsTo(User::class, 'leader_approved_by');
    }

    public function leaderRejector()
    {
        return $this->belongsTo(User::class, 'leader_rejected_by');
    }

    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function signature()
    {
        return $this->belongsTo(Signature::class);
    }
}
