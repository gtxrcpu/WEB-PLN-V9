<?php

namespace App\Models;

use App\Models\Traits\HasUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Apab extends Model
{
    use HasFactory, HasUnit;

    protected $guarded = [];

    protected $casts = [
        'masa_berlaku' => 'date',
    ];

    protected static function booted(): void
    {
        // No auto-generation here - controller will call generateNextSerial explicitly
    }

    /**
     * Accessor: $apab->qr_url → Generate QR as SVG data URI (no file, no HTTP request!)
     * This generates QR on-the-fly as base64 encoded SVG (works without imagick)
     */
    public function getQrUrlAttribute(): string
    {
        // Generate QR content with equipment info (not URL)
        $qrContent = json_encode([
            'type' => 'APAB',
            'code' => $this->barcode ?? $this->serial_no,
            'serial' => $this->serial_no,
            'location' => $this->lokasi ?? '-',  // Fixed: lokasi not location_code
            'status' => $this->status ?? '-',
            'capacity' => $this->kapasitas ?? '-',  // Fixed: kapasitas not capacity
            'type_detail' => $this->jenis ?? '-',  // Fixed: jenis not type
        ], JSON_UNESCAPED_UNICODE);

        try {
            $qrCode = QrCode::format('svg')
                ->size(300)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($qrContent);

            $base64 = base64_encode($qrCode);
            return 'data:image/svg+xml;base64,' . $base64;
        } catch (\Exception $e) {
            // Fallback placeholder
            return 'data:image/svg+xml;base64,' . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300"><rect width="300" height="300" fill="#f3f4f6"/><text x="150" y="150" text-anchor="middle" font-size="14" fill="#6b7280">QR Error</text></svg>'
            );
        }
    }

    /**
     * Generate next serial number for APAB with unit-based format
     * Format: APAB-{UNIT}-{NNN} (e.g., APAB-UP2WIII-001, APAB-INDUK-001)
     * 
     * @param int|null $unitId Unit ID (null = Induk)
     * @param bool $incrementCounter Whether to increment counter (false = preview only)
     * @return string Generated serial number
     */
    public static function generateNextSerial($unitId = null, bool $incrementCounter = true): string
    {
        // Format from settings or default
        $format = \App\Models\AparSetting::get('apab_kode_format', 'APAB-{UNIT}-{NNN}');

        // Determine unit from auth user if not provided
        if ($unitId === null && auth()->check() && auth()->user()->unit_id) {
            $unitId = auth()->user()->unit_id;
        }

        // Get unit name for format
        $unitName = $unitId ? (\App\Models\Unit::find($unitId)?->name ?? 'INDUK') : 'INDUK';

        // Counter key based on unit (per-unit independent counter)
        $counterKey = $unitId ? "apab_kode_counter_{$unitId}" : "apab_kode_counter_induk";
        $currentCounter = (int) \App\Models\AparSetting::get($counterKey, 1);

        // Try up to 10 times to find unique serial
        $maxAttempts = 10;
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $tryCounter = $currentCounter + $attempt;

            // Replace placeholders
            $serial = str_replace([
                '{UNIT}',
                '{NNNN}',
                '{NNN}',
            ], [
                $unitName,
                str_pad($tryCounter, 4, '0', STR_PAD_LEFT),
                str_pad($tryCounter, 3, '0', STR_PAD_LEFT),
            ], $format);

            // Check for duplicates within same unit
            $exists = static::query()
                ->where(function ($q) use ($serial) {
                    $q->where('serial_no', $serial)
                        ->orWhere('barcode', $serial);
                })
                ->where('unit_id', $unitId)
                ->exists();

            if (!$exists) {
                // Found unique serial!
                if ($incrementCounter) {
                    // Update counter for next time
                    \App\Models\AparSetting::set($counterKey, $tryCounter + 1);
                }
                return $serial;
            }
        }

        // If all attempts failed, use timestamp fallback
        $fallback = str_replace(['{UNIT}', '{NNN}'], [$unitName, date('His')], 'APAB-{UNIT}-{NNN}');

        if ($incrementCounter) {
            \App\Models\AparSetting::set($counterKey, $currentCounter + $maxAttempts + 1);
        }

        return $fallback;
    }

    /**
     * Generate and save QR code as SVG file
     */
    public function generateQrSvg($force = false): void
    {
        if (!$force && $this->qr_svg_path && Storage::disk('public')->exists($this->qr_svg_path)) {
            return;
        }

        $url = route('apab.riwayat', $this->id);

        try {
            $qrCode = QrCode::format('svg')
                ->size(300)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($url);

            $path = 'qrcodes/apab/' . $this->serial_no . '.svg';
            Storage::disk('public')->put($path, $qrCode);

            $this->qr_svg_path = $path;
            $this->saveQuietly();
        } catch (\Exception $e) {
            \Log::error('Failed to generate QR for APAB: ' . $e->getMessage());
        }
    }

    /**
     * Alias for generateQrSvg() for backward compatibility
     */
    public function refreshQrSvg(): void
    {
        $this->generateQrSvg(true);
    }

    public function kartuInspeksi()
    {
        return $this->hasMany(KartuApab::class)->latest('tgl_periksa');
    }

    public function kartuApabs()
    {
        return $this->hasMany(KartuApab::class, 'apab_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the floor plan that this equipment belongs to
     */
    public function floorPlan()
    {
        return $this->belongsTo(FloorPlan::class);
    }
}
