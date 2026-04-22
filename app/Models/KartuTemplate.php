<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuTemplate extends Model
{
    protected $fillable = [
        'module',
        'unit_id',
        'title',
        'subtitle',
        'company_name',
        'company_address',
        'unit_address',
        'company_phone',
        'company_fax',
        'company_email',
        'header_fields',
        'inspection_fields',
        'footer_fields',
        'table_header',
        'is_active'
    ];

    protected $casts = [
        'header_fields' => 'array',
        'inspection_fields' => 'array',
        'footer_fields' => 'array',
        'unit_address' => 'array', // Ubah menjadi array untuk menyimpan alamat per unit
        'is_active' => 'boolean',
    ];

    public static function getTemplate($module, $unitId = null)
    {
        // Ambil template untuk module (hanya ada 1 template per module sekarang)
        $template = self::where('module', $module)
            ->where('is_active', true)
            ->whereNull('unit_id') // Hanya ambil template global
            ->first();
        
        // Jika tidak ditemukan, coba cari tanpa filter is_active dan unit_id
        if (!$template) {
            $template = self::where('module', $module)
                ->whereNull('unit_id')
                ->first();
        }
        
        // Jika masih tidak ditemukan, coba cari tanpa filter unit_id
        if (!$template) {
            $template = self::where('module', $module)
                ->where('is_active', true)
                ->first();
        }
        
        // Resolve alamat berdasarkan unit
        if ($template) {
            if ($unitId) {
                $template->resolved_address = $template->getAddressForUnit($unitId);
            } else {
                $template->resolved_address = $template->company_address;
            }
        }
        
        return $template;
    }
    
    protected static function boot()
    {
        parent::boot();
        
        // Clear cache saat template di-update atau delete
        static::updated(function ($template) {
            \Cache::forget('kartu_template_' . $template->module);
            if ($template->unit_id) {
                \Cache::forget('kartu_template_' . $template->module . '_unit_' . $template->unit_id);
            }
        });
        
        static::deleted(function ($template) {
            \Cache::forget('kartu_template_' . $template->module);
            if ($template->unit_id) {
                \Cache::forget('kartu_template_' . $template->module . '_unit_' . $template->unit_id);
            }
        });
    }

    // Relasi ke Unit
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // Relasi ke alamat per unit
    public function unitAddresses()
    {
        return $this->hasMany(KartuTemplateUnitAddress::class, 'kartu_template_id');
    }

    /**
     * Get alamat untuk unit tertentu
     */
    public function getAddressForUnit($unitId)
    {
        if (!$unitId) {
            return $this->company_address;
        }

        // Pastikan unit_address adalah array
        if (!is_array($this->unit_address)) {
            return $this->company_address;
        }

        // Try dengan integer key
        if (isset($this->unit_address[$unitId])) {
            return $this->unit_address[$unitId];
        }

        // Try dengan string key
        $stringKey = (string) $unitId;
        if (isset($this->unit_address[$stringKey])) {
            return $this->unit_address[$stringKey];
        }

        // Try dengan integer key (jika input adalah string)
        if (is_numeric($unitId)) {
            $intKey = (int) $unitId;
            if (isset($this->unit_address[$intKey])) {
                return $this->unit_address[$intKey];
            }
        }

        // Fallback ke company_address
        return $this->company_address;
    }

    /**
     * Get alamat yang tepat berdasarkan prioritas:
     * 1. unit_address jika ada
     * 2. company_address sebagai fallback
     */
    public function getAddress()
    {
        return $this->unit_address ?: $this->company_address;
    }

    /**
     * Get template dengan alamat yang sudah resolved
     */
    public static function getTemplateWithAddress($module, $unitId = null)
    {
        $template = self::getTemplate($module, $unitId);
        
        if ($template) {
            // Jika template memiliki unit_address, gunakan itu
            // Jika tidak, gunakan company_address
            $template->resolved_address = $template->getAddress();
        }
        
        return $template;
    }

    public static function getModules()
    {
        return [
            'apar' => 'APAR - Alat Pemadam Api Ringan',
            'apat' => 'APAT - Alat Pemadam Api Tradisional',
            'apab' => 'APAB - Alat Pemadam Api Berat',
            'fire-alarm' => 'Fire Alarm - Panel & Titik Alarm',
            'box-hydrant' => 'Box Hydrant - Box, Hose, Nozzle',
            'rumah-pompa' => 'Rumah Pompa - Hydrant Rumah Pompa',
            'p3k' => 'P3K - Kotak & Isi P3K (Legacy)',
            'p3k-pemeriksaan' => 'P3K Pemeriksaan - Checklist Kondisi Kotak P3K',
            'p3k-pemakaian' => 'P3K Pemakaian - Catatan Penggunaan Obat/Alat',
            'p3k-stock' => 'P3K Stock - Kartu Kendali Stock P3K',
        ];
    }
}
