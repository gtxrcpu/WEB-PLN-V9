<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Apab;

class RegenerateApabQr extends Command
{
    protected $signature = 'apab:regenerate-qr';
    protected $description = 'Regenerate all APAB QR codes with correct field names';

    public function handle()
    {
        $this->info('Regenerating APAB QR codes...');

        $apabs = Apab::all();
        $count = 0;

        foreach ($apabs as $apab) {
            $apab->generateQrSvg(true);
            $count++;
            $this->line("  ✅ {$apab->serial_no}");
        }

        $this->newLine();
        $this->info("✨ Regenerated {$count} QR codes!");

        return 0;
    }
}
