<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\Apar;
use App\Models\Apat;
use App\Models\Apab;
use App\Models\FireAlarm;
use App\Models\BoxHydrant;
use App\Models\RumahPompa;
use App\Models\P3k;
use Illuminate\Http\Request;

class InspectorDashboardController extends Controller
{
    public function index()
    {
        $aparData = [
            'total' => Apar::forAuthUser()->count(),
            'baik' => Apar::forAuthUser()->where('status', 'baik')->count(),
            'isi_ulang' => Apar::forAuthUser()->where('status', 'isi ulang')->count(),
            'rusak' => Apar::forAuthUser()->where('status', 'rusak')->count(),
        ];

        $apatData = [
            'total' => Apat::forAuthUser()->count(),
            'baik' => Apat::forAuthUser()->where('status', 'baik')->count(),
            'rusak' => Apat::forAuthUser()->where('status', 'rusak')->count(),
        ];

        $apabData = [
            'total' => Apab::forAuthUser()->count(),
            'baik' => Apab::forAuthUser()->where('status', 'baik')->count(),
            'tidak_baik' => Apab::forAuthUser()->where('status', '!=', 'baik')->count(),
        ];

        $fireAlarmData = [
            'total' => FireAlarm::forAuthUser()->count(),
            'baik' => FireAlarm::forAuthUser()->where('status', 'baik')->count(),
            'rusak' => FireAlarm::forAuthUser()->where('status', 'rusak')->count(),
        ];

        $boxHydrantData = [
            'total' => BoxHydrant::forAuthUser()->count(),
            'baik' => BoxHydrant::forAuthUser()->where('status', 'baik')->count(),
            'rusak' => BoxHydrant::forAuthUser()->where('status', 'rusak')->count(),
        ];

        $rumahPompaData = [
            'total' => RumahPompa::forAuthUser()->count(),
            'baik' => RumahPompa::forAuthUser()->where('status', 'baik')->count(),
            'rusak' => RumahPompa::forAuthUser()->where('status', 'rusak')->count(),
        ];

        $totalItems = $aparData['total'] + $apatData['total'] + $apabData['total'] + 
                     $fireAlarmData['total'] + $boxHydrantData['total'] + $rumahPompaData['total'];
        
        $totalEquipment = $totalItems; // Alias for compatibility

        // Trend data (last 6 months)
        $trendData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            'datasets' => [
                'APAR' => [0, 0, 0, 0, 0, \App\Models\KartuApar::count()],
                'APAT' => [0, 0, 0, 0, 0, \App\Models\KartuApat::count()],
                'APAB' => [0, 0, 0, 0, 0, \App\Models\KartuApab::count()],
                'Fire Alarm' => [0, 0, 0, 0, 0, \App\Models\KartuFireAlarm::count()],
                'Box Hydrant' => [0, 0, 0, 0, 0, \App\Models\KartuBoxHydrant::count()],
                'Rumah Pompa' => [0, 0, 0, 0, 0, \App\Models\KartuRumahPompa::count()],
            ]
        ];

        return view('inspector.dashboard', compact(
            'aparData', 'apatData', 'apabData', 'fireAlarmData', 
            'boxHydrantData', 'rumahPompaData', 'totalItems', 'totalEquipment', 'trendData'
        ));
    }

    // APAR
    public function apar()
    {
        $apars = Apar::forAuthUser()->orderBy('serial_no')->get();
        return view('inspector.apar.index', compact('apars'));
    }

    public function aparRiwayat(Apar $apar)
    {
        $kartuKendali = $apar->kartuApars()
            ->with(['user', 'leaderApprover', 'leaderSignature', 'approver', 'signature'])
            ->orderBy('tgl_periksa', 'desc')
            ->get();
        return view('inspector.apar.riwayat', compact('apar', 'kartuKendali'));
    }

    // APAT
    public function apat()
    {
        $apats = Apat::forAuthUser()->orderBy('serial_no')->get();
        return view('inspector.apat.index', compact('apats'));
    }

    public function apatRiwayat(Apat $apat)
    {
        $riwayatInspeksi = $apat->kartuApats()->orderBy('tgl_periksa', 'desc')->get();
        return view('inspector.apat.riwayat', compact('apat', 'riwayatInspeksi'));
    }

    // P3K
    public function p3k()
    {
        $p3ks = P3k::forAuthUser()->orderBy('serial_no')->get();
        return view('inspector.p3k.index', compact('p3ks'));
    }

    public function p3kRiwayat(P3k $p3k)
    {
        $riwayatInspeksi = $p3k->kartuP3ks()->orderBy('tgl_periksa', 'desc')->get();
        return view('inspector.p3k.riwayat', compact('p3k', 'riwayatInspeksi'));
    }

    // APAB
    public function apab()
    {
        $apabs = Apab::forAuthUser()->orderBy('serial_no')->get();
        return view('inspector.apab.index', compact('apabs'));
    }

    public function apabRiwayat(Apab $apab)
    {
        $riwayatInspeksi = $apab->kartuInspeksi()->orderBy('tgl_periksa', 'desc')->get();
        return view('inspector.apab.riwayat', compact('apab', 'riwayatInspeksi'));
    }

    // Fire Alarm
    public function fireAlarm()
    {
        $fireAlarms = FireAlarm::forAuthUser()->orderBy('serial_no')->get();
        return view('inspector.fire-alarm.index', compact('fireAlarms'));
    }

    public function fireAlarmRiwayat(FireAlarm $fireAlarm)
    {
        $riwayatInspeksi = $fireAlarm->kartuInspeksi()->orderBy('tgl_periksa', 'desc')->get();
        return view('inspector.fire-alarm.riwayat', compact('fireAlarm', 'riwayatInspeksi'));
    }

    // Box Hydrant
    public function boxHydrant()
    {
        $boxHydrants = BoxHydrant::forAuthUser()->orderBy('serial_no')->get();
        return view('inspector.box-hydrant.index', compact('boxHydrants'));
    }

    public function boxHydrantRiwayat(BoxHydrant $boxHydrant)
    {
        $riwayatInspeksi = $boxHydrant->kartuInspeksi()->orderBy('tgl_periksa', 'desc')->get();
        return view('inspector.box-hydrant.riwayat', compact('boxHydrant', 'riwayatInspeksi'));
    }

    // Rumah Pompa
    public function rumahPompa()
    {
        $rumahPompas = RumahPompa::forAuthUser()->orderBy('serial_no')->get();
        return view('inspector.rumah-pompa.index', compact('rumahPompas'));
    }

    public function rumahPompaRiwayat(RumahPompa $rumahPompa)
    {
        $riwayatInspeksi = $rumahPompa->kartuInspeksi()->orderBy('tgl_periksa', 'desc')->get();
        return view('inspector.rumah-pompa.riwayat', compact('rumahPompa', 'riwayatInspeksi'));
    }
}
