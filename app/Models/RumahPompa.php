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

        return \App\Helpers\QrCodeHelper::generateVisualSvgDataUri($url);
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
        // Format from settings or default
        $format = \App\Models\AparSetting::get('rumah_pompa_kode_format', 'RUMAHPOMPA-{UNIT}-{NNN}');

        // Determine unit from auth user if not provided
        if ($unitId === null && auth()->check() && auth()->user()->unit_id) {
            $unitId = auth()->user()->unit_id;
        }

        // Get unit name for format
        $unitName = $unitId ? (\App\Models\Unit::find($unitId)?->name ?? 'INDUK') : 'INDUK';

        // Counter key based on unit (per-unit independent counter)
        $counterKey = $unitId ? "rumah_pompa_kode_counter_{$unitId}" : "rumah_pompa_kode_counter_induk";
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
        $fallback = str_replace(['{UNIT}', '{NNN}'], [$unitName, date('His')], 'RUMAHPOMPA-{UNIT}-{NNN}');

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
            'module' => 'rumah-pompa',
            'id' => $this->id
        ]);

        try {
            $qrCode = \App\Helpers\QrCodeHelper::generateVisualSvg($url);

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
