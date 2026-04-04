<?php

namespace App\Models;

use App\Models\Traits\HasUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Apat extends Model
{
    use HasFactory, HasUnit;

    /**
     * Biar fleksibel: semua field boleh diisi via create/update()
     * (validasi tetap di Controller).
     */
    protected $guarded = [];

    protected $casts = [
        'last_inspection_at' => 'datetime',
        'next_inspection_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Auto-generate serial_no & barcode APAT A2.xxx
        static::creating(function (Apat $apat) {
            // Kalau belum ada serial_no, generate A2.001, A2.002, dst
            if (empty($apat->serial_no)) {
                $lastSerial = static::where('serial_no', 'like', 'A2.%')
                    ->orderBy('id', 'desc')
                    ->value('serial_no');

                $nextNumber = 1;

                if ($lastSerial && preg_match('/A2\.(\d+)/', $lastSerial, $m)) {
                    $nextNumber = (int) $m[1] + 1;
                }

                $apat->serial_no = 'A2.' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            }

            // Barcode default = serial_no kalau belum diisi
            if (empty($apat->barcode)) {
                $apat->barcode = $apat->serial_no;
            }
        });
    }

    /**
     * Generate next serial number for APAT using custom format from settings
     * @param int|null $unitId Unit ID (null = Induk)
     * @param bool $incrementCounter Whether to increment counter
     */
    public static function generateNextSerial($unitId = null, $incrementCounter = true): string
    {
        // NEW FORMAT: Include unit name for uniqueness
        $format = \App\Models\AparSetting::get('apat_kode_format', 'APAT-{UNIT}-{NNN}');

        // Determine unit from auth user if not provided
        if ($unitId === null && auth()->check() && auth()->user()->unit_id) {
            $unitId = auth()->user()->unit_id;
        }

        // Counter key based on unit (per-unit independent counter)
        $counterKey = $unitId ? "apat_kode_counter_{$unitId}" : "apat_kode_counter_induk";
        $counter = (int) \App\Models\AparSetting::get($counterKey, 1);

        // Get unit name for format (more readable than code)
        $unitName = $unitId ? (\App\Models\Unit::find($unitId)?->name ?? 'INDUK') : 'INDUK';

        // Check existing data to ensure counter is in sync FOR THIS UNIT ONLY
        $query = self::query();
        if ($unitId) {
            $query->where('unit_id', $unitId);
        } else {
            $query->whereNull('unit_id');
        }

        // Get the highest serial number from existing data FOR THIS UNIT
        $lastApat = $query->orderByRaw('CAST(SUBSTRING_INDEX(serial_no, "-", -1) AS UNSIGNED) DESC')->first();

        if ($lastApat && $lastApat->serial_no) {
            // Extract number from last serial (e.g., "APAT-INDUK-005" -> 5)
            $parts = explode('-', $lastApat->serial_no);
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
                '{NNNN}',
                '{NNN}',
            ], [
                $unitName,
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
            \App\Models\AparSetting::set($counterKey, $counter + $attempts + 1);
        }

        return $serial;
    }

    /**
     * Accessor: $apat->qr_url → Generate QR as SVG data URI (no file, no HTTP request!)
     * This generates QR on-the-fly as base64 encoded SVG (works without imagick)
     */
    public function getQrUrlAttribute(): string
    {
        $url = \Illuminate\Support\Facades\URL::signedRoute('equipment.status', [
            'module' => 'apat', 
            'id' => $this->id
        ]);

        return \App\Helpers\QrCodeHelper::generateVisualSvgDataUri($url);
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
            'module' => 'apat',
            'id' => $this->id
        ]);

        try {
            $qrCode = \App\Helpers\QrCodeHelper::generateVisualSvg($url);
            $path = 'qrcodes/apat/' . $this->serial_no . '.svg';
            Storage::disk('public')->put($path, $qrCode);

            $this->qr_svg_path = $path;
            $this->saveQuietly();
        } catch (\Exception $e) {
            \Log::error('Failed to generate QR for APAT ' . $this->id . ': ' . $e->getMessage());
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
     * Relasi ke kartu inspeksi APAT
     */
    public function kartuApats()
    {
        return $this->hasMany(\App\Models\KartuApat::class, 'apat_id');
    }

    /**
     * Relasi ke user yang menginput
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the floor plan that this equipment belongs to
     */
    public function floorPlan()
    {
        return $this->belongsTo(FloorPlan::class);
    }
}
