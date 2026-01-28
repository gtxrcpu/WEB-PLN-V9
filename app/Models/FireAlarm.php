<?php

namespace App\Models;

use App\Models\Traits\HasUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class FireAlarm extends Model
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
        'zone',
        'status',
        'notes',
        'qr_svg_path',
    ];

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate QR Code SVG dan simpan ke storage
     */
    public function refreshQrSvg(): void
    {
        $qrContent = $this->barcode ?? $this->serial_no ?? 'FA-' . $this->id;

        $svg = QrCode::size(300)
            ->format('svg')
            ->generate($qrContent);

        $filename = 'fire-alarm-' . $this->id . '.svg';
        $path = 'qr/' . $filename;

        Storage::disk('public')->put($path, $svg);

        $this->qr_svg_path = 'storage/' . $path;
        $this->saveQuietly();
    }

    /**
     * Generate next serial number for Fire Alarm with unit-based format
     * Format: FIREALARM-{UNIT}-{NNN} (e.g., FIREALARM-UP2WIII-001, FIREALARM-INDUK-001)
     * 
     * @param int|null $unitId Unit ID (null = Induk)
     * @param bool $incrementCounter Whether to increment counter (false = preview only)
     * @return string Generated serial number
     */
    public static function generateNextSerial($unitId = null, bool $incrementCounter = true): string
    {
        // Format from settings or default
        $format = \App\Models\AparSetting::get('fire_alarm_kode_format', 'FIREALARM-{UNIT}-{NNN}');

        // Determine unit from auth user if not provided
        if ($unitId === null && auth()->check() && auth()->user()->unit_id) {
            $unitId = auth()->user()->unit_id;
        }

        // Get unit name for format
        $unitName = $unitId ? (\App\Models\Unit::find($unitId)?->name ?? 'INDUK') : 'INDUK';

        // Counter key based on unit (per-unit independent counter)
        $counterKey = $unitId ? "fire_alarm_kode_counter_{$unitId}" : "fire_alarm_kode_counter_induk";
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
        $fallback = str_replace(['{UNIT}', '{NNN}'], [$unitName, date('His')], 'FIREALARM-{UNIT}-{NNN}');

        if ($incrementCounter) {
            \App\Models\AparSetting::set($counterKey, $currentCounter + $maxAttempts + 1);
        }

        return $fallback;
    }

    /**
     * Accessor: $fireAlarm->qr_url → Generate QR as SVG data URI (no file, no HTTP request!)
     * This generates QR on-the-fly as base64 encoded SVG (works without imagick)
     */
    public function getQrUrlAttribute(): string
    {
        // Generate QR content with equipment info (not URL)
        $qrContent = json_encode([
            'type' => 'Fire Alarm',
            'code' => $this->barcode ?? $this->serial_no,
            'serial' => $this->serial_no,
            'location' => $this->location_code ?? '-',
            'status' => $this->status ?? '-',
            'zone' => $this->zone ?? '-',
        ], JSON_UNESCAPED_UNICODE);

        $svg = QrCode::size(300)
            ->format('svg')
            ->margin(1)
            ->errorCorrection('H')
            ->generate($qrContent);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Generate and save QR code as SVG file
     */
    public function generateQrSvg($force = false): void
    {
        if (!$force && $this->qr_svg_path && Storage::disk('public')->exists($this->qr_svg_path)) {
            return;
        }

        $url = route('fire-alarm.riwayat', $this->id);

        try {
            $qrCode = QrCode::format('svg')
                ->size(300)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($url);

            $path = 'qrcodes/fire-alarm/' . $this->serial_no . '.svg';
            Storage::disk('public')->put($path, $qrCode);

            $this->qr_svg_path = $path;
            $this->saveQuietly();
        } catch (\Exception $e) {
            \Log::error('Failed to generate QR for Fire Alarm: ' . $e->getMessage());
        }
    }

    public function kartuInspeksi()
    {
        return $this->hasMany(KartuFireAlarm::class)->latest('tgl_periksa');
    }

    public function kartuFireAlarms()
    {
        return $this->hasMany(KartuFireAlarm::class, 'fire_alarm_id');
    }

    /**
     * Get the floor plan that this equipment belongs to
     */
    public function floorPlan()
    {
        return $this->belongsTo(FloorPlan::class);
    }
}
