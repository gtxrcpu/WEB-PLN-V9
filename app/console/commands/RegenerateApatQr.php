<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Apat;

class RegenerateApatQr extends Command
{
    protected $signature = 'apat:regenerate-qr';
    protected $description = 'Regenerate all APAT QR codes with correct field names';

    public function handle()
    {
        $this->info('Regenerating APAT QR codes...');

        $apats = Apat::all();
        $count = 0;

        foreach ($apats as $apat) {
            $apat->generateQrSvg(true);
            $count++;
            $this->line("  ✅ {$apat->serial_no}");
        }

        $this->newLine();
        $this->info("✨ Regenerated {$count} QR codes!");

        return 0;
    }
}
