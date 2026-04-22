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
     * Generate serial berikutnya berdasarkan format custom dari settings
     * 
     * @param int|null $unitId Unit ID (null = Induk)
     * @param bool $incrementCounter Whether to increment counter (default: true)
     * @return string Generated serial number
     */
    public static function generateNextSerial($unitId = null, $incrementCounter = true): string
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
        // Note: Key matches admin panel (no dash needed for apab)
        $format = \App\Models\AparSetting::getByUnit('apab_kode_format', $unitId, 'APAB-{UNIT}-{NNN}');
        $counter = (int) \App\Models\AparSetting::getByUnit('apab_kode_counter', $unitId, 1);

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
        $lastApab = $query->orderByRaw('CAST(SUBSTRING_INDEX(serial_no, "-", -1) AS UNSIGNED) DESC')->first();

        if ($lastApab && $lastApab->serial_no) {
            // Extract number from last serial safely
            $parts = explode('-', $lastApab->serial_no);
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
            \App\Models\AparSetting::setByUnit('apab_kode_counter', $counter + $attempts + 1, $unitId);
        }

        return $serial;
    }

    /**
     * Accessor: $apab->qr_url → Generate QR as SVG data URI (no file, no HTTP request!)
     * This generates QR on-the-fly as base64 encoded SVG (works without imagick)
     */
    public function getQrUrlAttribute(): string
    {
        $url = \Illuminate\Support\Facades\URL::signedRoute('equipment.status', [
            'module' => 'apab', 
            'id' => $this->id
        ]);

        return \App\Helpers\QrCodeHelper::generateVisualSvgDataUri($url, 'APAB', $this->serial_no);
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
            'module' => 'apab',
            'id' => $this->id
        ]);

        try {
            $qrCode = \App\Helpers\QrCodeHelper::generateVisualSvg($url, 'APAB', $this->serial_no);
            $path = 'qrcodes/apab/' . $this->serial_no . '.svg';
            Storage::disk('public')->put($path, $qrCode);

            $this->qr_svg_path = $path;
            $this->saveQuietly();
        } catch (\Exception $e) {
            \Log::error('Failed to generate QR for APAB ' . $this->id . ': ' . $e->getMessage());
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
