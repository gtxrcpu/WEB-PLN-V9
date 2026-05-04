<?php

namespace App\Http\Controllers\Traits;

trait FiltersByUnit
{
    protected function getQueryForAuthUser($model)
    {
        $user = auth()->user();
        $query = $model::query();
        if ($user && $user->unit_id) {
            $query->forUnit($user->unit_id);
        } elseif ($user && !$user->unit_id && session('viewing_unit_id')) {
            $query->forUnit(session('viewing_unit_id'));
        }
        return $query;
    }

    protected function getAuthUserUnitId()
    {
        $user = auth()->user();
        if ($user && $user->unit_id) {
            return $user->unit_id;
        }
        if ($user && !$user->unit_id && session('viewing_unit_id')) {
            return session('viewing_unit_id');
        }
        return null;
    }

    protected function getCurrentViewingUnit()
    {
        $user = auth()->user();
        if ($user && $user->unit_id) {
            return $user->unit;
        }
        if ($user && !$user->unit_id && session('viewing_unit_id')) {
            return \App\Models\Unit::find(session('viewing_unit_id'));
        }
        return null;
    }

    protected function authorizeUnit($model)
    {
        $user = auth()->user();
        if ($user && $user->unit_id) {
            if (isset($model->unit_id) && $model->unit_id != $user->unit_id) {
                abort(403, 'Anda tidak memiliki akses ke data unit lain.');
            }
        }
    }
}
