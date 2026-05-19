<?php

namespace App\Services;

use App\Models\Apar;
use App\Models\Apat;
use App\Models\Apab;
use App\Models\FireAlarm;
use App\Models\BoxHydrant;
use App\Models\RumahPompa;
use App\Models\P3k;
use App\Models\Cctv;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for equipment statistics.
 *
 * All dashboards (Admin, Leader, Petugas, Guest) should use this service
 * to ensure consistent counting logic across the application.
 *
 * Key design decisions:
 * - Status comparisons are ALWAYS case-insensitive (LOWER/whereRaw)
 * - APAB "rusak" = any status that is NOT 'baik' (covers 'tidak baik', 'tidak_baik', etc.)
 * - P3K status uses 'lengkap'/'kurang'/'rusak' (different from other modules)
 */
class EquipmentStatsService
{
    /**
     * Get equipment status breakdown for a specific unit (or all units).
     *
     * @param  int|null $unitId  Filter by unit. Null = all units.
     * @return array Keyed by module name with 'total', 'baik', 'rusak' (+ module-specific keys)
     */
    public function getStatusBreakdown(?int $unitId = null): array
    {
        return [
            'apar' => $this->getModuleStats(Apar::class, $unitId, ['baik', 'isi ulang', 'rusak']),
            'apat' => $this->getModuleStats(Apat::class, $unitId, ['baik', 'rusak']),
            'apab' => $this->getApabStats($unitId),
            'fireAlarm' => $this->getModuleStats(FireAlarm::class, $unitId, ['baik', 'rusak']),
            'boxHydrant' => $this->getModuleStats(BoxHydrant::class, $unitId, ['baik', 'rusak']),
            'rumahPompa' => $this->getModuleStats(RumahPompa::class, $unitId, ['baik', 'rusak']),
            'p3k' => $this->getModuleStats(P3k::class, $unitId, ['baik', 'rusak']),
            'cctv' => $this->getCctvStats($unitId),
        ];
    }

    /**
     * Get total equipment count across all modules for a unit.
     */
    public function getTotalEquipment(?int $unitId = null): int
    {
        $models = [Apar::class, Apat::class, Apab::class, FireAlarm::class, BoxHydrant::class, RumahPompa::class, P3k::class, Cctv::class];
        $total = 0;

        foreach ($models as $model) {
            $query = $model::query();
            if ($unitId) {
                $query->where('unit_id', $unitId);
            }
            $total += $query->count();
        }

        return $total;
    }

    /**
     * Get total baik/rusak counts across all modules.
     */
    public function getTotalBaikRusak(?int $unitId = null): array
    {
        $stats = $this->getStatusBreakdown($unitId);

        $totalBaik = 0;
        $totalRusak = 0;

        foreach ($stats as $module => $data) {
            $totalBaik += $data['baik'] ?? 0;
            $totalRusak += ($data['rusak'] ?? 0) + ($data['isi_ulang'] ?? 0) + ($data['tidak_baik'] ?? 0);
        }

        return [
            'total_baik' => $totalBaik,
            'total_rusak' => $totalRusak,
        ];
    }

