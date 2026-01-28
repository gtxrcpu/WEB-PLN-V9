<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ReferenceVideo extends Model
{
    protected $fillable = [
        'title',
        'description',
        'video_path',
        'thumbnail_path',
        'unit_id',
        'created_by',
    ];

    /**
     * Relationship to Unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relationship to User (uploader)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to filter videos accessible to a specific unit
     */
    public function scopeForUnit($query, $unitId)
    {
        return $query->where(function ($q) use ($unitId) {
            $q->whereNull('unit_id') // Videos for all units
                ->orWhere('unit_id', $unitId); // Videos for specific unit
        });
    }

    /**
     * Get video URL
     */
    public function getVideoUrlAttribute()
    {
        return $this->video_path ? Storage::url($this->video_path) : null;
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path ? Storage::url($this->thumbnail_path) : null;
    }

    /**
     * Delete video files when model is deleted
     */
    protected static function booted()
    {
        static::deleting(function ($video) {
            if ($video->video_path && Storage::disk('public')->exists($video->video_path)) {
                Storage::disk('public')->delete($video->video_path);
            }
            if ($video->thumbnail_path && Storage::disk('public')->exists($video->thumbnail_path)) {
                Storage::disk('public')->delete($video->thumbnail_path);
            }
        });
    }
}
