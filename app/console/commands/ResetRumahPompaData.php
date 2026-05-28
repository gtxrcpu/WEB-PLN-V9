<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RumahPompa;
use App\Models\AparSetting;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class ResetRumahPompaData extends Command
{
    protected $signature = 'rumah-pompa:reset {--force : Force reset without confirmation}';
    protected $description = 'Reset all Rumah Pompa data and counters for each unit';

    public function handle()
    {
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║   RESET RUMAH POMPA DATA & COUNTERS    ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->newLine();

        if (!$this->option('force')) {
            $this->warn('⚠️  WARNING: This will DELETE ALL Rumah Pompa data!');
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
            $rumahPompaCount = RumahPompa::count();
            $kartuCount = DB::table('kartu_rumah_pompas')->count();

            $this->info("📊 Current data:");
            $this->line("   • Rumah Pompa records: {$rumahPompaCount}");
            $this->line("   • Kartu Rumah Pompa records: {$kartuCount}");
            $this->newLine();

            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('kartu_rumah_pompas')->truncate();
            DB::table('rumah_pompas')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->newLine();
            $this->info('🔢 Resetting counters...');

            AparSetting::set('rumah_pompa_kode_counter_induk', 1);
            $this->line('   ✅ INDUK → 001');

            $units = Unit::all();
            foreach ($units as $unit) {
                $counterKey = "rumah_pompa_kode_counter_{$unit->id}";
                AparSetting::set($counterKey, 1);
                $this->line("   ✅ {$unit->name} ({$unit->code}) → 001");
            }

            $this->newLine();
            $this->info('✅ RESET COMPLETE! ✨');
            return 0;

        } catch (\Exception $e) {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Exception $fkException) {
            }

            $this->newLine();
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
