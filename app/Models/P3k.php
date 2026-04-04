<?php

namespace App\Models;

use App\Models\Traits\HasUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class P3k extends Model
{
    use HasFactory, HasUnit;

    protected $guarded = [];

    protected static function booted(): void
    {
        // Auto-generate serial_no P3K.xxx
        static::creating(function (P3k $p3k) {
            if (empty($p3k->serial_no)) {
                $lastSerial = static::where('serial_no', 'like', 'P3K.%')
                    ->orderBy('id', 'desc')
                    ->value('serial_no');

                $nextNumber = 1;

                if ($lastSerial && preg_match('/P3K\.(\d+)/', $lastSerial, $m)) {
                    $nextNumber = (int) $m[1] + 1;
                }

                $p3k->serial_no = 'P3K.' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            }

            // Barcode default = serial_no
            if (empty($p3k->barcode)) {
                $p3k->barcode = 'P3K ' . $p3k->serial_no;
            }
        });
    }

    /**
     * Accessor: $p3k->qr_url → Generate QR as SVG data URI (no file, no HTTP request!)
     * This generates QR on-the-fly as base64 encoded SVG (works without imagick)
     */
    public function getQrUrlAttribute(): string
    {
        $url = \Illuminate\Support\Facades\URL::signedRoute('equipment.status', [
            'module' => 'p3k',
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
            'module' => 'p3k',
            'id' => $this->id
        ]);

        try {
            $qrCode = \App\Helpers\QrCodeHelper::generateVisualSvg($url);

            $path = 'qrcodes/p3k/' . $this->serial_no . '.svg';
            Storage::disk('public')->put($path, $qrCode);

            $this->qr_svg_path = $path;
            $this->saveQuietly();
        } catch (\Exception $e) {
            \Log::error('Failed to generate QR for P3K ' . $this->id . ': ' . $e->getMessage());
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
     * Relasi ke kartu inspeksi P3K (legacy)
     */
    public function kartuP3ks()
    {
        return $this->hasMany(KartuP3k::class, 'p3k_id');
    }

    /**
     * Relasi ke kartu pemeriksaan P3K
     */
    public function kartuPemeriksaan()
    {
        return $this->hasMany(KartuP3kPemeriksaan::class, 'p3k_id');
    }

    /**
     * Relasi ke kartu pemakaian P3K
     */
    public function kartuPemakaian()
    {
        return $this->hasMany(KartuP3kPemakaian::class, 'p3k_id');
    }

    /**
     * Relasi ke kartu stock P3K
     */
    public function kartuStock()
    {
        return $this->hasMany(KartuP3kStock::class, 'p3k_id');
    }

    /**
     * Relasi ke user
     */
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
