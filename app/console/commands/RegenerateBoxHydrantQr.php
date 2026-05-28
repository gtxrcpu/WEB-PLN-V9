<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BoxHydrant;

class RegenerateBoxHydrantQr extends Command
{
    protected $signature = 'box-hydrant:regenerate-qr';
    protected $description = 'Regenerate all Box Hydrant QR codes';

    public function handle()
    {
        $this->info('Regenerating Box Hydrant QR codes...');

        $boxHydrants = BoxHydrant::all();
        $count = 0;

        foreach ($boxHydrants as $boxHydrant) {
            $boxHydrant->generateQrSvg(true);
            $count++;
            $this->line("  ✅ {$boxHydrant->serial_no}");
        }

        $this->newLine();
        $this->info("✨ Regenerated {$count} QR codes!");

        return 0;
    }
}
