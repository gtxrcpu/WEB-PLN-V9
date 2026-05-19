<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\KartuApar;
use App\Models\Apar;
use App\Models\Apat;
use App\Models\Apab;
use App\Models\FireAlarm;
use App\Models\BoxHydrant;
use App\Models\RumahPompa;
use App\Services\EquipmentStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $unitId = $user->unit_id ?? null;
        $statsService = new EquipmentStatsService();

        // Stats from centralized service
        $stats = [
            'total_equipment' => $statsService->getTotalEquipment($unitId),
            'pending_approvals' => $statsService->getPendingApprovals($unitId),
            'approved_this_month' => KartuApar::query()
                ->when($unitId, fn($q) => $q->whereHas('apar', fn($q2) => $q2->where('unit_id', $unitId)))
                ->whereNotNull('approved_at')
                ->whereMonth('approved_at', now()->month)
                ->count(),
            'total_users' => $unitId
                ? \App\Models\User::where('unit_id', $unitId)->count()
                : \App\Models\User::count(),
        ];

        // Equipment breakdown from centralized service
        $breakdown = $statsService->getStatusBreakdown($unitId);

        $aparData = $breakdown['apar'];
        $apatData = $breakdown['apat'];
        $apabData = $breakdown['apab'];
        $fireAlarmData = $breakdown['fireAlarm'];
        $boxHydrantData = $breakdown['boxHydrant'];
        $rumahPompaData = $breakdown['rumahPompa'];

        $totalItems = $aparData['total'] + $apatData['total'] + $apabData['total'] +
            $fireAlarmData['total'] + $boxHydrantData['total'] + $rumahPompaData['total'];

        // Inspection trend from centralized service
        $trendData = $statsService->getInspectionTrend($unitId, 6);

        // Recent pending approvals
        $allPendingLists = collect();
        $models = [
            'apar' => [\App\Models\KartuApar::class, 'apar'],
            'apat' => [\App\Models\KartuApat::class, 'apat'],
            'apab' => [\App\Models\KartuApab::class, 'apab'],
            'fireAlarm' => [\App\Models\KartuFireAlarm::class, 'fireAlarm'],
            'boxHydrant' => [\App\Models\KartuBoxHydrant::class, 'boxHydrant'],
            'rumahPompa' => [\App\Models\KartuRumahPompa::class, 'rumahPompa'],
            'p3k' => [\App\Models\KartuP3k::class, 'p3k'],
        ];

        foreach ($models as $moduleKey => $config) {
            $modelClass = $config[0];
            $relation = $config[1];

            $query = $modelClass::with([$relation, 'user'])
                ->whereNull('approved_at')
                ->whereNull('rejected_at')
                ->whereNull('leader_approved_at')
                ->whereNull('leader_rejected_at');

            if ($unitId) {
                $query->whereHas($relation, function ($q) use ($unitId) {
                    $q->where('unit_id', $unitId);
                });
            } else {
                $query->whereHas($relation);
            }

            $pending = $query->latest()->take(10)->get()->map(function($item) use ($moduleKey, $relation) {
                $item->module_type = $moduleKey;
                $item->equipment_name = $item->{$relation}->name ?? $item->{$relation}->location_code ?? strtoupper($moduleKey);
                return $item;
            });
            $allPendingLists = $allPendingLists->merge($pending);
        }

        $pendingApprovals = $allPendingLists->sortByDesc('created_at')->take(10)->values();

        return view('leader.dashboard', compact(
            'stats',
            'pendingApprovals',
            'totalItems',
            'aparData',
            'apatData',
            'apabData',
            'fireAlarmData',
            'boxHydrantData',
            'rumahPompaData',
            'trendData'
        ));
    }
}

