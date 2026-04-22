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

        return \App\Helpers\QrCodeHelper::generateVisualSvgDataUri($url, 'BOX HYDRANT', $this->serial_no);
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
     * Format: H6-{UNIT}-{NNN} (e.g., H6-UP2WI-001, H6-INDUK-001)
     * 
     * @param int|null $unitId Unit ID (null = Induk)
     * @param bool $incrementCounter Whether to increment counter (false = preview only)
     * @return string Generated serial number
     */
    public static function generateNextSerial($unitId = null, bool $incrementCounter = true): string
    {
        // Determine unit from auth user if not provided
        if ($unitId === null && auth()->check()) {
            if (auth()->user()->unit_id) {
                $unitId = auth()->user()->unit_id;
            } elseif (session('viewing_unit_id')) {
                $unitId = session('viewing_unit_id');
            }
        }

        // Get unit-specific format and counter (same as APAR)
        // Note: Use 'box-hydrant' (with dash) to match admin panel key
        $format = \App\Models\AparSetting::getByUnit('box-hydrant_kode_format', $unitId, 'H6-{UNIT}-{NNN}');
        $counter = (int) \App\Models\AparSetting::getByUnit('box-hydrant_kode_counter', $unitId, 1);

        // Get unit code for format
        $unit = $unitId ? \App\Models\Unit::find($unitId) : null;
        $unitCode = $unit ? $unit->code : 'INDUK';

        // Check existing data to ensure counter is in sync FOR THIS UNIT ONLY
        $query = self::query();
        if ($unitId) {
            $query->where('unit_id', $unitId);
        } else {
            $query->whereNull('unit_id');
        }

        // Get the highest serial number from existing data FOR THIS UNIT
        $lastBoxHydrant = $query->orderByRaw('CAST(SUBSTRING_INDEX(serial_no, "-", -1) AS UNSIGNED) DESC')->first();

        if ($lastBoxHydrant && $lastBoxHydrant->serial_no) {
            $parts = explode('-', $lastBoxHydrant->serial_no);
            $lastStr = end($parts);
            $lastNumber = is_numeric($lastStr) ? (int) ltrim($lastStr, '0') : 0;
            $counter = max($counter, $lastNumber + 1);
        }

        // Generate serial with retry logic for duplicate prevention
        $maxRetries = 10;
        $attempts = 0;

        do {
            $serial = str_replace([
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
                str_pad($counter + $attempts, 4, '0', STR_PAD_LEFT),
                str_pad($counter + $attempts, 3, '0', STR_PAD_LEFT),
            ], $format);

            // Check for duplicates within same unit
            $exists = static::query()
                ->where(function ($q) use ($serial) {
                    $q->where('serial_no', $serial)
                        ->orWhere('barcode', $serial);
                })
                ->when($unitId, fn($q) => $q->where('unit_id', $unitId), fn($q) => $q->whereNull('unit_id'))
                ->exists();

            if (!$exists) {
                break;
            }

            $attempts++;
        } while ($attempts < $maxRetries);

        // Increment counter only if requested
        if ($incrementCounter) {
            \App\Models\AparSetting::setByUnit('box-hydrant_kode_counter', $counter + $attempts + 1, $unitId);
        }

        return $serial;
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
            $qrCode = \App\Helpers\QrCodeHelper::generateVisualSvg($url, 'BOX HYDRANT', $this->serial_no);

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
