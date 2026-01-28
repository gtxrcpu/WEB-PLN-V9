<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AparSetting;
use App\Models\Unit;
use Illuminate\Http\Request;

class KodeSettingController extends Controller
{
    private $modules = [
        'apar' => [
            'name' => 'APAR',
            'full_name' => 'Alat Pemadam Api Ringan',
            'icon' => 'images/apar.png',
        ],
        'apat' => [
            'name' => 'APAT',
            'full_name' => 'Alat Pemadam Api Tradisional',
            'icon' => 'images/apat.png',
        ],
        'apab' => [
            'name' => 'APAB',
            'full_name' => 'Alat Pemadam Api Berat',
            'icon' => 'images/apab.png',
        ],
        'fire-alarm' => [
            'name' => 'Fire Alarm',
            'full_name' => 'Fire Alarm System',
            'icon' => 'images/fire-alarm.png',
        ],
        'box-hydrant' => [
            'name' => 'Box Hydrant',
            'full_name' => 'Box Hydrant',
            'icon' => 'images/box-hydrant.png',
        ],
        'rumah-pompa' => [
            'name' => 'Rumah Pompa',
            'full_name' => 'Rumah Pompa',
            'icon' => 'images/box-hydrant.png',
        ],
        'p3k' => [
            'name' => 'P3K',
            'full_name' => 'Pertolongan Pertama Pada Kecelakaan',
            'icon' => 'images/p3k.png',
        ],
    ];

    public function index()
    {
        $modules = $this->modules;

        return view('admin.edit-kode.index', compact('modules'));
    }

    public function edit($module, Request $request)
    {
        if (!isset($this->modules[$module])) {
            abort(404);
        }

        // Get all units
        $units = Unit::orderBy('code')->get();

        // Get selected unit from query parameter or default to first unit
        $unitId = $request->query('unit_id');
        $selectedUnit = $unitId ? Unit::find($unitId) : $units->first();

        if (!$selectedUnit) {
            return redirect()->route('admin.edit-kode.index')
                ->with('error', 'No units found. Please create a unit first.');
        }

        $moduleInfo = $this->modules[$module];
        $settingKey = $module . '_kode_format';
        $counterKey = $module . '_kode_counter';

        $settings = [
            'kode_format' => AparSetting::where('key', $settingKey)
                ->where('unit_id', $selectedUnit->id)
                ->first(),
            'kode_counter' => AparSetting::where('key', $counterKey)
                ->where('unit_id', $selectedUnit->id)
                ->first(),
        ];

        // Set default jika belum ada (format seperti: APAR-UP2WIV-001)
        if (!$settings['kode_format']) {
            $defaultFormat = match ($module) {
                'apar' => 'APAR-{UNIT}-{NNN}',
                'apat' => 'APAT-{UNIT}-{NNN}',
                'apab' => 'APAB-{UNIT}-{NNN}',
                'fire-alarm' => 'FA-{UNIT}-{NNN}',
                'box-hydrant' => 'BH-{UNIT}-{NNN}',
                'rumah-pompa' => 'RP-{UNIT}-{NNN}',
                'p3k' => 'P3K-{UNIT}-{NNN}',
                default => strtoupper($module) . '-{UNIT}-{NNN}',
            };

            $settings['kode_format'] = (object) [
                'key' => $settingKey,
                'value' => $defaultFormat,
            ];
        }

        if (!$settings['kode_counter']) {
            $settings['kode_counter'] = (object) [
                'key' => $counterKey,
                'value' => '1',
            ];
        }

        return view('admin.edit-kode.edit', compact('module', 'moduleInfo', 'settings', 'units', 'selectedUnit'));
    }

    public function update(Request $request, $module)
    {
        if (!isset($this->modules[$module])) {
            abort(404);
        }

        $validated = $request->validate([
            'kode_format' => 'required|string|max:255',
            'kode_counter' => 'required|integer|min:1',
            'unit_id' => 'required|exists:units,id',
        ]);

        $settingKey = $module . '_kode_format';
        $counterKey = $module . '_kode_counter';

        AparSetting::updateOrCreate(
            ['key' => $settingKey, 'unit_id' => $validated['unit_id']],
            ['value' => $validated['kode_format'], 'type' => 'text']
        );

        AparSetting::updateOrCreate(
            ['key' => $counterKey, 'unit_id' => $validated['unit_id']],
            ['value' => $validated['kode_counter'], 'type' => 'number']
        );

        return redirect()
            ->route('admin.edit-kode.edit', ['module' => $module, 'unit_id' => $validated['unit_id']])
            ->with('success', 'Settings ' . $this->modules[$module]['name'] . ' berhasil diupdate');
    }

    public function resetCounter(Request $request, $module)
    {
        if (!isset($this->modules[$module])) {
            abort(404);
        }

        $unitId = $request->input('unit_id');
        $counterKey = $module . '_kode_counter';

        AparSetting::updateOrCreate(
            ['key' => $counterKey, 'unit_id' => $unitId],
            ['value' => '1', 'type' => 'number']
        );

        return redirect()
            ->route('admin.edit-kode.edit', ['module' => $module, 'unit_id' => $unitId])
            ->with('success', 'Counter berhasil direset ke 1');
    }
}
