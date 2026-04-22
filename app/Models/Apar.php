<?php

namespace App\Models;

use App\Models\Traits\HasUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Apar extends Model
{
    use HasUnit;
    protected $table = 'apars';

    protected $fillable = [
        'user_id',
        'unit_id',
        'name',          // contoh: "APAR A1.001"
        'barcode',       // contoh: "APAR A1.001"
        'serial_no',     // contoh: "A1.001"
        'type',          // contoh: "UUV"
        'capacity',      // contoh: "5 Liter"
        'agent',         // contoh: "500"
        'location_code', // contoh: "BDG"
        'status',        // "BAIK" / "ISI ULANG" / "RUSAK"
        'notes',
        'qr_svg_path',
        'floor_plan_id',
        'floor_plan_x',
        'floor_plan_y',
    ];

    /**
     * Generate serial berikutnya berdasarkan format custom dari settings
     * 
     * @param int|null $unitId Unit ID (null = Induk)
     * @param bool $incrementCounter Whether to increment counter (default: true)
     * @return string Generated serial number
     */
    public static function generateNextSerial($unitId = null, $incrementCounter = true): string
    {
        // NEW FORMAT: Use unit-specific settings
        // Determine unit from auth user if not provided
        if ($unitId === null && auth()->check()) {
            if (auth()->user()->unit_id) {
                $unitId = auth()->user()->unit_id;
            } elseif (session('viewing_unit_id')) {
                $unitId = session('viewing_unit_id');
            }
        }

        // Get unit-specific format and counter
        $format = \App\Models\AparSetting::getByUnit('apar_kode_format', $unitId, 'APAR-{UNIT}-{NNN}');
        $counter = (int) \App\Models\AparSetting::getByUnit('apar_kode_counter', $unitId, 1);

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
        $lastApar = $query->orderByRaw('CAST(SUBSTRING_INDEX(serial_no, "-", -1) AS UNSIGNED) DESC')->first();

        if ($lastApar && $lastApar->serial_no) {
            // Extract number from last serial safely
            $parts = explode('-', $lastApar->serial_no);
            $lastStr = end($parts);
            $lastNumber = is_numeric($lastStr) ? (int) ltrim($lastStr, '0') : 0;

            // Use the higher value between counter and last number + 1
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

            // Check if this serial already exists IN THIS UNIT ONLY
            // Use where closure to properly group OR conditions with unit_id filter
            $duplicateQuery = self::where(function ($q) use ($serial) {
                $q->where('serial_no', $serial)->orWhere('barcode', $serial);
            });

            // Filter by unit_id to ensure independence between units
            if ($unitId) {
                $duplicateQuery->where('unit_id', $unitId);
            } else {
                $duplicateQuery->whereNull('unit_id');
            }

            $exists = $duplicateQuery->exists();

            if (!$exists) {
                break;
            }

            $attempts++;
        } while ($attempts < $maxRetries);

        // Increment counter only if requested
        if ($incrementCounter) {
            \App\Models\AparSetting::setByUnit('apar_kode_counter', $counter + $attempts + 1, $unitId);
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

        $url = route('apar.riwayat', $this->id);

        try {
            $qrCode = \App\Helpers\QrCodeHelper::generateVisualSvg($url, 'APAR', $this->serial_no);

            $filename = 'qrcodes/apar_' . $this->id . '.svg';
            Storage::disk('public')->put($filename, $qrCode);

            $this->update(['qr_svg_path' => $filename]);
        } catch (\Exception $e) {
            \Log::error('Failed to generate QR for APAR ' . $this->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Alias for generateQrSvg() for backward compatibility
     */
    public function refreshQrSvg(): void
    {
        $this->generateQrSvg(true);
    }

    /**
     * Accessor: $apar->qr_url → Generate QR as SVG data URI (no file, no HTTP request!)
     * This generates QR on-the-fly as base64 encoded SVG (works without imagick)
     */
    public function getQrUrlAttribute(): string
    {
        $url = \Illuminate\Support\Facades\URL::signedRoute('equipment.status', [
            'module' => 'apar', 
            'id' => $this->id
        ]);

        return \App\Helpers\QrCodeHelper::generateVisualSvgDataUri($url, 'APAR', $this->serial_no);
    }

    /**
     * Generate QR Code as data URI (base64 encoded PNG)
     * This works on any hosting without file storage issues
     * Usage: <img src="{{ $apar->qr_data_uri }}" />
     * 
     * @return string Base64 data URI
     */
    public function getQrDataUriAttribute(): string
    {
        $url = route('apar.riwayat', $this->id);

        return \App\Helpers\QrCodeHelper::generateVisualSvgDataUri($url, 'APAR', $this->serial_no);
    }

    /**
     * Generate QR Code as SVG data URI (smaller size, better quality)
     * Usage: <img src="{{ $apar->qr_svg_data_uri }}" />
     * 
     * @return string SVG data URI
     */
    public function getQrSvgDataUriAttribute(): string
    {
        $url = route('apar.riwayat', $this->id);

        return \App\Helpers\QrCodeHelper::generateVisualSvgDataUri($url, 'APAR', $this->serial_no);
    }

    /**
     * Relasi ke kartu kendali APAR
     */
    public function kartuApars()
    {
        return $this->hasMany(\App\Models\KartuApar::class, 'apar_id');
    }

    /**
     * Get the floor plan that this equipment belongs to
     */
    public function floorPlan()
    {
        return $this->belongsTo(FloorPlan::class);
    }
}
