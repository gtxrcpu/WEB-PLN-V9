<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait HasRevisionLogic
 *
 * Provides consistent revision number computation for all Kartu controllers.
 *
 * RULES:
 * - If the latest kartu for this equipment was rejected (by leader OR admin),
 *   the new kartu gets revisi = latest revisi + 1.
 * - Otherwise (first kartu, or latest was approved/pending), revisi = '00'.
 *
 * This ensures consistent behavior across APAR, APAT, APAB, Fire Alarm,
 * Box Hydrant, Rumah Pompa, and P3K modules.
 */
trait HasRevisionLogic
{
    /**
     * Compute the next revision number for a new kartu.
     *
     * @param  Model|null $latestKartu  The most recent kartu for this equipment
     * @return string  Two-digit zero-padded revision (e.g. '00', '01', '02')
     */
    protected function computeNextRevisi(?Model $latestKartu): string
    {
        if (!$latestKartu) {
            return '00';
        }

        // Check if latest kartu was rejected by EITHER leader or admin
        $wasRejected = !empty($latestKartu->rejected_at) || !empty($latestKartu->leader_rejected_at);

        if ($wasRejected) {
            // Increment from the latest revision
            $currentRevisi = (int) ($latestKartu->revisi ?? 0);
            return str_pad($currentRevisi + 1, 2, '0', STR_PAD_LEFT);
        }

        // Not rejected = fresh start
        return '00';
    }
}
