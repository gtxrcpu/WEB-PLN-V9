<?php

namespace App\Models;

use App\Models\Traits\HasApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartuFireAlarm extends Model
{
    use HasApprovalStatus;

    protected $guarded = [];

    protected $casts = [
        'tgl_periksa' => 'date',
        'leader_approved_at' => 'datetime',
        'leader_rejected_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function fireAlarm(): BelongsTo
    {
        return $this->belongsTo(FireAlarm::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function leaderApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_approved_by');
    }

    public function leaderRejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_rejected_by');
    }

    public function signature(): BelongsTo
    {
        return $this->belongsTo(Signature::class);
    }

    public function leaderSignature(): BelongsTo
    {
        return $this->belongsTo(Signature::class, 'leader_signature_id');
    }
}
