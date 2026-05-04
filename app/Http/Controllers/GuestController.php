<?php

namespace App\Http\Controllers;

use App\Models\Apar;
use App\Models\Apat;
use App\Models\Apab;
use App\Models\FireAlarm;
use App\Models\BoxHydrant;
use App\Models\RumahPompa;
use App\Models\P3k;
use App\Models\KartuApar;
use App\Models\KartuApat;
use App\Models\KartuApab;
use App\Models\KartuFireAlarm;
use App\Models\KartuBoxHydrant;
use App\Models\KartuRumahPompa;
use App\Models\KartuP3k;
use App\Models\Unit;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index()
    {
        // Get real-time statistics without caching
        // Optimize: Use single query with groupBy for each model
        $aparStats = Apar::forAuthUser()->selectRaw('LOWER(status) as status_lower, COUNT(*) as count')
            ->groupBy('status_lower')
            ->pluck('count', 'status_lower');

        $apatStats = Apat::forAuthUser()->selectRaw('LOWER(status) as status_lower, COUNT(*) as count')
            ->groupBy('status_lower')
            ->pluck('count', 'status_lower');

        $apabStats = Apab::forAuthUser()->selectRaw('LOWER(status) as status_lower, COUNT(*) as count')
            ->groupBy('status_lower')
            ->pluck('count', 'status_lower');

        $fireAlarmStats = FireAlarm::forAuthUser()->selectRaw('LOWER(status) as status_lower, COUNT(*) as count')
            ->groupBy('status_lower')
            ->pluck('count', 'status_lower');

        $boxHydrantStats = BoxHydrant::forAuthUser()->selectRaw('LOWER(status) as status_lower, COUNT(*) as count')
            ->groupBy('status_lower')
            ->pluck('count', 'status_lower');

        $rumahPompaStats = RumahPompa::forAuthUser()->selectRaw('LOWER(status) as status_lower, COUNT(*) as count')
            ->groupBy('status_lower')
            ->pluck('count', 'status_lower');

        $p3kStats = P3k::forAuthUser()->selectRaw('LOWER(status) as status_lower, COUNT(*) as count')
            ->groupBy('status_lower')
            ->pluck('count', 'status_lower');

        $stats = [
            'apar' => [
                'total' => $aparStats->sum(),
                'baik' => $aparStats->get('baik', 0),
                'isi_ulang' => $aparStats->get('isi ulang', 0),
                'rusak' => $aparStats->get('rusak', 0),
            ],
            'apat' => [
                'total' => $apatStats->sum(),
                'baik' => $apatStats->get('baik', 0),
                'rusak' => $apatStats->get('rusak', 0),
            ],
            'apab' => [
                'total' => $apabStats->sum(),
                'baik' => $apabStats->get('baik', 0),
                'tidak_baik' => $apabStats->sum() - $apabStats->get('baik', 0),
            ],
            'fireAlarm' => [
                'total' => $fireAlarmStats->sum(),
                'baik' => $fireAlarmStats->get('baik', 0),
                'rusak' => $fireAlarmStats->get('rusak', 0),
            ],
            'boxHydrant' => [
                'total' => $boxHydrantStats->sum(),
                'baik' => $boxHydrantStats->get('baik', 0),
                'rusak' => $boxHydrantStats->get('rusak', 0),
            ],
            'rumahPompa' => [
                'total' => $rumahPompaStats->sum(),
                'baik' => $rumahPompaStats->get('baik', 0),
                'rusak' => $rumahPompaStats->get('rusak', 0),
            ],
            'p3k' => [
                'total' => $p3kStats->sum(),
                'baik' => $p3kStats->get('baik', 0),
                'rusak' => $p3kStats->get('rusak', 0),
            ],
        ];

        $aparData = $stats['apar'];
        $apatData = $stats['apat'];
        $apabData = $stats['apab'];
        $fireAlarmData = $stats['fireAlarm'];
        $boxHydrantData = $stats['boxHydrant'];
        $rumahPompaData = $stats['rumahPompa'];
        $p3kData = $stats['p3k'];

        $totalItems = $aparData['total'] + $apatData['total'] + $apabData['total'] +
            $fireAlarmData['total'] + $boxHydrantData['total'] + $rumahPompaData['total'] + $p3kData['total'];

        $totalEquipment = $totalItems;

        // Generate real-time trend data for the last 6 months
        $months = [];
        $monthLabels = [];
        $now = now();

        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $months[] = [
                'start' => $date->copy()->startOfMonth(),
                'end' => $date->copy()->endOfMonth(),
            ];
            // Indonesian month names
            $monthLabels[] = $date->locale('id')->format('M');
        }

        $trendData = [
            'labels' => $monthLabels,
            'datasets' => [
                'APAR' => [],
                'APAT' => [],
                'APAB' => [],
                'Fire Alarm' => [],
                'Box Hydrant' => [],
                'Rumah Pompa' => [],
                'P3K' => [],
            ]
        ];

        foreach ($months as $month) {
            $trendData['datasets']['APAR'][] = \App\Models\KartuApar::whereBetween('tgl_periksa', [$month['start'], $month['end']])->count();
            $trendData['datasets']['APAT'][] = \App\Models\KartuApat::whereBetween('tgl_periksa', [$month['start'], $month['end']])->count();
            $trendData['datasets']['APAB'][] = \App\Models\KartuApab::whereBetween('tgl_periksa', [$month['start'], $month['end']])->count();
            $trendData['datasets']['Fire Alarm'][] = \App\Models\KartuFireAlarm::whereBetween('tgl_periksa', [$month['start'], $month['end']])->count();
            $trendData['datasets']['Box Hydrant'][] = \App\Models\KartuBoxHydrant::whereBetween('tgl_periksa', [$month['start'], $month['end']])->count();
            $trendData['datasets']['Rumah Pompa'][] = \App\Models\KartuRumahPompa::whereBetween('tgl_periksa', [$month['start'], $month['end']])->count();
            $trendData['datasets']['P3K'][] = \App\Models\KartuP3k::whereBetween('tgl_periksa', [$month['start'], $month['end']])->count();
        }

        return view('guest.dashboard', compact(
            'aparData',
            'apatData',
            'apabData',
            'fireAlarmData',
            'boxHydrantData',
            'rumahPompaData',
            'p3kData',
            'totalItems',
            'totalEquipment',
            'trendData'
        ));
    }

    // APAR
    public function apar(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->unit_id) {
            $unitId = $user->unit_id;
            $units = Unit::where('id', $unitId)->get();
        } else {
            $unitId = $request->get('unit_id');
            // Get all units for dropdown
            $units = Unit::where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        // Build query with unit filter
        $query = Apar::forAuthUser()->select('id', 'serial_no', 'barcode', 'location_code', 'type', 'capacity', 'status', 'unit_id')
            ->with(['unit:id,name', 'kartuApars:id,apar_id,tgl_periksa']);
        
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        
        $apars = $query->orderBy('serial_no')->paginate(20);
        
        // Get selected unit info
        $selectedUnit = $unitId ? Unit::find($unitId) : null;
        
        return view('guest.apar.index', compact('apars', 'units', 'selectedUnit'));
    }

    public function aparRiwayat(Apar $apar)
    {
        // Optimize: Load only needed relationships and columns
        $apar->load(['unit:id,name']);

        // Get inspection history with optimized query
        $riwayatInspeksi = $apar->kartuApars()
            ->select('id', 'apar_id', 'tgl_periksa', 'petugas', 'kesimpulan')
            ->orderBy('tgl_periksa', 'desc')
            ->get()
            ->map(function ($kartu) {
                // Remove sensitive fields
                $kartu->makeHidden(['ttd_petugas', 'ttd_penyelia', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason']);
                return $kartu;
            });

        return view('guest.apar.riwayat', compact('apar', 'riwayatInspeksi'));
    }

    // APAT
    public function apat(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->unit_id) {
            $unitId = $user->unit_id;
            $units = Unit::where('id', $unitId)->get();
        } else {
            $unitId = $request->get('unit_id');
            // Get all units for dropdown
            $units = Unit::where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        // Build query with unit filter
        $query = Apat::forAuthUser()->with(['unit', 'kartuApats']);
        
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        
        $apats = $query->orderBy('serial_no')->paginate(20);
        
        // Get selected unit info
        $selectedUnit = $unitId ? Unit::find($unitId) : null;
        
        return view('guest.apat.index', compact('apats', 'units', 'selectedUnit'));
    }

    public function apatRiwayat(Apat $apat)
    {
        // Load relationships for the equipment
        $apat->load(['unit', 'kartuApats']);

        // Get inspection history ordered by date
        // Filter sensitive data - exclude signatures and approval details
        $riwayatInspeksi = $apat->kartuApats()
            ->orderBy('tgl_periksa', 'desc')
            ->get()
            ->map(function ($kartu) {
                // Remove sensitive fields
                $kartu->makeHidden(['ttd_petugas', 'ttd_penyelia', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason']);
                return $kartu;
            });

        return view('guest.apat.riwayat', compact('apat', 'riwayatInspeksi'));
    }

    // P3K
    public function p3k(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->unit_id) {
            $unitId = $user->unit_id;
            $units = Unit::where('id', $unitId)->get();
        } else {
            $unitId = $request->get('unit_id');
            $units = Unit::where('is_active', true)->orderBy('name')->get();
        }
        
        $query = P3k::forAuthUser()->with(['unit', 'kartuPemeriksaan', 'kartuPemakaian', 'kartuStock']);
        
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        
        $p3ks = $query->orderBy('serial_no')->paginate(20);
        $selectedUnit = $unitId ? Unit::find($unitId) : null;
        
        return view('guest.p3k.index', compact('p3ks', 'units', 'selectedUnit'));
    }

    public function p3kRiwayat(P3k $p3k, Request $request)
    {
        $jenis = $request->get('jenis', 'pemeriksaan');
        
        $p3k->load(['unit']);

        $riwayatInspeksi = collect();
        
        switch ($jenis) {
            case 'pemeriksaan':
                $riwayatInspeksi = $p3k->kartuPemeriksaan()
                    ->orderBy('tgl_periksa', 'desc')
                    ->get()
                    ->map(function ($kartu) {
                        $kartu->makeHidden(['ttd_petugas', 'ttd_penyelia', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason']);
                        return $kartu;
                    });
                break;
                
            case 'pemakaian':
                $riwayatInspeksi = $p3k->kartuPemakaian()
                    ->orderBy('tgl_pemakaian', 'desc')
                    ->get()
                    ->map(function ($kartu) {
                        $kartu->makeHidden(['ttd_petugas', 'ttd_penyelia', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason']);
                        return $kartu;
                    });
                break;
                
            case 'stock':
                $riwayatInspeksi = $p3k->kartuStock()
                    ->orderBy('tgl_periksa', 'desc')
                    ->get()
                    ->map(function ($kartu) {
                        $kartu->makeHidden(['ttd_petugas', 'ttd_penyelia', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason']);
                        return $kartu;
                    });
                break;
        }

        return view('guest.p3k.riwayat', compact('p3k', 'riwayatInspeksi', 'jenis'));
    }

    /**
     * Pilih jenis kartu P3K (pemeriksaan, pemakaian, stock)
     * DEPRECATED - Not used anymore, P3K now shows direct list like APAR
     */
    public function p3kPilihJenis()
    {
        // Redirect to main P3K page
        return redirect()->route('guest.p3k');
    }

    /**
     * Pilih lokasi P3K berdasarkan jenis
     * DEPRECATED - Not used anymore
     */
    public function p3kPilihLokasi(Request $request)
    {
        // Redirect to main P3K page
        return redirect()->route('guest.p3k');
    }

    /**
     * Tampilkan P3K berdasarkan jenis dan lokasi
     * DEPRECATED - Not used anymore
     */
    public function p3kByLokasi(Request $request)
    {
        // Redirect to main P3K page
        return redirect()->route('guest.p3k');
    }

    // APAB
    public function apab(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->unit_id) {
            $unitId = $user->unit_id;
            $units = Unit::where('id', $unitId)->get();
        } else {
            $unitId = $request->get('unit_id');
            // Get all units for dropdown
            $units = Unit::where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        // Build query with unit filter
        $query = Apab::forAuthUser()->with(['unit', 'kartuApabs']);
        
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        
        $apabs = $query->orderBy('serial_no')->paginate(20);
        
        // Get selected unit info
        $selectedUnit = $unitId ? Unit::find($unitId) : null;
        
        return view('guest.apab.index', compact('apabs', 'units', 'selectedUnit'));
    }

    public function apabRiwayat(Apab $apab)
    {
        // Load relationships for the equipment
        $apab->load(['unit', 'kartuApabs']);

        // Get inspection history ordered by date
        // Filter sensitive data - exclude signatures and approval details
        $riwayatInspeksi = $apab->kartuApabs()
            ->orderBy('tgl_periksa', 'desc')
            ->get()
            ->map(function ($kartu) {
                // Remove sensitive fields
                $kartu->makeHidden(['ttd_petugas', 'ttd_penyelia', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason']);
                return $kartu;
            });

        return view('guest.apab.riwayat', compact('apab', 'riwayatInspeksi'));
    }

    // Fire Alarm
    public function fireAlarm(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->unit_id) {
            $unitId = $user->unit_id;
            $units = Unit::where('id', $unitId)->get();
        } else {
            $unitId = $request->get('unit_id');
            // Get all units for dropdown
            $units = Unit::where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        // Build query with unit filter
        $query = FireAlarm::forAuthUser()->with(['unit', 'kartuFireAlarms']);
        
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        
        $fireAlarms = $query->orderBy('serial_no')->paginate(20);
        
        // Get selected unit info
        $selectedUnit = $unitId ? Unit::find($unitId) : null;
        
        return view('guest.fire-alarm.index', compact('fireAlarms', 'units', 'selectedUnit'));
    }

    public function fireAlarmRiwayat(FireAlarm $fireAlarm)
    {
        // Load relationships for the equipment
        $fireAlarm->load(['unit', 'kartuFireAlarms']);

        // Get inspection history ordered by date
        // Filter sensitive data - exclude signatures and approval details
        $riwayatInspeksi = $fireAlarm->kartuFireAlarms()
            ->orderBy('tgl_periksa', 'desc')
            ->get()
            ->map(function ($kartu) {
                // Remove sensitive fields
                $kartu->makeHidden(['ttd_petugas', 'ttd_penyelia', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason']);
                return $kartu;
            });

        return view('guest.fire-alarm.riwayat', compact('fireAlarm', 'riwayatInspeksi'));
    }

    // Box Hydrant
    public function boxHydrant(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->unit_id) {
            $unitId = $user->unit_id;
            $units = Unit::where('id', $unitId)->get();
        } else {
            $unitId = $request->get('unit_id');
            // Get all units for dropdown
            $units = Unit::where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        // Build query with unit filter
        $query = BoxHydrant::forAuthUser()->with(['unit', 'kartuBoxHydrants']);
        
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        
        $boxHydrants = $query->orderBy('serial_no')->paginate(20);
        
        // Get selected unit info
        $selectedUnit = $unitId ? Unit::find($unitId) : null;
        
        return view('guest.box-hydrant.index', compact('boxHydrants', 'units', 'selectedUnit'));
    }

    public function boxHydrantRiwayat(BoxHydrant $boxHydrant)
    {
        // Load relationships for the equipment
        $boxHydrant->load(['unit', 'kartuBoxHydrants']);

        // Get inspection history ordered by date
        // Filter sensitive data - exclude signatures and approval details
        $riwayatInspeksi = $boxHydrant->kartuBoxHydrants()
            ->orderBy('tgl_periksa', 'desc')
            ->get()
            ->map(function ($kartu) {
                // Remove sensitive fields
                $kartu->makeHidden(['ttd_petugas', 'ttd_penyelia', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason']);
                return $kartu;
            });

        return view('guest.box-hydrant.riwayat', compact('boxHydrant', 'riwayatInspeksi'));
    }

    // Rumah Pompa
    public function rumahPompa(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->unit_id) {
            $unitId = $user->unit_id;
            $units = Unit::where('id', $unitId)->get();
        } else {
            $unitId = $request->get('unit_id');
            // Get all units for dropdown
            $units = Unit::where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        // Build query with unit filter
        $query = RumahPompa::forAuthUser()->with(['unit', 'kartuRumahPompas']);
        
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        
        $rumahPompas = $query->orderBy('serial_no')->paginate(20);
        
        // Get selected unit info
        $selectedUnit = $unitId ? Unit::find($unitId) : null;
        
        return view('guest.rumah-pompa.index', compact('rumahPompas', 'units', 'selectedUnit'));
    }

    public function rumahPompaRiwayat(RumahPompa $rumahPompa)
    {
        // Load relationships for the equipment
        $rumahPompa->load(['unit', 'kartuRumahPompas']);

        // Get inspection history ordered by date
        // Filter sensitive data - exclude signatures and approval details
        $riwayatInspeksi = $rumahPompa->kartuRumahPompas()
            ->orderBy('tgl_periksa', 'desc')
            ->get()
            ->map(function ($kartu) {
                // Remove sensitive fields
                $kartu->makeHidden(['ttd_petugas', 'ttd_penyelia', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason']);
                return $kartu;
            });

        return view('guest.rumah-pompa.riwayat', compact('rumahPompa', 'riwayatInspeksi'));
    }

    // Laporan Keseluruhan
    public function report()
    {
        // Real-time data - No cache for fresh data from database
        // Optimize: Select only needed columns
        $apars = Apar::forAuthUser()->select('id', 'serial_no', 'location_code', 'type', 'status', 'unit_id')
            ->with([
                'unit:id,name',
                'kartuApars' => function ($q) {
                    $q->select('id', 'apar_id', 'tgl_periksa')
                        ->latest('tgl_periksa')
                        ->limit(1);
                }
            ])
            ->orderBy('serial_no')
            ->get();

        $apats = Apat::forAuthUser()->select('id', 'serial_no', 'lokasi', 'jenis as type', 'status', 'unit_id')
            ->with([
                'unit:id,name',
                'kartuApats' => function ($q) {
                    $q->select('id', 'apat_id', 'tgl_periksa')
                        ->latest('tgl_periksa')
                        ->limit(1);
                }
            ])
            ->orderBy('serial_no')
            ->get();

        $p3ks = P3k::forAuthUser()->select('id', 'serial_no', 'location_code', 'type as jenis', 'status', 'unit_id')
            ->with([
                'unit:id,name',
                'kartuP3ks' => function ($q) {
                    $q->select('id', 'p3k_id', 'tgl_periksa')
                        ->latest('tgl_periksa')
                        ->limit(1);
                }
            ])
            ->orderBy('serial_no')
            ->get();

        $apabs = Apab::forAuthUser()->select('id', 'serial_no', 'location_code', 'isi_apab as type', 'status', 'unit_id')
            ->with([
                'unit:id,name',
                'kartuApabs' => function ($q) {
                    $q->select('id', 'apab_id', 'tgl_periksa')
                        ->latest('tgl_periksa')
                        ->limit(1);
                }
            ])
            ->orderBy('serial_no')
            ->get();

        $fireAlarms = FireAlarm::forAuthUser()->select('id', 'serial_no', 'location_code', 'type', 'status', 'unit_id')
            ->with([
                'unit:id,name',
                'kartuFireAlarms' => function ($q) {
                    $q->select('id', 'fire_alarm_id', 'tgl_periksa')
                        ->latest('tgl_periksa')
                        ->limit(1);
                }
            ])
            ->orderBy('serial_no')
            ->get();

        $boxHydrants = BoxHydrant::forAuthUser()->select('id', 'serial_no', 'location_code', 'type', 'status', 'unit_id')
            ->with([
                'unit:id,name',
                'kartuBoxHydrants' => function ($q) {
                    $q->select('id', 'box_hydrant_id', 'tgl_periksa')
                        ->latest('tgl_periksa')
                        ->limit(1);
                }
            ])
            ->orderBy('serial_no')
            ->get();

        $rumahPompas = RumahPompa::forAuthUser()->select('id', 'serial_no', 'location_code', 'type', 'status', 'unit_id')
            ->with([
                'unit:id,name',
                'kartuRumahPompas' => function ($q) {
                    $q->select('id', 'rumah_pompa_id', 'tgl_periksa')
                        ->latest('tgl_periksa')
                        ->limit(1);
                }
            ])
            ->orderBy('serial_no')
            ->get();

        // Calculate summary
        $summary = [
            'total_equipment' => $apars->count() + $apats->count() + $p3ks->count() +
                $apabs->count() + $fireAlarms->count() + $boxHydrants->count() +
                $rumahPompas->count(),
            'total_baik' => $apars->where('status', 'baik')->count() +
                $apats->where('status', 'baik')->count() +
                $p3ks->where('status', 'baik')->count() +
                $apabs->where('status', 'baik')->count() +
                $fireAlarms->where('status', 'baik')->count() +
                $boxHydrants->where('status', 'baik')->count() +
                $rumahPompas->where('status', 'baik')->count(),
            'total_rusak' => $apars->where('status', 'rusak')->count() +
                $apats->where('status', 'rusak')->count() +
                $p3ks->where('status', 'rusak')->count() +
                $apabs->where('status', '!=', 'baik')->count() +
                $fireAlarms->where('status', 'rusak')->count() +
                $boxHydrants->where('status', 'rusak')->count() +
                $rumahPompas->where('status', 'rusak')->count(),
        ];

        return view('guest.report', compact('apars', 'apats', 'p3ks', 'apabs', 'fireAlarms', 'boxHydrants', 'rumahPompas', 'summary'));
    }

    // API endpoint for real-time data fetch
    public function getReportData()
    {
        // Fetch real-time data from database
        $apars = Apar::forAuthUser()->select('id', 'serial_no', 'location_code', 'type', 'status', 'unit_id')
            ->with([
                'unit:id,name',
                'kartuApars' => function ($q) {
                    $q->select('id', 'apar_id', 'tgl_periksa')
                        ->latest('tgl_periksa')
                        ->limit(1);
                }
            ])
            ->orderBy('serial_no')
            ->get();

        $apats = Apat::forAuthUser()->select('id', 'serial_no', 'lokasi', 'jenis as type', 'status', 'unit_id')
            ->with([
                'unit:id,name',
                'kartuApats' => function ($q) {
                    $q->select('id', 'apat_id', 'tgl_periksa')
                        ->latest('tgl_periksa')
                        ->limit(1);
                }
            ])
            ->orderBy('serial_no')
            ->get();

        $p3ks = P3k::forAuthUser()->select('id', 'serial_no', 'location_code', 'type as jenis', 'status', 'unit_id')
            ->with([
                'unit:id,name',
                'kartuP3ks' => function ($q) {
                    $q->select('id', 'p3k_id', 'tgl_periksa')
                        ->latest('tgl_periksa')
                        ->limit(1);
                }
            ])
            ->orderBy('serial_no')
            ->get();

        $apabs = Apab::forAuthUser()->select('id', 'serial_no', 'location_code', 'isi_apab as type', 'status', 'unit_id')
            ->with([
                'unit:id,name',
                'kartuApabs' => function ($q) {
                    $q->select('id', 'apab_id', 'tgl_periksa')
                        ->latest('tgl_periksa')
                        ->limit(1);
                }
            ])
            ->orderBy('serial_no')
            ->get();

        $fireAlarms = FireAlarm::forAuthUser()->select('id', 'serial_no', 'location_code', 'type', 'status', 'unit_id')
            ->with([
                'unit:id,name',
                'kartuFireAlarms' => function ($q) {
                    $q->select('id', 'fire_alarm_id', 'tgl_periksa')
                        ->latest('tgl_periksa')
                        ->limit(1);
                }
            ])
            ->orderBy('serial_no')
            ->get();

        $boxHydrants = BoxHydrant::forAuthUser()->select('id', 'serial_no', 'location_code', 'type', 'status', 'unit_id')
            ->with([
                'unit:id,name',
                'kartuBoxHydrants' => function ($q) {
                    $q->select('id', 'box_hydrant_id', 'tgl_periksa')
                        ->latest('tgl_periksa')
                        ->limit(1);
                }
            ])
            ->orderBy('serial_no')
            ->get();

        $rumahPompas = RumahPompa::forAuthUser()->select('id', 'serial_no', 'location_code', 'type', 'status', 'unit_id')
            ->with([
                'unit:id,name',
                'kartuRumahPompas' => function ($q) {
                    $q->select('id', 'rumah_pompa_id', 'tgl_periksa')
                        ->latest('tgl_periksa')
                        ->limit(1);
                }
            ])
            ->orderBy('serial_no')
            ->get();

        // Calculate summary
        $summary = [
            'total_equipment' => $apars->count() + $apats->count() + $p3ks->count() +
                $apabs->count() + $fireAlarms->count() + $boxHydrants->count() +
                $rumahPompas->count(),
            'total_baik' => $apars->where('status', 'baik')->count() +
                $apats->where('status', 'baik')->count() +
                $p3ks->where('status', 'baik')->count() +
                $apabs->where('status', 'baik')->count() +
                $fireAlarms->where('status', 'baik')->count() +
                $boxHydrants->where('status', 'baik')->count() +
                $rumahPompas->where('status', 'baik')->count(),
            'total_rusak' => $apars->where('status', 'rusak')->count() +
                $apats->where('status', 'rusak')->count() +
                $p3ks->where('status', 'rusak')->count() +
                $apabs->where('status', '!=', 'baik')->count() +
                $fireAlarms->where('status', 'rusak')->count() +
                $boxHydrants->where('status', 'rusak')->count() +
                $rumahPompas->where('status', 'rusak')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => compact('apars', 'apats', 'p3ks', 'apabs', 'fireAlarms', 'boxHydrants', 'rumahPompas', 'summary'),
            'timestamp' => now()->toIso8601String()
        ]);
    }

    // API endpoint for real-time dashboard data
    public function getDashboardData()
    {
        // Get real-time statistics without caching
        $aparStats = Apar::forAuthUser()->selectRaw('LOWER(status) as status_lower, COUNT(*) as count')
            ->groupBy('status_lower')
            ->pluck('count', 'status_lower');

        $apatStats = Apat::forAuthUser()->selectRaw('LOWER(status) as status_lower, COUNT(*) as count')
            ->groupBy('status_lower')
            ->pluck('count', 'status_lower');

        $apabStats = Apab::forAuthUser()->selectRaw('LOWER(status) as status_lower, COUNT(*) as count')
            ->groupBy('status_lower')
            ->pluck('count', 'status_lower');

        $fireAlarmStats = FireAlarm::forAuthUser()->selectRaw('LOWER(status) as status_lower, COUNT(*) as count')
            ->groupBy('status_lower')
            ->pluck('count', 'status_lower');

        $boxHydrantStats = BoxHydrant::forAuthUser()->selectRaw('LOWER(status) as status_lower, COUNT(*) as count')
            ->groupBy('status_lower')
            ->pluck('count', 'status_lower');

        $rumahPompaStats = RumahPompa::forAuthUser()->selectRaw('LOWER(status) as status_lower, COUNT(*) as count')
            ->groupBy('status_lower')
            ->pluck('count', 'status_lower');

        $p3kStats = P3k::forAuthUser()->selectRaw('LOWER(status) as status_lower, COUNT(*) as count')
            ->groupBy('status_lower')
            ->pluck('count', 'status_lower');

        $stats = [
            'apar' => [
                'total' => $aparStats->sum(),
                'baik' => $aparStats->get('baik', 0),
                'isi_ulang' => $aparStats->get('isi ulang', 0),
                'rusak' => $aparStats->get('rusak', 0),
            ],
            'apat' => [
                'total' => $apatStats->sum(),
                'baik' => $apatStats->get('baik', 0),
                'rusak' => $apatStats->get('rusak', 0),
            ],
            'apab' => [
                'total' => $apabStats->sum(),
                'baik' => $apabStats->get('baik', 0),
                'tidak_baik' => $apabStats->sum() - $apabStats->get('baik', 0),
            ],
            'fireAlarm' => [
                'total' => $fireAlarmStats->sum(),
                'baik' => $fireAlarmStats->get('baik', 0),
                'rusak' => $fireAlarmStats->get('rusak', 0),
            ],
            'boxHydrant' => [
                'total' => $boxHydrantStats->sum(),
                'baik' => $boxHydrantStats->get('baik', 0),
                'rusak' => $boxHydrantStats->get('rusak', 0),
            ],
            'rumahPompa' => [
                'total' => $rumahPompaStats->sum(),
                'baik' => $rumahPompaStats->get('baik', 0),
                'rusak' => $rumahPompaStats->get('rusak', 0),
            ],
            'p3k' => [
                'total' => $p3kStats->sum(),
                'baik' => $p3kStats->get('baik', 0),
                'rusak' => $p3kStats->get('rusak', 0),
            ],
        ];

        // Generate trend data for the last 6 months
        $months = [];
        $monthLabels = [];
        $now = now();

        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $months[] = [
                'start' => $date->copy()->startOfMonth(),
                'end' => $date->copy()->endOfMonth(),
            ];
            $monthLabels[] = $date->locale('id')->format('M');
        }

        $trendData = [
            'labels' => $monthLabels,
            'datasets' => [
                'APAR' => [],
                'APAT' => [],
                'APAB' => [],
                'Fire Alarm' => [],
                'Box Hydrant' => [],
                'Rumah Pompa' => [],
                'P3K' => [],
            ]
        ];

        foreach ($months as $month) {
            $trendData['datasets']['APAR'][] = \App\Models\KartuApar::whereBetween('tgl_periksa', [$month['start'], $month['end']])->count();
            $trendData['datasets']['APAT'][] = \App\Models\KartuApat::whereBetween('tgl_periksa', [$month['start'], $month['end']])->count();
            $trendData['datasets']['APAB'][] = \App\Models\KartuApab::whereBetween('tgl_periksa', [$month['start'], $month['end']])->count();
            $trendData['datasets']['Fire Alarm'][] = \App\Models\KartuFireAlarm::whereBetween('tgl_periksa', [$month['start'], $month['end']])->count();
            $trendData['datasets']['Box Hydrant'][] = \App\Models\KartuBoxHydrant::whereBetween('tgl_periksa', [$month['start'], $month['end']])->count();
            $trendData['datasets']['Rumah Pompa'][] = \App\Models\KartuRumahPompa::whereBetween('tgl_periksa', [$month['start'], $month['end']])->count();
            $trendData['datasets']['P3K'][] = \App\Models\KartuP3k::whereBetween('tgl_periksa', [$month['start'], $month['end']])->count();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'trendData' => $trendData,
            ],
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Get equipment data by unit for real-time filtering
     */
    public function getEquipmentByUnit(Request $request)
    {
        $unitId = $request->get('unit_id');
        $module = $request->get('module'); // apar, apat, p3k, etc.
        
        if (!$module) {
            return response()->json(['success' => false, 'message' => 'Module is required']);
        }
        
        $data = [];
        
        switch ($module) {
            case 'apar':
                $query = Apar::with(['unit:id,name', 'kartuApars:id,apar_id,tgl_periksa']);
                if ($unitId) $query->where('unit_id', $unitId);
                $data = $query->orderBy('serial_no')->get();
                break;
                
            case 'apat':
                $query = Apat::with(['unit:id,name', 'kartuApats:id,apat_id,tgl_periksa']);
                if ($unitId) $query->where('unit_id', $unitId);
                $data = $query->orderBy('serial_no')->get();
                break;
                
            case 'p3k':
                $query = P3k::with(['unit:id,name', 'kartuP3ks:id,p3k_id,tgl_periksa']);
                if ($unitId) $query->where('unit_id', $unitId);
                $data = $query->orderBy('serial_no')->get();
                break;
                
            case 'apab':
                $query = Apab::with(['unit:id,name', 'kartuApabs:id,apab_id,tgl_periksa']);
                if ($unitId) $query->where('unit_id', $unitId);
                $data = $query->orderBy('serial_no')->get();
                break;
                
            case 'fire_alarm':
                $query = FireAlarm::with(['unit:id,name', 'kartuFireAlarms:id,fire_alarm_id,tgl_periksa']);
                if ($unitId) $query->where('unit_id', $unitId);
                $data = $query->orderBy('serial_no')->get();
                break;
                
            case 'box_hydrant':
                $query = BoxHydrant::with(['unit:id,name', 'kartuBoxHydrants:id,box_hydrant_id,tgl_periksa']);
                if ($unitId) $query->where('unit_id', $unitId);
                $data = $query->orderBy('serial_no')->get();
                break;
                
            case 'rumah_pompa':
                $query = RumahPompa::with(['unit:id,name', 'kartuRumahPompas:id,rumah_pompa_id,tgl_periksa']);
                if ($unitId) $query->where('unit_id', $unitId);
                $data = $query->orderBy('serial_no')->get();
                break;
                
            default:
                return response()->json(['success' => false, 'message' => 'Invalid module']);
        }
        
        // Calculate stats for the filtered data
        $stats = [
            'total' => $data->count(),
            'baik' => $data->where('status', 'baik')->count(),
            'rusak' => $data->where('status', 'rusak')->count(),
        ];
        
        // Add specific stats for APAR
        if ($module === 'apar') {
            $stats['isi_ulang'] = $data->where('status', 'isi ulang')->count();
        }
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'stats' => $stats,
            'unit_id' => $unitId,
            'module' => $module,
            'timestamp' => now()->toIso8601String()
        ]);
    }
}
