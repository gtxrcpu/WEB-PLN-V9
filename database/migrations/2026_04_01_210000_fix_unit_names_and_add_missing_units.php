<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix unit names to use proper human-readable format
     * and ensure all required units exist without duplication.
     */
    public function up(): void
    {
        // -------------------------------------------------------
        // 1. Remove stale "UPW1" / "UP2W1" pattern duplicates that
        //    may exist in the DB with old incorrect codes/names.
        //    These are old entries that were incorrectly named.
        // -------------------------------------------------------
        $stalePatterns = ['UPW1', 'UPW2', 'UPW3', 'UPW4', 'UPW5', 'UPW6',
                          'UP2W1', 'UP2W2', 'UP2W3', 'UP2W4', 'UP2W5', 'UP2W6'];

        foreach ($stalePatterns as $code) {
            $stale = DB::table('units')->where('code', $code)->first();
            if ($stale) {
                // Find canonical replacement
                $canonical = null;
                if (in_array($code, ['UPW1', 'UP2W1'])) {
                    $canonical = DB::table('units')->where('code', 'UP2WI')->first();
                } elseif (in_array($code, ['UPW2', 'UP2W2'])) {
                    $canonical = DB::table('units')->where('code', 'UP2WII')->first();
                } elseif (in_array($code, ['UPW3', 'UP2W3'])) {
                    $canonical = DB::table('units')->where('code', 'UP2WIII')->first();
                } elseif (in_array($code, ['UPW4', 'UP2W4'])) {
                    $canonical = DB::table('units')->where('code', 'UP2WIV')->first();
                } elseif (in_array($code, ['UPW5', 'UP2W5'])) {
                    $canonical = DB::table('units')->where('code', 'UP2WV')->first();
                } elseif (in_array($code, ['UPW6', 'UP2W6'])) {
                    $canonical = DB::table('units')->where('code', 'UP2WVI')->first();
                }

                if ($canonical) {
                    // Migrate references to canonical id
                    DB::table('users')->where('unit_id', $stale->id)->update(['unit_id' => $canonical->id]);
                    $equipmentTables = ['apars', 'apats', 'apabs', 'fire_alarms', 'box_hydrants', 'rumah_pompas', 'p3ks', 'cctvs'];
                    foreach ($equipmentTables as $tbl) {
                        if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'unit_id')) {
                            DB::table($tbl)->where('unit_id', $stale->id)->update(['unit_id' => $canonical->id]);
                        }
                    }
                    DB::table('units')->where('id', $stale->id)->delete();
                }
            }
        }

        // -------------------------------------------------------
        // 2. Update existing unit names to human-readable format
        //    (with spaces, e.g. "UP2W I" instead of "UP2WI")
        // -------------------------------------------------------
        $nameMap = [
            'INDUK'   => ['name' => 'Induk',   'description' => 'Unit Induk (Pusat)'],
            'UP2WI'   => ['name' => 'UP2W I',  'description' => 'Unit Pelayanan dan Pengelolaan Wilayah I'],
            'UP2WII'  => ['name' => 'UP2W II', 'description' => 'Unit Pelayanan dan Pengelolaan Wilayah II'],
            'UP2WIII' => ['name' => 'UP2W III','description' => 'Unit Pelayanan dan Pengelolaan Wilayah III'],
            'UP2WIV'  => ['name' => 'UP2W IV', 'description' => 'Unit Pelayanan dan Pengelolaan Wilayah IV'],
            'UP2WV'   => ['name' => 'UP2W V',  'description' => 'Unit Pelayanan dan Pengelolaan Wilayah V'],
            'UP2WVI'  => ['name' => 'UP2W VI', 'description' => 'Unit Pelayanan dan Pengelolaan Wilayah VI'],
        ];

        foreach ($nameMap as $code => $data) {
            $existing = DB::table('units')->where('code', $code)->first();
            if ($existing) {
                // Update name and description
                DB::table('units')->where('id', $existing->id)->update([
                    'name'        => $data['name'],
                    'description' => $data['description'],
                    'updated_at'  => now(),
                ]);
            } else {
                // Insert if missing
                DB::table('units')->insert([
                    'code'        => $code,
                    'name'        => $data['name'],
                    'description' => $data['description'],
                    'is_active'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     * Restore old names (code == name format).
     */
    public function down(): void
    {
        $oldNames = [
            'INDUK'   => 'Induk',
            'UP2WI'   => 'UP2WI',
            'UP2WII'  => 'UP2WII',
            'UP2WIII' => 'UP2WIII',
            'UP2WIV'  => 'UP2WIV',
            'UP2WV'   => 'UP2WV',
            'UP2WVI'  => 'UP2WVI',
        ];

        foreach ($oldNames as $code => $name) {
            DB::table('units')->where('code', $code)->update(['name' => $name, 'updated_at' => now()]);
        }
    }
};
