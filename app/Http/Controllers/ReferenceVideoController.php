<?php

namespace App\Http\Controllers;

use App\Models\ReferenceVideo;
use Illuminate\Http\Request;

class ReferenceVideoController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get videos accessible to user's unit
        $videos = ReferenceVideo::with(['unit', 'creator'])
            ->forUnit($user->unit_id)
            ->latest()
            ->paginate(12);

        return view('reference-videos.index', compact('videos'));
    }

    public function show(ReferenceVideo $referenceVideo)
    {
        $user = auth()->user();

        // Superadmin dan Inspector bisa akses semua video
        if ($user->hasAnyRole(['superadmin', 'inspector'])) {
            return view('reference-videos.show', compact('referenceVideo'));
        }

        // Video tanpa unit_id = video global, semua bisa akses
        if (!$referenceVideo->unit_id) {
            return view('reference-videos.show', compact('referenceVideo'));
        }

        // Video dengan unit_id = hanya unit yang sama yang bisa akses
        if ($referenceVideo->unit_id != $user->unit_id) {
            abort(403, 'Anda tidak memiliki akses ke video ini.');
        }

        return view('reference-videos.show', compact('referenceVideo'));
    }
}
