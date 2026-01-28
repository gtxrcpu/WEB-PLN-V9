<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AparSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description', 'unit_id'];

    // Helper method untuk get setting by key
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    // Helper method untuk set setting
    public static function set($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    // Helper method untuk get setting by unit
    public static function getByUnit($key, $unitId, $default = null)
    {
        $setting = self::where('key', $key)
            ->where('unit_id', $unitId)
            ->first();
        return $setting ? $setting->value : $default;
    }

    // Helper method untuk set setting by unit
    public static function setByUnit($key, $value, $unitId)
    {
        return self::updateOrCreate(
            ['key' => $key, 'unit_id' => $unitId],
            ['value' => $value]
        );
    }

    // Relationship to Unit
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
