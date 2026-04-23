<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\Apar;
use App\Models\Apat;
use App\Models\Apab;
use App\Models\P3k;
use App\Models\BoxHydrant;
use App\Models\FireAlarm;
use App\Models\RumahPompa;

class QrRegenerationController extends Controller
{
    public function index()
    {
        // Get counts for each equipment type
        $counts = [
            'apar' => Apar::count(),
            'apat' => Apat::count(),
            'apab' => Apab::count(),
            'p3k' => P3k::count(),
            'box_hydrant' => BoxHydrant::count(),
            'fire_alarm' => FireAlarm::count(),
            'rumah_pompa' => RumahPompa::count(),
        ];

        return view('admin.qr-regeneration', compact('counts'));
    }

    public function regenerate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:all,apar,apat,apab,p3k,box_hydrant,fire_alarm,rumah_pompa'
        ]);

        $type = $request->input('type');
        
        try {
            $stats = $this->performRegeneration($type);
            
            return response()->json([
                'success' => true,
                'message' => 'QR codes regenerated successfully',
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error regenerating QR codes: ' . $e->getMessage()
            ], 500);
        }
    }

    private function performRegeneration($type)
    {
        $stats = [
            'apar' => ['total' => 0, 'success' => 0, 'failed' => 0],
            'apat' => ['total' => 0, 'success' => 0, 'failed' => 0],
            'apab' => ['total' => 0, 'success' => 0, 'failed' => 0],
            'p3k' => ['total' => 0, 'success' => 0, 'failed' => 0],
            'box_hydrant' => ['total' => 0, 'success' => 0, 'failed' => 0],
            'fire_alarm' => ['total' => 0, 'success' => 0, 'failed' => 0],
            'rumah_pompa' => ['total' => 0, 'success' => 0, 'failed' => 0],
        ];

        $equipmentTypes = [
            'apar' => Apar::class,
            'apat' => Apat::class,
            'apab' => Apab::class,
            'p3k' => P3k::class,
            'box_hydrant' => BoxHydrant::class,
            'fire_alarm' => FireAlarm::class,
            'rumah_pompa' => RumahPompa::class,
        ];

        // Filter by type if not 'all'
        if ($type !== 'all') {
            $equipmentTypes = [$type => $equipmentTypes[$type]];
        }

        foreach ($equipmentTypes as $key => $modelClass) {
            $items = $modelClass::all();
            $stats[$key]['total'] = $items->count();
            
            foreach ($items as $item) {
                try {
                    // Force regeneration with new improved layout
                    $item->generateQrSvg(true);
                    $stats[$key]['success']++;
                } catch (\Exception $e) {
                    $stats[$key]['failed']++;
                    \Log::error("QR regeneration failed for {$item->serial_no}: " . $e->getMessage());
                }
            }
        }

        return $stats;
    }

    public function testQr()
    {
        try {
            // Generate test QR codes for validation
            $testUrl = url('/') . "/scan/test-qr-" . time();
            
            // Test basic QR
            $basicQr = \App\Helpers\QrCodeHelper::generateDataUri($testUrl);
            
            // Test visual QR
            $visualQr = \App\Helpers\QrCodeHelper::generateVisualSvg($testUrl, "TEST", "QR-VALIDATION");
            
            return response()->json([
                'success' => true,
                'basic_qr' => $basicQr,
                'visual_qr' => 'data:image/svg+xml;base64,' . base64_encode($visualQr),
                'test_url' => $testUrl
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating test QR: ' . $e->getMessage()
            ], 500);
        }
    }

    public function validateQr(Request $request)
    {
        $request->validate([
            'type' => 'required|in:apar,apat,apab,p3k,box_hydrant,fire_alarm,rumah_pompa',
            'limit' => 'integer|min:1|max:10'
        ]);

        $type = $request->input('type');
        $limit = $request->input('limit', 3);

        $modelClass = match($type) {
            'apar' => Apar::class,
            'apat' => Apat::class,
            'apab' => Apab::class,
            'p3k' => P3k::class,
            'box_hydrant' => BoxHydrant::class,
            'fire_alarm' => FireAlarm::class,
            'rumah_pompa' => RumahPompa::class,
        };

        try {
            $items = $modelClass::limit($limit)->get();
            $results = [];

            foreach ($items as $item) {
                $result = [
                    'serial_no' => $item->serial_no,
                    'has_qr_path' => !empty($item->qr_svg_path),
                    'qr_file_exists' => false,
                    'qr_url' => null,
                    'file_size' => 0,
                    'issues' => []
                ];

                if ($item->qr_svg_path) {
                    $fullPath = storage_path('app/public/' . $item->qr_svg_path);
                    $result['qr_file_exists'] = file_exists($fullPath);
                    
                    if ($result['qr_file_exists']) {
                        $result['file_size'] = filesize($fullPath);
                        $result['qr_url'] = asset('storage/' . $item->qr_svg_path);
                        
                        // Validate QR structure
                        $svgContent = file_get_contents($fullPath);
                        $result['issues'] = $this->validateQrStructure($svgContent);
                    }
                }

                $results[] = $result;
            }

            return response()->json([
                'success' => true,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error validating QR codes: ' . $e->getMessage()
            ], 500);
        }
    }

    private function validateQrStructure($svgContent)
    {
        $issues = [];
        
        // Check canvas dimensions
        if (preg_match('/width="(\d+)" height="(\d+)"/', $svgContent, $matches)) {
            $width = (int)$matches[1];
            $height = (int)$matches[2];
            
            if ($width < 300 || $height < 300) {
                $issues[] = "Canvas too small: {$width}x{$height}";
            }
        } else {
            $issues[] = "No canvas dimensions found";
        }
        
        // Check QR positioning
        if (preg_match('/<svg x="(\d+)" y="(\d+)" width="(\d+)" height="(\d+)"/', $svgContent, $matches)) {
            $qrX = (int)$matches[1];
            $qrY = (int)$matches[2];
            $qrW = (int)$matches[3];
            $qrH = (int)$matches[4];
            
            // Check if QR has adequate margins
            if ($qrX < 10) {
                $issues[] = "QR too close to left edge: x={$qrX}";
            }
            if ($qrY < 50) {
                $issues[] = "QR too close to top edge: y={$qrY}";
            }
            
            // Check QR size
            if ($qrW < 200 || $qrH < 200) {
                $issues[] = "QR too small: {$qrW}x{$qrH}";
            }
        } else {
            $issues[] = "QR positioning not found";
        }
        
        return $issues;
    }
}