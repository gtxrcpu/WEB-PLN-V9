<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RumahPompa;

class RegenerateRumahPompaQr extends Command
{
    protected $signature = 'rumah-pompa:regenerate-qr';
    protected $description = 'Regenerate all Rumah Pompa QR codes';

    public function handle()
    {
        $this->info('Regenerating Rumah Pompa QR codes...');

        $rumahPompas = RumahPompa::all();
        $count = 0;

        foreach ($rumahPompas as $rumahPompa) {
            $rumahPompa->generateQrSvg(true);
            $count++;
            $this->line("  ✅ {$rumahPompa->serial_no}");
        }

        $this->newLine();
        $this->info("✨ Regenerated {$count} QR codes!");

        return 0;
    }
}
