<?php

namespace App\Models;

use App\Models\Traits\HasUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class RumahPompa extends Model
{
    use HasUnit;

    protected $fillable = [
        'user_id',
        'unit_id',
        'serial_no',
        'barcode',
        'location_code',
        'type',
        'zone',
        'status',
        'qr_svg_path',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor: $rumahPompa->qr_url → Generate QR as SVG data URI
     */
    public function getQrUrlAttribute(): string
    {
        $url = \Illuminate\Support\Facades\URL::signedRoute('equipment.status', [
            'module' => 'rumah-pompa', 
            'id' => $this->id
        ]);

        return \App\Helpers\QrCodeHelper::generateVisualSvgDataUri($url, 'RUMAH POMPA', $this->serial_no);
    }

    /**
     * Generate next serial number for Rumah Pompa with unit-based format
     * Format: RUMAHPOMPA-{UNIT}-{NNN} (e.g., RUMAHPOMPA-UP2WIII-001)
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
        // Note: Use 'rumah-pompa' (with dash) to match admin panel key
        $format = \App\Models\AparSetting::getByUnit('rumah-pompa_kode_format', $unitId, 'RUMAHPOMPA-{UNIT}-{NNN}');
        $counter = (int) \App\Models\AparSetting::getByUnit('rumah-pompa_kode_counter', $unitId, 1);

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
        $lastRumahPompa = $query->orderByRaw('CAST(SUBSTRING_INDEX(serial_no, "-", -1) AS UNSIGNED) DESC')->first();

        if ($lastRumahPompa && $lastRumahPompa->serial_no) {
            $parts = explode('-', $lastRumahPompa->serial_no);
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
            \App\Models\AparSetting::setByUnit('rumah-pompa_kode_counter', $counter + $attempts + 1, $unitId);
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
            'module' => 'rumah-pompa',
            'id' => $this->id
        ]);

        try {
            $qrCode = \App\Helpers\QrCodeHelper::generateVisualSvg($url, 'RUMAH POMPA', $this->serial_no);

            $path = 'qrcodes/rumah-pompa/' . $this->serial_no . '.svg';
            Storage::disk('public')->put($path, $qrCode);

            $this->qr_svg_path = $path;
            $this->saveQuietly();
        } catch (\Exception $e) {
            \Log::error('Failed to generate QR for Rumah Pompa ' . $this->id . ': ' . $e->getMessage());
        }
    }

    public function kartuInspeksi()
    {
        return $this->hasMany(KartuRumahPompa::class)->latest('tgl_periksa');
    }

    public function kartuRumahPompas()
    {
        return $this->hasMany(KartuRumahPompa::class, 'rumah_pompa_id');
    }

    /**
     * Get the floor plan that this equipment belongs to
     */
    public function floorPlan()
    {
        return $this->belongsTo(FloorPlan::class);
    }
}
