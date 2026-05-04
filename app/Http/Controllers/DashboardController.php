<?php

namespace App\Http\Controllers;

use App\Models\Apar;
use App\Models\Apat;
use App\Models\Apab;
use App\Models\FireAlarm;
use App\Models\BoxHydrant;
use App\Models\RumahPompa;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function user()
    {
        // Ambil data status dari setiap modul
        $aparData = [
            'baik' => Apar::forAuthUser()->where('status', 'baik')->count(),
            'isi_ulang' => Apar::forAuthUser()->where('status', 'isi ulang')->count(),
            'rusak' => Apar::forAuthUser()->where('status', 'rusak')->count(),
            'total' => Apar::forAuthUser()->count()
        ];

        $apatData = [
            'baik' => Apat::forAuthUser()->where('status', 'baik')->count(),
            'rusak' => Apat::forAuthUser()->where('status', 'rusak')->count(),
            'total' => Apat::forAuthUser()->count()
        ];

        $apabData = [
            'baik' => Apab::forAuthUser()->where('status', 'baik')->count(),
            'tidak_baik' => Apab::forAuthUser()->where('status', '!=', 'baik')->count(),
            'total' => Apab::forAuthUser()->count()
        ];

        $fireAlarmData = [
            'baik' => FireAlarm::forAuthUser()->where('status', 'baik')->count(),
            'rusak' => FireAlarm::forAuthUser()->where('status', 'rusak')->count(),
            'total' => FireAlarm::forAuthUser()->count()
        ];

        $boxHydrantData = [
            'baik' => BoxHydrant::forAuthUser()->where('status', 'baik')->count(),
            'rusak' => BoxHydrant::forAuthUser()->where('status', 'rusak')->count(),
            'total' => BoxHydrant::forAuthUser()->count()
        ];

        $rumahPompaData = [
            'baik' => RumahPompa::forAuthUser()->where('status', 'baik')->count(),
            'rusak' => RumahPompa::forAuthUser()->where('status', 'rusak')->count(),
            'total' => RumahPompa::forAuthUser()->count()
        ];

        // Total keseluruhan
        $totalItems = $aparData['total'] + $apatData['total'] + $apabData['total'] + 
                      $fireAlarmData['total'] + $boxHydrantData['total'] + $rumahPompaData['total'];

        // Data tren inspeksi 6 bulan terakhir (real-time dari database)
        $trendData = $this->getInspectionTrend();

        return view('dashboard.user', compact(
            'aparData',
            'apatData', 
            'apabData',
            'fireAlarmData',
            'boxHydrantData',
            'rumahPompaData',
            'totalItems',
            'trendData'
        ));
    }

    /**
     * Ambil data tren inspeksi 6 bulan terakhir dari semua modul
     */
    private function getInspectionTrend()
    {
        $user = auth()->user();
        $unitId = $user ? $user->unit_id : null;
        
        $months = [];
        $data = [
            'APAR' => [],
            'APAT' => [],
            'APAB' => [],
            'Fire Alarm' => [],
            'Box Hydrant' => [],
            'Rumah Pompa' => []
        ];

        // Generate 6 bulan terakhir
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            $year = $date->year;
            $month = $date->month;

            // Hitung inspeksi per modul per bulan
            $modules = [
                'APAR' => ['kartu_apars', 'apars', 'apar_id'],
                'APAT' => ['kartu_apats', 'apats', 'apat_id'],
                'APAB' => ['kartu_apabs', 'apabs', 'apab_id'],
                'Fire Alarm' => ['kartu_fire_alarms', 'fire_alarms', 'fire_alarm_id'],
                'Box Hydrant' => ['kartu_box_hydrants', 'box_hydrants', 'box_hydrant_id'],
                'Rumah Pompa' => ['kartu_rumah_pompas', 'rumah_pompas', 'rumah_pompa_id'],
            ];

            foreach ($modules as $label => $config) {
                try {
                    $query = \DB::table($config[0]);
                    
                    if ($unitId) {
                        $query->join($config[1], $config[0].'.'.$config[2], '=', $config[1].'.id')
                            ->where($config[1].'.unit_id', $unitId);
                    }
                    
                    $data[$label][] = $query->whereYear($config[0].'.tgl_periksa', $year)
                        ->whereMonth($config[0].'.tgl_periksa', $month)
                        ->count();
                } catch (\Exception $e) {
                    $data[$label][] = 0;
                }
            }
        }

        return [
            'labels' => $months,
            'datasets' => $data
        ];
    }
}
