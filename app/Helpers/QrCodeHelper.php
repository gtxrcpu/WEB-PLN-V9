<?php

namespace App\Helpers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeHelper
{
    /**
     * Generate QR Code as base64 data URI (PNG format)
     */
    public static function generateDataUri(string $data, int $size = 300): string
    {
        try {
            $qrCode = QrCode::format('png')
                ->size($size)
                ->margin(2)
                ->errorCorrection('H')
                ->generate($data);

            return 'data:image/png;base64,' . base64_encode($qrCode);
        } catch (\Exception $e) {
            return 'data:image/svg+xml;base64,' . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300"><rect width="300" height="300" fill="#f0f0f0"/><text x="150" y="150" text-anchor="middle" fill="#999">QR Error</text></svg>'
            );
        }
    }

    /**
     * Generate QR Code as SVG data URI
     */
    public static function generateSvgDataUri(string $data, int $size = 300): string
    {
        try {
            $qrCode = QrCode::format('svg')
                ->size($size)
                ->margin(2)
                ->errorCorrection('H')
                ->generate($data);

            return 'data:image/svg+xml;base64,' . base64_encode($qrCode);
        } catch (\Exception $e) {
            return 'data:image/svg+xml;base64,' . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300"><rect width="300" height="300" fill="#f0f0f0"/><text x="150" y="150" text-anchor="middle" fill="#999">QR Error</text></svg>'
            );
        }
    }

    /**
     * Generate custom visual QR code as SVG string.
     *
     * Layout (total canvas 400 x 480):
     *   - Logo bar  : y=0   h=50  (logos kiri & kanan)
     *   - Separator : y=50  h=8   (garis tipis)
     *   - QR area   : y=58  w=300 h=300  (centred, dengan quiet zone penuh)
     *   - Separator : y=358 h=8
     *   - Label bar : y=366 h=40  (teks jenis & serial)
     *   Total       : 406  → dibulatkan ke 410
     *
     * QR digenerate dengan margin=4 (quiet zone standar) sehingga
     * tidak ada elemen lain yang overlap area QR.
     */
    public static function generateVisualSvg(string $data, ?string $label = null, ?string $serialNumber = null): string
    {
        try {
            // ── 1. Generate QR dengan margin cukup (quiet zone 4 modul) ──────
            $qrSize = 300; // px ukuran QR murni
            $qrSvg  = QrCode::format('svg')
                ->size($qrSize)
                ->margin(4)          // quiet zone standar minimum
                ->errorCorrection('M') // M = ~15% recovery, lebih mudah discan dari H
                ->generate($data);

            // Hapus XML declaration agar bisa di-embed
            $qrSvg = preg_replace('/<\?xml[^?]*\?>/', '', $qrSvg);
            // Hapus atribut width/height dari root <svg> inner agar mengikuti viewport
            $qrSvg = preg_replace('/<svg([^>]*)width="[^"]*"([^>]*)height="[^"]*"/', '<svg$1$2', $qrSvg);

            // ── 2. Dimensi canvas ─────────────────────────────────────────────
            $canvasW   = 360;
            $logoH     = 50;   // tinggi area logo
            $sepH      = 6;    // garis pemisah
            $qrPad     = 10;   // padding kiri-kanan QR dalam canvas
            $qrAreaW   = $canvasW - ($qrPad * 2); // 340
            $qrAreaH   = $qrAreaW;                // bujur sangkar
            $labelH    = ($label || $serialNumber) ? 36 : 0;
            $canvasH   = $logoH + $sepH + $qrAreaH + $sepH + $labelH;

            $qrY = $logoH + $sepH;

            // ── 3. Embed logo sebagai base64 ──────────────────────────────────
            $plnBase64  = self::embedImage(public_path('images/logoo.png'));
            $bumnBase64 = self::embedImage(public_path('images/bumn.png'));

            // ── 4. Bangun SVG ─────────────────────────────────────────────────
            $svg  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $svg .= '<svg xmlns="http://www.w3.org/2000/svg"'
                  . ' xmlns:xlink="http://www.w3.org/1999/xlink"'
                  . ' width="' . $canvasW . '" height="' . $canvasH . '"'
                  . ' viewBox="0 0 ' . $canvasW . ' ' . $canvasH . '">' . "\n";

            // Background putih penuh
            $svg .= '  <rect width="' . $canvasW . '" height="' . $canvasH . '" fill="#ffffff"/>' . "\n";

            // ── Logo bar ──────────────────────────────────────────────────────
            if ($bumnBase64) {
                $svg .= '  <image href="' . $bumnBase64 . '" x="8" y="4" width="110" height="42" preserveAspectRatio="xMinYMid meet"/>' . "\n";
            } else {
                $svg .= '  <text x="63" y="32" font-family="sans-serif" font-size="13" font-weight="bold" fill="#555" text-anchor="middle">BUMN</text>' . "\n";
            }

            if ($plnBase64) {
                $svg .= '  <image href="' . $plnBase64 . '" x="242" y="4" width="110" height="42" preserveAspectRatio="xMaxYMid meet"/>' . "\n";
            } else {
                $svg .= '  <text x="297" y="32" font-family="sans-serif" font-size="13" font-weight="bold" fill="#555" text-anchor="middle">PLN</text>' . "\n";
            }

            // Garis pemisah atas
            $svg .= '  <line x1="0" y1="' . $logoH . '" x2="' . $canvasW . '" y2="' . $logoH . '" stroke="#e2e8f0" stroke-width="1"/>' . "\n";

            // ── QR Code ───────────────────────────────────────────────────────
            // Tempatkan QR di area penuh tanpa clipping — nested <svg> dengan
            // preserveAspectRatio agar QR tidak terdistorsi
            $svg .= '  <svg x="' . $qrPad . '" y="' . $qrY . '"'
                  . ' width="' . $qrAreaW . '" height="' . $qrAreaH . '"'
                  . ' viewBox="0 0 ' . $qrSize . ' ' . $qrSize . '"'
                  . ' preserveAspectRatio="xMidYMid meet">' . "\n";
            $svg .= '    ' . $qrSvg . "\n";
            $svg .= '  </svg>' . "\n";

            // Garis pemisah bawah (hanya jika ada label)
            if ($labelH > 0) {
                $sepY2 = $qrY + $qrAreaH;
                $svg .= '  <line x1="0" y1="' . $sepY2 . '" x2="' . $canvasW . '" y2="' . $sepY2 . '" stroke="#e2e8f0" stroke-width="1"/>' . "\n";

                // ── Label bar ─────────────────────────────────────────────────
                $labelY = $sepY2 + 1;
                $svg .= '  <rect x="0" y="' . $labelY . '" width="' . $canvasW . '" height="' . $labelH . '" fill="#1e3a8a"/>' . "\n";

                $textY = $labelY + 24; // baseline teks

                if ($label && $serialNumber) {
                    $svg .= '  <text x="12" y="' . $textY . '" font-family="Arial,sans-serif" font-size="13" font-weight="bold" fill="#ffffff" text-anchor="start">'
                          . htmlspecialchars($label) . '</text>' . "\n";
                    $svg .= '  <text x="' . ($canvasW - 12) . '" y="' . $textY . '" font-family="Arial,sans-serif" font-size="13" font-weight="bold" fill="#ffffff" text-anchor="end">'
                          . htmlspecialchars($serialNumber) . '</text>' . "\n";
                } elseif ($serialNumber) {
                    $svg .= '  <text x="' . ($canvasW / 2) . '" y="' . $textY . '" font-family="Arial,sans-serif" font-size="13" font-weight="bold" fill="#ffffff" text-anchor="middle">'
                          . htmlspecialchars($serialNumber) . '</text>' . "\n";
                } else {
                    $svg .= '  <text x="' . ($canvasW / 2) . '" y="' . $textY . '" font-family="Arial,sans-serif" font-size="13" font-weight="bold" fill="#ffffff" text-anchor="middle">'
                          . htmlspecialchars($label) . '</text>' . "\n";
                }
            }

            $svg .= '</svg>';

            return $svg;

        } catch (\Exception $e) {
            \Log::error('QrCodeHelper::generateVisualSvg error: ' . $e->getMessage());
            return '<svg xmlns="http://www.w3.org/2000/svg" width="360" height="360"><rect width="360" height="360" fill="#f0f0f0"/><text x="180" y="180" text-anchor="middle" fill="#999">QR Error</text></svg>';
        }
    }

    /**
     * Generate custom visual QR code as Base64 Data URI
     */
    public static function generateVisualSvgDataUri(string $data, ?string $label = null, ?string $serialNumber = null): string
    {
        $svg = self::generateVisualSvg($data, $label, $serialNumber);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Helper: embed image file as base64 data URI string.
     * Returns empty string if file not found.
     */
    private static function embedImage(string $path): string
    {
        if (!file_exists($path)) {
            return '';
        }
        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match($ext) {
            'png'  => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg'  => 'image/svg+xml',
            default => 'image/png',
        };
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }
}
