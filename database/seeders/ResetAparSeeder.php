<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Apar;
use App\Models\AparSetting;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class ResetAparSeeder extends Seeder
{
    /**
     * Reset all APAR data and counters for each unit
     * Each unit (Induk, UP2WIII, UP2WIV) will have independent counters starting from 001
     */
    public function run(): void
    {
        $this->command->info('🔄 Resetting APAR data...');

        // 1. Disable foreign key checks
        $this->command->info('🔓 Disabling foreign key checks...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // 2. Delete all related data
        $this->command->info('📝 Deleting all related records...');

        // Delete Kartu Kendali APAR
        $kartuKendaliCount = DB::table('kartu_kendalis')->where('apar_id', '!=', null)->count();
        DB::table('kartu_kendalis')->where('apar_id', '!=', null)->delete();
        $this->command->info("   ✅ Deleted {$kartuKendaliCount} Kartu Kendali records");

        // Delete Kartu APAR
        $kartuCount = DB::table('kartu_apars')->count();
        DB::table('kartu_apars')->truncate();
        $this->command->info("   ✅ Deleted {$kartuCount} Kartu APAR records");

        // Delete APAR
        $deletedCount = Apar::count();
        DB::table('apars')->truncate();
        $this->command->info("   ✅ Deleted {$deletedCount} APAR records");

        // 3. Re-enable foreign key checks
        $this->command->info('🔒 Re-enabling foreign key checks...');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // 4. Reset counters for each unit
        $this->command->info('🔢 Resetting counters for each unit...');

        // Get all units
        $units = Unit::all();

        // Reset Induk (unit_id = null)
        AparSetting::set('apar_kode_counter_induk', 1);
        $this->command->info('   ✅ Reset counter for INDUK → 001');

        // Reset each unit
        foreach ($units as $unit) {
            $counterKey = "apar_kode_counter_{$unit->id}";
            AparSetting::set($counterKey, 1);
            $this->command->info("   ✅ Reset counter for {$unit->name} ({$unit->code}) → 001");
        }

        $this->command->info('');
        $this->command->info('✨ APAR Reset Complete!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info("   • Deleted: {$deletedCount} APAR records");
        $this->command->info("   • Deleted: {$kartuCount} Kartu APAR records");
        $this->command->info("   • Deleted: {$kartuKendaliCount} Kartu Kendali records");
        $this->command->info('   • Reset: ' . ($units->count() + 1) . ' counters (Induk + ' . $units->count() . ' units)');
        $this->command->info('');
        $this->command->info('🎯 Next Steps:');
        $this->command->info('   • Each unit now has independent counter starting from 001');
        $this->command->info('   • INDUK: APAR A1.001, A1.002, ...');
        $this->command->info('   • UP2WIII: APAR A1.001, A1.002, ...');
        $this->command->info('   • UP2WIV: APAR A1.001, A1.002, ...');
        $this->command->info('');
        $this->command->info('✅ You can now add new APAR for each unit!');
    }
}
