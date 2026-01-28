<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\ReferenceVideo;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReferenceVideoController extends Controller
{
    public function index()
    {
        $videos = ReferenceVideo::with(['unit', 'creator'])
            ->latest()
            ->paginate(12);

        return view('leader.reference-videos.index', compact('videos'));
    }

    public function create()
    {
        // Leader can only upload for their own unit or All Units (null)
        $leaderUnit = auth()->user()->unit;

        return view('leader.reference-videos.create', compact('leaderUnit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit_scope' => 'required|in:own_unit,all_units', // own_unit or all_units
            'video' => 'required|file|mimes:mp4,mov,avi,wmv,webm|max:102400', // Max 100MB
            'thumbnail' => 'nullable|image|max:2048', // Max 2MB
        ]);

        // Determine unit_id based on scope
        $unitId = null;
        if ($validated['unit_scope'] === 'own_unit') {
            // Leader's own unit
            $unitId = auth()->user()->unit_id;

            // Security check: ensure leader has a unit
            if (!$unitId) {
                return back()->withErrors(['unit_scope' => 'Anda tidak memiliki unit yang terdaftar.']);
            }
        }
        // If 'all_units', unitId stays null (for all units)

        // Store video
        $videoPath = $request->file('video')->store('videos/references', 'public');

        // Store thumbnail if provided
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('videos/thumbnails', 'public');
        }

        ReferenceVideo::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'video_path' => $videoPath,
            'thumbnail_path' => $thumbnailPath,
            'unit_id' => $unitId,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('leader.reference-videos.index')
            ->with('success', 'Video berhasil diupload!');
    }
}
