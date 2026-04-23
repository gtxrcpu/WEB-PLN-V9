<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Apar;
use App\Models\Apat;
use App\Models\Apab;
use App\Models\P3k;
use App\Models\BoxHydrant;
use App\Models\FireAlarm;
use App\Models\RumahPompa;

class RegenerateQrCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr:regenerate {--type=all : Equipment type to regenerate (all, apar, apat, apab, p3k, box_hydrant, fire_alarm, rumah_pompa)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate QR codes for all equipment with improved scanning layout';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        
        $this->info('=== QR CODE REGENERATION ===');
        $this->info('Fixing scanning issues with improved layout');
        $this->newLine();

        $stats = [
            'apar' => ['total' => 0, 'success' => 0, 'failed' => 0],
            'apat' => ['total' => 0, 'success' => 0, 'failed' => 0],
            'apab' => ['total' => 0, 'success' => 0, 'failed' => 0],
            'p3k' => ['total' => 0, 'success' => 0, 'failed' => 0],
            'box_hydrant' => ['total' => 0, 'success' => 0, 'failed' => 0],
            'fire_alarm' => ['total' => 0, 'success' => 0, 'failed' => 0],
            'rumah_pompa' => ['total' => 0, 'success' => 0, 'failed' => 0],
        ];

        // Define equipment types to process
        $equipmentTypes = [
            'apar' => [Apar::class, 'APAR Equipment'],
            'apat' => [Apat::class, 'APAT Equipment'],
            'apab' => [Apab::class, 'APAB Equipment'],
            'p3k' => [P3k::class, 'P3K Equipment'],
            'box_hydrant' => [BoxHydrant::class, 'Box Hydrant Equipment'],
            'fire_alarm' => [FireAlarm::class, 'Fire Alarm Equipment'],
            'rumah_pompa' => [RumahPompa::class, 'Rumah Pompa Equipment'],
        ];

        // Filter by type if specified
        if ($type !== 'all' && isset($equipmentTypes[$type])) {
            $equipmentTypes = [$type => $equipmentTypes[$type]];
        } elseif ($type !== 'all') {
            $this->error("Invalid type: {$type}");
            $this->info("Valid types: all, " . implode(', ', array_keys($equipmentTypes)));
            return 1;
        }

        // Process each equipment type
        foreach ($equipmentTypes as $key => [$modelClass, $label]) {
            $this->regenerateModelQr($modelClass, $label, $stats, $key);
        }

        // Display summary
        $this->displaySummary($stats);
        
        return 0;
    }

    private function regenerateModelQr($modelClass, $label, &$stats, $key)
    {
        $this->info("Processing {$label}...");
        
        try {
            $items = $modelClass::all();
            $stats[$key]['total'] = $items->count();
            
            if ($items->isEmpty()) {
                $this->warn("  No {$label} items found");
                return;
            }

            $progressBar = $this->output->createProgressBar($items->count());
            $progressBar->start();
            
            foreach ($items as $item) {
                try {
                    // Force regeneration with new improved layout
                    $item->generateQrSvg(true);
                    $stats[$key]['success']++;
                } catch (\Exception $e) {
                    $stats[$key]['failed']++;
                    $this->newLine();
                    $this->error("  Failed {$item->serial_no}: " . $e->getMessage());
                }
                $progressBar->advance();
            }
            
            $progressBar->finish();
            $this->newLine();
            $this->info("  Completed: {$stats[$key]['success']}/{$stats[$key]['total']} successful");
            
        } catch (\Exception $e) {
            $this->error("  Failed to process {$label}: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function displaySummary($stats)
    {
        $this->info('=== REGENERATION SUMMARY ===');
        
        $totalItems = 0;
        $totalSuccess = 0;
        $totalFailed = 0;

        $headers = ['Equipment Type', 'Success', 'Total', 'Percentage'];
        $rows = [];

        foreach ($stats as $type => $data) {
            $totalItems += $data['total'];
            $totalSuccess += $data['success'];
            $totalFailed += $data['failed'];
            
            if ($data['total'] > 0) {
                $percentage = round(($data['success'] / $data['total']) * 100, 1);
                $rows[] = [
                    strtoupper(str_replace('_', ' ', $type)),
                    $data['success'],
                    $data['total'],
                    $percentage . '%'
                ];
            }
        }

        if (!empty($rows)) {
            $rows[] = ['---', '---', '---', '---'];
            $rows[] = [
                'TOTAL',
                $totalSuccess,
                $totalItems,
                $totalItems > 0 ? round(($totalSuccess / $totalItems) * 100, 1) . '%' : '0%'
            ];
        }

        $this->table($headers, $rows);

        if ($totalFailed > 0) {
            $this->warn("{$totalFailed} items failed to regenerate. Check error messages above.");
        }

        $this->newLine();
        $this->info('=== QR CODE IMPROVEMENTS ===');
        $this->line('✓ Error correction level changed from H to M (better scanning)');
        $this->line('✓ Proper quiet zone (4 modules minimum) implemented');
        $this->line('✓ QR area kept completely free from overlapping elements');
        $this->line('✓ Improved visual layout with proper spacing');
        $this->line('✓ Logo positioning optimized to avoid QR interference');

        $this->newLine();
        $this->info('=== TESTING RECOMMENDATIONS ===');
        $this->line('1. Test QR codes with multiple scanner apps:');
        $this->line('   - Built-in camera apps (iOS/Android)');
        $this->line('   - QR Code Reader apps');
        $this->line('   - Barcode Scanner apps');
        $this->line('2. Test in different lighting conditions');
        $this->line('3. Test at various distances and angles');
        $this->line('4. Verify all equipment types are scannable');
    }
}