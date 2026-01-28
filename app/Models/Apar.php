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
        if ($unitId === null && auth()->check() && auth()->user()->unit_id) {
            $unitId = auth()->user()->unit_id;
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
            // Extract number from last serial (e.g., "APAR-INDUK-005" -> 5)
            $parts = explode('-', $lastApar->serial_no);
            $lastNumber = isset($parts[2]) ? (int) ltrim($parts[2], '0') : 0;

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
            $qrCode = QrCode::format('svg')
                ->size(300)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($url);

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
        // Generate QR content with equipment info (not URL)
        $qrContent = json_encode([
            'type' => 'APAR',
            'code' => $this->barcode ?? $this->serial_no,
            'serial' => $this->serial_no,
            'location' => $this->location_code ?? '-',
            'status' => $this->status ?? '-',
            'capacity' => $this->capacity ?? '-',
            'type_detail' => $this->type ?? '-',
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
     * Generate QR Code as data URI (base64 encoded PNG)
     * This works on any hosting without file storage issues
     * Usage: <img src="{{ $apar->qr_data_uri }}" />
     * 
     * @return string Base64 data URI
     */
    public function getQrDataUriAttribute(): string
    {
        $url = route('apar.riwayat', $this->id);

        try {
            $qrCode = QrCode::format('png')
                ->size(300)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($url);

            $base64 = base64_encode($qrCode);
            return 'data:image/png;base64,' . $base64;
        } catch (\Exception $e) {
            // Fallback placeholder
            return 'data:image/svg+xml;base64,' . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300"><rect width="300" height="300" fill="#f3f4f6"/><text x="150" y="150" text-anchor="middle" font-size="14" fill="#6b7280">QR Code</text></svg>'
            );
        }
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

        try {
            $qrCode = QrCode::format('svg')
                ->size(300)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($url);

            $base64 = base64_encode($qrCode);
            return 'data:image/svg+xml;base64,' . $base64;
        } catch (\Exception $e) {
            return 'data:image/svg+xml;base64,' . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300"><rect width="300" height="300" fill="#f3f4f6"/><text x="150" y="150" text-anchor="middle" font-size="14" fill="#6b7280">QR Code</text></svg>'
            );
        }
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
