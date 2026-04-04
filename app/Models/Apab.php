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
        $url = \Illuminate\Support\Facades\URL::signedRoute('equipment.status', [
            'module' => 'apab', 
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
            'module' => 'apab',
            'id' => $this->id
        ]);

        try {
            $qrCode = \App\Helpers\QrCodeHelper::generateVisualSvg($url);
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