    /**
     * Get inspection trend data for the last N months.
     *
     * @param  int|null $unitId
     * @param  int      $months  Number of months to look back
     * @return array ['labels' => [...], 'datasets' => ['APAR' => [...], ...]]
     */
    public function getInspectionTrend(?int $unitId = null, int $months = 6): array
    {
        $labels = [];
        $data = [
            'APAR' => [],
            'APAT' => [],
            'APAB' => [],
            'Fire Alarm' => [],
            'Box Hydrant' => [],
            'Rumah Pompa' => [],
            'P3K' => [],
        ];

        $modules = [
            'APAR' => ['kartu_apars', 'apars', 'apar_id'],
            'APAT' => ['kartu_apats', 'apats', 'apat_id'],
            'APAB' => ['kartu_apabs', 'apabs', 'apab_id'],
            'Fire Alarm' => ['kartu_fire_alarms', 'fire_alarms', 'fire_alarm_id'],
            'Box Hydrant' => ['kartu_box_hydrants', 'box_hydrants', 'box_hydrant_id'],
            'Rumah Pompa' => ['kartu_rumah_pompas', 'rumah_pompas', 'rumah_pompa_id'],
            'P3K' => ['kartu_p3ks', 'p3ks', 'p3k_id'],
        ];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $year = $date->year;
            $month = $date->month;

            foreach ($modules as $label => $config) {
                try {
                    $query = DB::table($config[0]);

                    if ($unitId) {
                        $query->join($config[1], $config[0] . '.' . $config[2], '=', $config[1] . '.id')
                            ->where($config[1] . '.unit_id', $unitId);
                    }

                    $data[$label][] = $query
                        ->whereYear($config[0] . '.tgl_periksa', $year)
                        ->whereMonth($config[0] . '.tgl_periksa', $month)
                        ->count();
                } catch (\Exception $e) {
                    $data[$label][] = 0;
                }
            }
        }

        return [
            'labels' => $labels,
            'datasets' => $data,
        ];
    }

    /**
     * Get pending approval count across all modules.
     */
    public function getPendingApprovals(?int $unitId = null): int
    {
        $models = [
            ['class' => \App\Models\KartuApar::class, 'relation' => 'apar'],
            ['class' => \App\Models\KartuApat::class, 'relation' => 'apat'],
            ['class' => \App\Models\KartuApab::class, 'relation' => 'apab'],
            ['class' => \App\Models\KartuFireAlarm::class, 'relation' => 'fireAlarm'],
            ['class' => \App\Models\KartuBoxHydrant::class, 'relation' => 'boxHydrant'],
            ['class' => \App\Models\KartuRumahPompa::class, 'relation' => 'rumahPompa'],
        ];

        $total = 0;

        foreach ($models as $config) {
            $query = $config['class']::whereNull('approved_at');

            if ($unitId) {
                $query->whereHas($config['relation'], function ($q) use ($unitId) {
                    $q->where('unit_id', $unitId);
                });
            }

            $total += $query->count();
        }

        return $total;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generic module stats using case-insensitive status comparison.
     */
    private function getModuleStats(string $modelClass, ?int $unitId, array $statuses): array
    {
        $query = $modelClass::query();
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        // Single query with groupBy for efficiency
        $grouped = (clone $query)
            ->selectRaw('LOWER(status) as status_lower, COUNT(*) as count')
            ->groupBy('status_lower')
            ->pluck('count', 'status_lower');

        $result = [
            'total' => $grouped->sum(),
            'baik' => (int) $grouped->get('baik', 0),
        ];

        if (in_array('isi ulang', $statuses)) {
            $result['isi_ulang'] = (int) $grouped->get('isi ulang', 0);
        }

        if (in_array('rusak', $statuses)) {
            $result['rusak'] = (int) $grouped->get('rusak', 0);
        }

        return $result;
    }

    /**
     * APAB has special logic: "tidak baik" = everything that's not "baik".
     * This handles 'tidak baik', 'tidak_baik', 'rusak', etc. consistently.
     */
    private function getApabStats(?int $unitId): array
    {
        $query = Apab::query();
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        $total = (clone $query)->count();
        $baik = (clone $query)->whereRaw('LOWER(status) = ?', ['baik'])->count();

        return [
            'total' => $total,
            'baik' => $baik,
            'tidak_baik' => $total - $baik,
        ];
    }

    /**
     * CCTV uses 'Baik'/'Jelek' status values.
     */
    private function getCctvStats(?int $unitId): array
    {
        $query = Cctv::query();
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        $grouped = (clone $query)
            ->selectRaw('LOWER(status) as status_lower, COUNT(*) as count')
            ->groupBy('status_lower')
            ->pluck('count', 'status_lower');

        return [
            'total' => $grouped->sum(),
            'baik' => (int) $grouped->get('baik', 0),
            'rusak' => (int) $grouped->get('jelek', 0),
        ];
    }
}
