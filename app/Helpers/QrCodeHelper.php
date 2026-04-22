<?php

namespace App\Helpers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeHelper
{
    /**
     * Generate QR Code as base64 data URI (PNG format)
     * This doesn't require file storage and works on any hosting
     * 
     * @param string $data The data to encode in QR
     * @param int $size Size of QR code (default 300)
     * @return string Base64 data URI
     */
    public static function generateDataUri(string $data, int $size = 300): string
    {
        try {
            // Generate QR code as PNG and encode to base64
            $qrCode = QrCode::format('png')
                ->size($size)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($data);
            
            // Convert to base64 data URI
            $base64 = base64_encode($qrCode);
            return 'data:image/png;base64,' . $base64;
        } catch (\Exception $e) {
            // Fallback: return placeholder
            return 'data:image/svg+xml;base64,' . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300"><rect width="300" height="300" fill="#f0f0f0"/><text x="150" y="150" text-anchor="middle" fill="#999">QR Error</text></svg>'
            );
        }
    }
    
    /**
     * Generate QR Code as SVG data URI
     * Smaller file size than PNG
     * 
     * @param string $data The data to encode in QR
     * @param int $size Size of QR code (default 300)
     * @return string SVG data URI
     */
    public static function generateSvgDataUri(string $data, int $size = 300): string
    {
        try {
            $qrCode = QrCode::format('svg')
                ->size($size)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($data);
            
            // Encode SVG to base64 data URI
            $base64 = base64_encode($qrCode);
            return 'data:image/svg+xml;base64,' . $base64;
        } catch (\Exception $e) {
            return 'data:image/svg+xml;base64,' . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300"><rect width="300" height="300" fill="#f0f0f0"/><text x="150" y="150" text-anchor="middle" fill="#999">QR Error</text></svg>'
            );
        }
    }

    /**
     * Generate custom visual QR code (physical 10x10 cm layout)
     * as SVG string with embedded logos at top right and bottom left.
     *
     * @param string $data The data to encode in QR
     * @param string|null $label Optional label to display below QR (e.g., "APAR", "APAT")
     * @param string|null $serialNumber Optional serial number to display (e.g., "APAR-UP2WII-002")
     * @return string Raw SVG content
     */
    public static function generateVisualSvg(string $data, ?string $label = null, ?string $serialNumber = null): string
    {
        try {
            // Setup base QR SVG code using standard SimpleSoftwareIO
            $qrSvg = QrCode::format('svg')
                ->size(300)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($data);

            // Paths for logos
            $plnLogoPath = public_path('images/logoo.png');
            $bumnLogoPath = public_path('images/bumn.png');

            // Embed logos as Base64 to ensure it renders on any setup
            $plnBase64 = '';
            if (file_exists($plnLogoPath)) {
                $plnType = pathinfo($plnLogoPath, PATHINFO_EXTENSION);
                $plnData = file_get_contents($plnLogoPath);
                $plnBase64 = 'data:image/' . $plnType . ';base64,' . base64_encode($plnData);
            }

            $bumnBase64 = '';
            if (file_exists($bumnLogoPath)) {
                $bumnType = pathinfo($bumnLogoPath, PATHINFO_EXTENSION);
                $bumnData = file_get_contents($bumnLogoPath);
                $bumnBase64 = 'data:image/' . $bumnType . ';base64,' . base64_encode($bumnData);
            }

            // Create wrapper SVG: 10cm x 10cm
            $wrapper = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="10cm" height="10cm" viewBox="0 0 360 360">
    <!-- Base Canvas -->
    <rect width="360" height="360" fill="#ffffff" />
    
    <!-- QRCode Matrix -->
    <svg x="30" y="60" width="300" height="260">
        ' . str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $qrSvg) . '
    </svg>';

            // Top Left Logo (BUMN)
            if (!empty($bumnBase64)) {
                $wrapper .= '
    <image href="' . $bumnBase64 . '" x="30" y="10" width="120" height="45" preserveAspectRatio="xMinYMid meet" />';
            } else {
                $wrapper .= '
    <!-- Placeholder BUMN Logo -->
    <rect x="30" y="10" width="120" height="45" fill="#f0f0f0" />
    <text x="90" y="37" font-family="sans-serif" font-size="14" font-weight="bold" fill="#666" text-anchor="middle">BUMN</text>';
            }

            // Top Right Logo (PLN)
            if (!empty($plnBase64)) {
                $wrapper .= '
    <image href="' . $plnBase64 . '" x="210" y="10" width="120" height="45" preserveAspectRatio="xMaxYMid meet" />';
            } else {
                $wrapper .= '
    <!-- Placeholder PLN Logo -->
    <rect x="210" y="10" width="120" height="45" fill="#f0f0f0" />
    <text x="270" y="37" font-family="sans-serif" font-size="14" font-weight="bold" fill="#666" text-anchor="middle">PLN</text>';
            }

            // Add label and serial number at bottom if provided
            if (!empty($label) || !empty($serialNumber)) {
                // Background bar for labels
                $wrapper .= '
    <!-- Label Background -->
    <rect x="30" y="325" width="300" height="30" fill="#1e40af" rx="4" />';
                
                if (!empty($label) && !empty($serialNumber)) {
                    // Both label and serial number
                    $wrapper .= '
    <!-- Equipment Type Label -->
    <text x="50" y="345" font-family="Arial, sans-serif" font-size="12" font-weight="bold" fill="#ffffff" text-anchor="start">' . htmlspecialchars($label) . '</text>
    <!-- Serial Number -->
    <text x="310" y="345" font-family="Arial, sans-serif" font-size="12" font-weight="bold" fill="#ffffff" text-anchor="end">' . htmlspecialchars($serialNumber) . '</text>';
                } elseif (!empty($serialNumber)) {
                    // Only serial number (centered)
                    $wrapper .= '
    <!-- Serial Number -->
    <text x="180" y="345" font-family="Arial, sans-serif" font-size="13" font-weight="bold" fill="#ffffff" text-anchor="middle">' . htmlspecialchars($serialNumber) . '</text>';
                } else {
                    // Only label (centered)
                    $wrapper .= '
    <!-- Equipment Type Label -->
    <text x="180" y="345" font-family="Arial, sans-serif" font-size="13" font-weight="bold" fill="#ffffff" text-anchor="middle">' . htmlspecialchars($label) . '</text>';
                }
            }

            $wrapper .= '
</svg>';

            return $wrapper;
        } catch (\Exception $e) {
            return '<svg xmlns="http://www.w3.org/2000/svg" width="10cm" height="10cm" viewBox="0 0 360 360"><rect width="360" height="360" fill="#f0f0f0"/><text x="180" y="180" text-anchor="middle" fill="#999">QR Error</text></svg>';
        }
    }

    /**
     * Generate custom visual QR code as Base64 Data URI
     *
     * @param string $data The data to encode in QR
     * @param string|null $label Optional label to display below QR (e.g., "APAR", "APAT")
     * @param string|null $serialNumber Optional serial number to display (e.g., "APAR-UP2WII-002")
     * @return string Base64 encoded Data URI
     */
    public static function generateVisualSvgDataUri(string $data, ?string $label = null, ?string $serialNumber = null): string
    {
        $svg = self::generateVisualSvg($data, $label, $serialNumber);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
