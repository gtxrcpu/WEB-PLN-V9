<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FireAlarm;
use App\Models\AparSetting;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class ResetFireAlarmData extends Command
{
    protected $signature = 'fire-alarm:reset {--force : Force reset without confirmation}';
    protected $description = 'Reset all Fire Alarm data and counters for each unit';

    public function handle()
    {
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║   RESET FIRE ALARM DATA & COUNTERS     ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->newLine();

        // Confirmation
        if (!$this->option('force')) {
            $this->warn('⚠️  WARNING: This will DELETE ALL Fire Alarm data!');
            $this->warn('   - All Fire Alarm records');
            $this->warn('   - All Kartu Fire Alarm records');
            $this->warn('   - Reset all counters to 001');
            $this->newLine();

            if (!$this->confirm('Do you want to continue?', false)) {
                $this->error('❌ Reset cancelled.');
                return 1;
            }
        }

        $this->newLine();
        $this->info('🔄 Starting reset process...');
        $this->newLine();

        try {
            // 1. Count existing data
            $fireAlarmCount = FireAlarm::count();
            $kartuCount = DB::table('kartu_fire_alarms')->count();

            $this->info("📊 Current data:");
            $this->line("   • Fire Alarm records: {$fireAlarmCount}");
            $this->line("   • Kartu Fire Alarm records: {$kartuCount}");
            $this->newLine();

            // 2. Disable foreign key checks
            $this->info('🔓 Disabling foreign key checks...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $this->line('   ✅ Foreign key checks disabled');

            // 3. Delete related data first
            $this->info('🗑️  Deleting related records...');
            DB::table('kartu_fire_alarms')->truncate();
            $this->line("   ✅ Deleted {$kartuCount} Kartu Fire Alarm records");

            // 4. Delete Fire Alarm data
            $this->info('🗑️  Deleting Fire Alarm records...');
            DB::table('fire_alarms')->truncate();
            $this->line("   ✅ Deleted {$fireAlarmCount} Fire Alarm records");

            // 5. Re-enable foreign key checks
            $this->info('🔒 Re-enabling foreign key checks...');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->line('   ✅ Foreign key checks re-enabled');

            $this->newLine();

            // 6. Reset counters
            $this->info('🔢 Resetting counters...');

            // Reset Induk
            AparSetting::set('fire_alarm_kode_counter_induk', 1);
            $this->line('   ✅ INDUK → 001');

            // Reset each unit
            $units = Unit::all();
            foreach ($units as $unit) {
                $counterKey = "fire_alarm_kode_counter_{$unit->id}";
                AparSetting::set($counterKey, 1);
                $this->line("   ✅ {$unit->name} ({$unit->code}) → 001");
            }

            $this->newLine();
            $this->info('╔════════════════════════════════════════╗');
            $this->info('║   ✨ RESET COMPLETE! ✨                ║');
            $this->info('╚════════════════════════════════════════╝');
            $this->newLine();

            $this->info('📊 Summary:');
            $this->line("   • Deleted: {$fireAlarmCount} Fire Alarm records");
            $this->line("   • Deleted: {$kartuCount} Kartu Fire Alarm records");
            $this->line('   • Reset: ' . ($units->count() + 1) . ' counters');
            $this->newLine();

            $this->info('✅ Ready to use!');

            return 0;

        } catch (\Exception $e) {
            // Make sure to re-enable foreign key checks even on error
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Exception $fkException) {
                // Ignore
            }

            $this->newLine();
            $this->error('❌ Error during reset:');
            $this->error('   ' . $e->getMessage());
            $this->newLine();
            return 1;
        }
    }
}
