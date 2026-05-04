<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apar;
use App\Models\Apat;
use App\Models\Apab;
use App\Models\FireAlarm;
use App\Models\BoxHydrant;
use App\Models\RumahPompa;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Search APAR
        $apars = Apar::forAuthUser()->where(function($q) use ($query) {
            $q->where('serial_no', 'LIKE', "%{$query}%")
                ->orWhere('barcode', 'LIKE', "%{$query}%")
                ->orWhere('location_code', 'LIKE', "%{$query}%");
        })
            ->limit(5)
            ->get();
        
        foreach ($apars as $item) {
            $results[] = [
                'id' => 'apar-' . $item->id,
                'module' => 'APAR',
                'serial_no' => $item->serial_no,
                'barcode' => $item->barcode,
                'location' => $item->location_code,
                'status' => $item->status ?? 'unknown',
                'url' => route('apar.index') . '#item-' . $item->id,
                'color' => 'bg-gradient-to-br from-blue-500 to-teal-500',
                'badgeColor' => 'bg-blue-100 text-blue-700',
            ];
        }

        // Search APAT
        $apats = Apat::forAuthUser()->where(function($q) use ($query) {
            $q->where('serial_no', 'LIKE', "%{$query}%")
                ->orWhere('barcode', 'LIKE', "%{$query}%")
                ->orWhere('lokasi', 'LIKE', "%{$query}%");
        })
            ->limit(5)
            ->get();
        
        foreach ($apats as $item) {
            $results[] = [
                'id' => 'apat-' . $item->id,
                'module' => 'APAT',
                'serial_no' => $item->serial_no,
                'barcode' => $item->barcode,
                'location' => $item->location_code,
                'status' => $item->status ?? 'unknown',
                'url' => route('apat.index') . '#item-' . $item->id,
                'color' => 'bg-gradient-to-br from-cyan-500 to-sky-500',
                'badgeColor' => 'bg-cyan-100 text-cyan-700',
            ];
        }

        // Search APAB
        $apabs = Apab::forAuthUser()->where(function($q) use ($query) {
            $q->where('serial_no', 'LIKE', "%{$query}%")
                ->orWhere('barcode', 'LIKE', "%{$query}%")
                ->orWhere('location_code', 'LIKE', "%{$query}%");
        })
            ->limit(5)
            ->get();
        
        foreach ($apabs as $item) {
            $results[] = [
                'id' => 'apab-' . $item->id,
                'module' => 'APAB',
                'serial_no' => $item->serial_no,
                'barcode' => $item->barcode,
                'location' => $item->location_code,
                'status' => $item->status ?? 'unknown',
                'url' => route('apab.index') . '#item-' . $item->id,
                'color' => 'bg-gradient-to-br from-red-500 to-orange-500',
                'badgeColor' => 'bg-red-100 text-red-700',
            ];
        }

        // Search Fire Alarm
        $fireAlarms = FireAlarm::forAuthUser()->where(function($q) use ($query) {
            $q->where('serial_no', 'LIKE', "%{$query}%")
                ->orWhere('barcode', 'LIKE', "%{$query}%")
                ->orWhere('location_code', 'LIKE', "%{$query}%");
        })
            ->limit(5)
            ->get();
        
        foreach ($fireAlarms as $item) {
            $results[] = [
                'id' => 'fire-alarm-' . $item->id,
                'module' => 'Fire Alarm',
                'serial_no' => $item->serial_no,
                'barcode' => $item->barcode,
                'location' => $item->location_code,
                'status' => $item->status ?? 'unknown',
                'url' => route('fire-alarm.index') . '#item-' . $item->id,
                'color' => 'bg-gradient-to-br from-red-500 to-pink-500',
                'badgeColor' => 'bg-pink-100 text-pink-700',
            ];
        }

        // Search Box Hydrant
        $boxHydrants = BoxHydrant::forAuthUser()->where(function($q) use ($query) {
            $q->where('serial_no', 'LIKE', "%{$query}%")
                ->orWhere('barcode', 'LIKE', "%{$query}%")
                ->orWhere('location_code', 'LIKE', "%{$query}%");
        })
            ->limit(5)
            ->get();
        
        foreach ($boxHydrants as $item) {
            $results[] = [
                'id' => 'box-hydrant-' . $item->id,
                'module' => 'Box Hydrant',
                'serial_no' => $item->serial_no,
                'barcode' => $item->barcode,
                'location' => $item->location_code,
                'status' => $item->status ?? 'unknown',
                'url' => route('box-hydrant.index') . '#item-' . $item->id,
                'color' => 'bg-gradient-to-br from-blue-700 to-cyan-500',
                'badgeColor' => 'bg-blue-100 text-blue-700',
            ];
        }

        // Search Rumah Pompa
        $rumahPompas = RumahPompa::forAuthUser()->where(function($q) use ($query) {
            $q->where('serial_no', 'LIKE', "%{$query}%")
                ->orWhere('barcode', 'LIKE', "%{$query}%")
                ->orWhere('location_code', 'LIKE', "%{$query}%");
        })
            ->limit(5)
            ->get();
        
        foreach ($rumahPompas as $item) {
            $results[] = [
                'id' => 'rumah-pompa-' . $item->id,
                'module' => 'Rumah Pompa',
                'serial_no' => $item->serial_no,
                'barcode' => $item->barcode,
                'location' => $item->location_code,
                'status' => $item->status ?? 'unknown',
                'url' => route('rumah-pompa.index') . '#item-' . $item->id,
                'color' => 'bg-gradient-to-br from-purple-600 to-indigo-600',
                'badgeColor' => 'bg-purple-100 text-purple-700',
            ];
        }

        // Limit total results
        $results = array_slice($results, 0, 20);

        return response()->json(['results' => $results]);
    }
}
