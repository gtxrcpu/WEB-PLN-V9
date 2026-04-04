<?php

namespace App\Models;

use App\Models\Traits\HasUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class BoxHydrant extends Model
{
    use HasUnit;

    protected $fillable = [
        'user_id',
        'unit_id',
        'name',
        'barcode',
        'serial_no',
        'location_code',
        'type',
        'status',
        'notes',
        'qr_svg_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor: $boxHydrant->qr_url → Generate QR as SVG data URI (no file, no HTTP request!)
     * This generates QR on-the-fly as base64 encoded SVG (works without imagick)
     */
    public function getQrUrlAttribute(): string
    {
        $url = \Illuminate\Support\Facades\URL::signedRoute('equipment.status', [
            'module' => 'box-hydrant', 
            'id' => $this->id
        ]);

        return \App\Helpers\QrCodeHelper::generateVisualSvgDataUri($url);
    }

    public function refreshQrSvg(): void
    {
        // keeping logic intact if it generates simple qr for other specific cases
        $qrContent = $this->barcode ?? $this->serial_no ?? 'H6-' . $this->id;

        $svg = QrCode::size(300)
            ->format('svg')
            ->generate($qrContent);

        $filename = 'box-hydrant-' . $this->id . '.svg';
        $path = 'qr/' . $filename;

        Storage::disk('public')->put($path, $svg);

        $this->qr_svg_path = 'storage/' . $path;
        $this->saveQuietly();
    }

    /**
     * Generate next serial number for Box Hydrant with unit-based format
     * Format: BOXHYDRANT-{UNIT}-{NNN} (e.g., BOX HYDRANT-UP2WIII-001, BOXHYDRANT-INDUK-001)
     * 
     * @param int|null $unitId Unit ID (null = Induk)
     * @param bool $incrementCounter Whether to increment counter (false = preview only)
     * @return string Generated serial number
     */
    public static function generateNextSerial($unitId = null, bool $incrementCounter = true): string
    {
        // Format from settings or default
        $format = \App\Models\AparSetting::get('box_hydrant_kode_format', 'BOXHYDRANT-{UNIT}-{NNN}');

        // Determine unit from auth user if not provided
        if ($unitId === null && auth()->check() && auth()->user()->unit_id) {
            $unitId = auth()->user()->unit_id;
        }

        // Get unit name for format
        $unitName = $unitId ? (\App\Models\Unit::find($unitId)?->name ?? 'INDUK') : 'INDUK';

        // Counter key based on unit (per-unit independent counter)
        $counterKey = $unitId ? "box_hydrant_kode_counter_{$unitId}" : "box_hydrant_kode_counter_induk";
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
        $fallback = str_replace(['{UNIT}', '{NNN}'], [$unitName, date('His')], 'BOXHYDRANT-{UNIT}-{NNN}');

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

        $url = \Illuminate\Support\Facades\URL::signedRoute('equipment.status', [
            'module' => 'box-hydrant',
            'id' => $this->id
        ]);

        try {
            $qrCode = \App\Helpers\QrCodeHelper::generateVisualSvg($url);

            $path = 'qrcodes/box-hydrant/' . $this->serial_no . '.svg';
            Storage::disk('public')->put($path, $qrCode);

            $this->qr_svg_path = $path;
            $this->saveQuietly();
        } catch (\Exception $e) {
            \Log::error('Failed to generate QR for Box Hydrant ' . $this->id . ': ' . $e->getMessage());
        }
    }

    public function kartuInspeksi()
    {
        return $this->hasMany(KartuBoxHydrant::class)->latest('tgl_periksa');
    }

    public function kartuBoxHydrants()
    {
        return $this->hasMany(KartuBoxHydrant::class, 'box_hydrant_id');
    }

    /**
     * Get the floor plan that this equipment belongs to
     */
    public function floorPlan()
    {
        return $this->belongsTo(FloorPlan::class);
    }
}
