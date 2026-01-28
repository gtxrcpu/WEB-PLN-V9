<?php

namespace App\Http\Controllers\Admin;

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

        return view('admin.reference-videos.index', compact('videos'));
    }

    public function create()
    {
        $units = Unit::orderBy('code')->get();
        return view('admin.reference-videos.create', compact('units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit_id' => 'nullable|exists:units,id',
            'video' => 'required|file|mimes:mp4,mov,avi,wmv|max:102400', // Max 100MB
            'thumbnail' => 'nullable|image|max:2048', // Max 2MB
        ]);

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
            'unit_id' => $validated['unit_id'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.reference-videos.index')
            ->with('success', 'Video berhasil diupload!');
    }

    public function edit(ReferenceVideo $referenceVideo)
    {
        $units = Unit::orderBy('code')->get();
        return view('admin.reference-videos.edit', compact('referenceVideo', 'units'));
    }

    public function update(Request $request, ReferenceVideo $referenceVideo)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit_id' => 'nullable|exists:units,id',
            'video' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:102400',
            'thumbnail' => 'nullable|image|max:2048',
        ]);

        // Update video if new one uploaded
        if ($request->hasFile('video')) {
            // Delete old video
            if ($referenceVideo->video_path && Storage::disk('public')->exists($referenceVideo->video_path)) {
                Storage::disk('public')->delete($referenceVideo->video_path);
            }
            $validated['video_path'] = $request->file('video')->store('videos/references', 'public');
        }

        // Update thumbnail if new one uploaded
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($referenceVideo->thumbnail_path && Storage::disk('public')->exists($referenceVideo->thumbnail_path)) {
                Storage::disk('public')->delete($referenceVideo->thumbnail_path);
            }
            $validated['thumbnail_path'] = $request->file('thumbnail')->store('videos/thumbnails', 'public');
        }

        $referenceVideo->update($validated);

        return redirect()->route('admin.reference-videos.index')
            ->with('success', 'Video berhasil diupdate!');
    }

    public function destroy(ReferenceVideo $referenceVideo)
    {
        $referenceVideo->delete(); // File cleanup handled by model event

        return redirect()->route('admin.reference-videos.index')
            ->with('success', 'Video berhasil dihapus!');
    }
}
