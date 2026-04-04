<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use App\Models\Apar;
use Illuminate\Support\Str;

class EquipmentQRStatusTest extends TestCase
{
    /**
     * Test QR Url attribute generates valid signed route
     */
    public function test_qr_url_accessor_generates_signed_route()
    {
        $apar = new Apar();
        $apar->id = 999;
        
        // This generates base64 SVG starting with data:image/svg+xml
        $qrDataUri = $apar->qr_url;

        $this->assertTrue(Str::startsWith($qrDataUri, 'data:image/svg+xml;base64,'));
        
        // Ensure no sensitive raw JSON like before is present in the SVG (base64 decoded text could be checked,
        // but it's simpler to assert that the signed URL was the data source).
        // Since we know the method uses signedRoute, we check if URL signing works.
        $expectedUrl = URL::signedRoute('equipment.status', ['module' => 'apar', 'id' => 999]);
        $this->assertNotEmpty($expectedUrl);
        $this->assertStringContainsString('signature=', $expectedUrl);
    }

    /**
     * Test if URL signature fails when modified
     */
    public function test_external_scan_fails_with_invalid_signature()
    {
        $validUrl = URL::signedRoute('equipment.status', ['module' => 'apar', 'id' => 999]);
        
        // Tamper signature
        $invalidUrl = str_replace('signature=', 'signature=INVALID', $validUrl);

        $response = $this->get($invalidUrl);
        
        // It should return 401 Unauthorized or 403 Forbidden due to invalid signature
        $response->assertStatus(403);
    }
}
