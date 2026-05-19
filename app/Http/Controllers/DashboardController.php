<?php

namespace App\Http\Controllers;

use App\Services\EquipmentStatsService;

class DashboardController extends Controller
{
    public function user()
    {
        $user = auth()->user();
        $unitId = $user ? $user->unit_id : null;

        // If superadmin/leader without unit, check session
        if (!$unitId && $user && !$user->unit_id && session('viewing_unit_id')) {
            $unitId = session('viewing_unit_id');
        }

        $statsService = new EquipmentStatsService();

        // Equipment stats from centralized service
        $stats = $statsService->getStatusBreakdown($unitId);

        $aparData = $stats['apar'];
        $apatData = $stats['apat'];
        $apabData = $stats['apab'];
        $fireAlarmData = $stats['fireAlarm'];
        $boxHydrantData = $stats['boxHydrant'];
        $rumahPompaData = $stats['rumahPompa'];

        // Total keseluruhan
        $totalItems = $aparData['total'] + $apatData['total'] + $apabData['total'] +
                      $fireAlarmData['total'] + $boxHydrantData['total'] + $rumahPompaData['total'];

        // Data tren inspeksi 6 bulan terakhir
        $trendData = $statsService->getInspectionTrend($unitId, 6);

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
}
