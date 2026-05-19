<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait AuthorizesEquipmentAccess
 *
 * Provides reusable authorization checks to ensure users can only
 * access/modify equipment belonging to their own unit (tenant scoping).
 *
 * Superadmin and Inspector roles bypass unit checks.
 */
trait AuthorizesEquipmentAccess
{
    /**
     * Verify that the authenticated user has access to the given equipment's unit.
     * Aborts with 403 if access is denied.
     *
     * @param  Model  $equipment  Any model with a unit_id column
     * @param  string $label      Human-readable label for error message
     * @return void
     */
    protected function authorizeEquipmentUnit(Model $equipment, string $label = 'equipment'): void
    {
        $user = auth()->user();

        // Superadmin and Inspector can access all units
        if ($user && $user->hasAnyRole(['superadmin', 'inspector'])) {
            return;
        }

        $userUnitId = $this->getAuthUserUnitId();

        // If user has a unit_id, equipment must belong to the same unit
        if ($userUnitId && $equipment->unit_id != $userUnitId) {
            $unitName = $equipment->unit ? $equipment->unit->name : 'lain';
            abort(403, "Anda tidak memiliki akses ke {$label} dari unit {$unitName}.");
        }
    }

    /**
     * Verify that a kartu record actually belongs to the given parent equipment.
     * Prevents IDOR where user supplies valid equipment ID but unrelated kartu ID.
     *
     * @param  Model  $kartu           The kartu record
     * @param  string $foreignKey      The FK column name on kartu (e.g. 'apar_id')
     * @param  int    $expectedParentId The expected parent equipment ID
     * @return void
     */
    protected function authorizeKartuBelongsToEquipment(Model $kartu, string $foreignKey, int $expectedParentId): void
    {
        if ((int) $kartu->{$foreignKey} !== $expectedParentId) {
            abort(404, 'Kartu tidak ditemukan untuk equipment ini.');
        }
    }
}
