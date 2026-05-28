<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Apat;
use App\Models\AparSetting;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class ResetApatData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'apat:reset {--force : Force reset without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Reset all APAT data and counters for each unit (Induk, UP2WIII, UP2WIV)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║   RESET APAT DATA & COUNTERS           ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->newLine();

        // Confirmation
        if (!$this->option('force')) {
            $this->warn('⚠️  WARNING: This will DELETE ALL APAT data!');
            $this->warn('   - All APAT records');
            $this->warn('   - All Kartu APAT records');
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
            $apatCount = Apat::count();
            $kartuCount = DB::table('kartu_apats')->count();

            $this->info("📊 Current data:");
            $this->line("   • APAT records: {$apatCount}");
            $this->line("   • Kartu APAT records: {$kartuCount}");
            $this->newLine();

            // 2. Disable foreign key checks
            $this->info('🔓 Disabling foreign key checks...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $this->line('   ✅ Foreign key checks disabled');

            // 3. Delete related data first
            $this->info('🗑️  Deleting related records...');

            // Delete Kartu APAT
            DB::table('kartu_apats')->truncate();
            $this->line("   ✅ Deleted {$kartuCount} Kartu APAT records");

            // 4. Delete APAT data
            $this->info('🗑️  Deleting APAT records...');
            DB::table('apats')->truncate();
            $this->line("   ✅ Deleted {$apatCount} APAT records");

            // 5. Re-enable foreign key checks
            $this->info('🔒 Re-enabling foreign key checks...');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->line('   ✅ Foreign key checks re-enabled');

            $this->newLine();

            // 6. Reset counters
            $this->info('🔢 Resetting counters...');

            // Reset Induk
            AparSetting::set('apat_kode_counter_induk', 1);
            $this->line('   ✅ INDUK → 001');

            // Reset each unit
            $units = Unit::all();
            foreach ($units as $unit) {
                $counterKey = "apat_kode_counter_{$unit->id}";
                AparSetting::set($counterKey, 1);
                $this->line("   ✅ {$unit->name} ({$unit->code}) → 001");
            }

            $this->newLine();
            $this->info('╔════════════════════════════════════════╗');
            $this->info('║   ✨ RESET COMPLETE! ✨                ║');
            $this->info('╚════════════════════════════════════════╝');
            $this->newLine();

            $this->info('📊 Summary:');
            $this->line("   • Deleted: {$apatCount} APAT records");
            $this->line("   • Deleted: {$kartuCount} Kar tu APAT records");
            $this->line('   • Reset: ' . ($units->count() + 1) . ' counters');
            $this->newLine();

            $this->info('🎯 Next Steps:');
            $this->line('   • Each unit now has independent counter');
            $this->line('   • All units start from 001');
            $this->line('   • You can add new APAT for each unit');
            $this->newLine();

            $this->info('✅ Ready to use!');

            return 0;

        } catch (\Exception $e) {
            // Make sure to re-enable foreign key checks even on error
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Exception $fkException) {
                // Ignore if already enabled or other issue
            }

            $this->newLine();
            $this->error('❌ Error during reset:');
            $this->error('   ' . $e->getMessage());
            $this->newLine();
            return 1;
        }
    }
}
