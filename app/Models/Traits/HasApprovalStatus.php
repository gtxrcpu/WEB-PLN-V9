<?php

namespace App\Models\Traits;

/**
 * Trait HasApprovalStatus
 *
 * Provides consistent approval status logic across ALL kartu models.
 *
 * RULE: A kartu is considered "approved" if EITHER:
 * - leader_approved_at is set (leader has approved), OR
 * - approved_at is set (admin/superadmin has approved)
 *
 * This ensures consistent behavior across APAR, APAT, APAB, Fire Alarm,
 * Box Hydrant, Rumah Pompa, AND P3K (Pemeriksaan/Pemakaian/Stock).
 */
trait HasApprovalStatus
{
    /**
     * Check if this kartu has been approved (by leader OR admin/superadmin).
     */
    public function isApproved(): bool
    {
        return !is_null($this->leader_approved_at) || !is_null($this->approved_at);
    }

    /**
     * Check if this kartu is pending (not yet approved and not rejected).
     */
    public function isPending(): bool
    {
        return !$this->isApproved() && !$this->isRejected();
    }

    /**
     * Check if this kartu has been rejected (by leader OR admin).
     */
    public function isRejected(): bool
    {
        return !is_null($this->leader_rejected_at) || !is_null($this->rejected_at);
    }

    /**
     * Get the human-readable approval status.
     */
    public function getApprovalStatusAttribute(): string
    {
        if ($this->isRejected()) {
            return 'rejected';
        }
        if ($this->isApproved()) {
            return 'approved';
        }
        return 'pending';
    }
}
