<?php

namespace App\Console\Commands;

use App\Models\Apar;
use App\Models\AparSetting;
use Illuminate\Console\Command;

class RegenerateAparSerial extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apar:regenerate-serial {--dry-run : Show what would be changed without actually changing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate all APAR serial numbers with new unit-based format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        } else {
            $this->warn('⚠️  This will update all APAR serial numbers!');
            if (!$this->confirm('Do you want to continue?')) {
                $this->info('Cancelled.');
                return 0;
            }
        }

        $apars = Apar::with('unit')->get();
        $this->info("Found {$apars->count()} APAR records to process...");

        $updated = 0;
        $skipped = 0;
        $errors = 0;

        $this->withProgressBar($apars, function ($apar) use (&$updated, &$skipped, &$errors, $dryRun) {
            try {
                $oldSerial = $apar->serial_no;
                $oldBarcode = $apar->barcode;
                $unitId = $apar->unit_id;

                // Get unit code
                $unitCode = $apar->unit ? $apar->unit->code : 'INDUK';

                // Check if already in new format (contains unit code)
                if (strpos($oldSerial, $unitCode) !== false || strpos($oldSerial, 'UP2W') !== false) {
                    $skipped++;
                    return;
                }

                // Get format from settings for this unit
                $format = AparSetting::getByUnit('apar_kode_format', $unitId, 'APAR-{UNIT}-{NNN}');
                $counter = (int) AparSetting::getByUnit('apar_kode_counter', $unitId, 1);

                // Extract number from old serial if possible
                preg_match('/(\d+)$/', $oldSerial, $matches);
                $number = isset($matches[1]) ? (int) $matches[1] : $counter;

                // Generate new serial
                $newSerial = str_replace([
                    '{UNIT}',
                    '{YYYY}',
                    '{YY}',
                    '{MM}',
                    '{NNNN}',
                    '{NNN}',
                ], [
                    $unitCode,
                    date('Y'),
                    date('y'),
                    date('m'),
                    str_pad($number, 4, '0', STR_PAD_LEFT),
                    str_pad($number, 3, '0', STR_PAD_LEFT),
                ], $format);

                if (!$dryRun) {
                    $apar->update([
                        'serial_no' => $newSerial,
                        'barcode' => $newSerial,
                        'name' => $newSerial,
                    ]);

                    // Regenerate QR code
                    $apar->generateQrSvg(true);
                }

                $this->newLine();
                $this->line("  {$oldSerial} → {$newSerial} (Unit: {$unitCode})");

                $updated++;

            } catch (\Exception $e) {
                $this->newLine();
                $this->error("  Error processing APAR {$apar->id}: " . $e->getMessage());
                $errors++;
            }
        });

        $this->newLine(2);
        $this->info('✅ Process completed!');
        $this->table(
            ['Status', 'Count'],
            [
                ['Updated', $updated],
                ['Skipped (already new format)', $skipped],
                ['Errors', $errors],
            ]
        );

        if ($dryRun) {
            $this->warn('🔍 This was a DRY RUN. Run without --dry-run to apply changes.');
        } else {
            $this->info('🎉 All APAR serial numbers have been regenerated!');
        }

        return 0;
    }
}
