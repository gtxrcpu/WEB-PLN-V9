<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FireAlarm;

class RegenerateFireAlarmQr extends Command
{
    protected $signature = 'fire-alarm:regenerate-qr';
    protected $description = 'Regenerate all Fire Alarm QR codes';

    public function handle()
    {
        $this->info('Regenerating Fire Alarm QR codes...');

        $fireAlarms = FireAlarm::all();
        $count = 0;

        foreach ($fireAlarms as $fireAlarm) {
            $fireAlarm->generateQrSvg(true);
            $count++;
            $this->line("  ✅ {$fireAlarm->serial_no}");
        }

        $this->newLine();
        $this->info("✨ Regenerated {$count} QR codes!");

        return 0;
    }
}
