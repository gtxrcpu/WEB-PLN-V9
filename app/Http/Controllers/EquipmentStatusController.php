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
     */
    public function show(Request $request, $module, $id)
    {
        // 1. Validasi & Authorization dilakukan melalui signed flag
        if (!$request->hasValidSignature()) {
            abort(403, 'Akses ditolak: QR Code tidak valid atau telah kadaluarsa. Pastikan Anda melakukan scan QR Code yang resmi.');
        }

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
}
