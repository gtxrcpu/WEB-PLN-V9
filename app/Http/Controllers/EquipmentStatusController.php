<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Apar;
use App\Models\Apat;
use App\Models\Apab;
use App\Models\FireAlarm;
use App\Models\BoxHydrant;
use App\Models\RumahPompa;
use App\Models\P3k;

class EquipmentStatusController extends Controller
{
    /**
     * Tampilkan status UI (responsive, HTML, meta SEO)
     * Endpoint ini menerima scan eksternal menggunakan kamera HP.
     * Public access - no signature validation required.
     */
    public function show(Request $request, $module, $id)
    {
        $equipment = null;
        $typeName = strtoupper(str_replace('-', ' ', $module));

        // 2. Filter / Fetch data
        switch ($module) {
            case 'apar':
                $equipment = Apar::findOrFail($id);
                break;
            case 'apat':
                $equipment = Apat::findOrFail($id);
                break;
            case 'apab':
                $equipment = Apab::findOrFail($id);
                break;
            case 'fire-alarm':
                $equipment = FireAlarm::findOrFail($id);
                break;
            case 'box-hydrant':
                $equipment = BoxHydrant::findOrFail($id);
                break;
            case 'rumah-pompa':
                $equipment = RumahPompa::findOrFail($id);
                break;
            case 'p3k':
                $equipment = P3k::findOrFail($id);
                break;
            default:
                abort(404, 'Tipe equipment tidak dikenali sistem.');
        }

        // 3. Render responsive mobile-first UI
        return view('equipment-status', [
            'equipment' => $equipment,
            'module' => $module,
            'typeName' => $typeName,
            'fallbackUrl' => route('guest.dashboard')
        ]);
    }

    /**
     * Tampilkan status P3K dengan jenis spesifik (pemeriksaan/pemakaian/stock)
     * Public access - no signature validation required.
     */
    public function showP3k(Request $request, $jenis, $id)
    {
        // Map jenis abbreviation to full name
        $jenisMap = [
            'pks' => 'pemeriksaan',
            'pmk' => 'pemakaian',
            'stk' => 'stock',
            'pemeriksaan' => 'pemeriksaan',
            'pemakaian' => 'pemakaian',
            'stock' => 'stock',
        ];

        $jenisName = $jenisMap[$jenis] ?? 'pemeriksaan';
        
        // Find P3K by ID and validate jenis matches
        $equipment = P3k::findOrFail($id);
        
        // Optional: validate that jenis matches (or just show it regardless)
        // if ($equipment->jenis !== $jenisName) {
        //     abort(404, 'P3K jenis tidak sesuai');
        // }
        
        $typeName = 'P3K ' . strtoupper($jenisName);

        // 3. Render responsive mobile-first UI
        return view('equipment-status', [
            'equipment' => $equipment,
            'module' => 'p3k',
            'typeName' => $typeName,
            'jenis' => $jenisName,
            'fallbackUrl' => route('guest.dashboard')
        ]);
    }
}
