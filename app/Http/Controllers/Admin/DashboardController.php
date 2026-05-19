<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FiltersByUnit;
use App\Models\User;
use App\Services\EquipmentStatsService;

class DashboardController extends Controller
{
    use FiltersByUnit;

    public function index()
    {
        $unitId = $this->getAuthUserUnitId();
        $statsService = new EquipmentStatsService();

        // Total users by role
        $totalUsers = User::count();
        $totalSuperadmin = User::role('superadmin')->count();
        $totalLeader = User::role('leader')->count();
        $totalInspector = User::role('inspector')->count();
        $totalPetugas = User::role('petugas')->count();

        // Equipment stats from centralized service (case-insensitive, consistent)
        $stats = $statsService->getStatusBreakdown($unitId);

        $aparData = $stats['apar'];
        $apatData = $stats['apat'];
        $apabData = $stats['apab'];
        $fireAlarmData = $stats['fireAlarm'];
        $boxHydrantData = $stats['boxHydrant'];
        $rumahPompaData = $stats['rumahPompa'];
        $cctvData = $stats['cctv'];

        // Totals per module
        $totalApar = $aparData['total'];
        $totalApat = $apatData['total'];
        $totalFireAlarm = $fireAlarmData['total'];
        $totalBoxHydrant = $boxHydrantData['total'];
        $totalRumahPompa = $rumahPompaData['total'];
        $totalApab = $apabData['total'];
        $totalP3k = $stats['p3k']['total'];
        $totalCctv = $cctvData['total'];
        $totalEquipment = $statsService->getTotalEquipment($unitId);

        // KPI totals
        $baikRusak = $statsService->getTotalBaikRusak($unitId);
        $totalBaik = $baikRusak['total_baik'];
        $totalRusak = $baikRusak['total_rusak'];

        // Pending approvals
        $pendingApprovals = $statsService->getPendingApprovals($unitId);

        // Inspection trend (12 months for admin)
        $trendData = $statsService->getInspectionTrend($unitId, 12);
        $monthLabels = $trendData['labels'];

        // Flatten trend datasets into single monthly totals for the admin chart
        $monthlyInspections = [];
        $monthCount = count($monthLabels);
        for ($i = 0; $i < $monthCount; $i++) {
            $total = 0;
            foreach ($trendData['datasets'] as $dataset) {
                $total += $dataset[$i] ?? 0;
            }
            $monthlyInspections[] = $total;
        }

        // Recent users
        $recentUsers = User::latest()->take(5)->get();

        // Equipment by type
        $equipmentByType = [
            ['name' => 'APAR', 'count' => $totalApar],
            ['name' => 'APAT', 'count' => $totalApat],
            ['name' => 'Fire Alarm', 'count' => $totalFireAlarm],
            ['name' => 'Box Hydrant', 'count' => $totalBoxHydrant],
            ['name' => 'Rumah Pompa', 'count' => $totalRumahPompa],
            ['name' => 'APAB', 'count' => $totalApab],
            ['name' => 'P3K', 'count' => $totalP3k],
            ['name' => 'CCTV', 'count' => $totalCctv],
        ];

        $totalItems = $totalEquipment;

        return view('dashboard.admin', compact(
            'totalUsers',
            'totalSuperadmin',
            'totalLeader',
            'totalInspector',
            'totalPetugas',
            'totalEquipment',
            'totalApar',
            'totalApat',
            'totalFireAlarm',
            'totalBoxHydrant',
            'totalRumahPompa',
            'totalApab',
            'totalP3k',
            'totalCctv',
            'aparData',
            'apatData',
            'apabData',
            'fireAlarmData',
            'boxHydrantData',
            'rumahPompaData',
            'cctvData',
            'recentUsers',
            'equipmentByType',
            'totalItems',
            'totalBaik',
            'totalRusak',
            'pendingApprovals',
            'monthlyInspections',
            'monthLabels'
        ));
    }
}
