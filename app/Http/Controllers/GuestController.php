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
use App\Services\EquipmentStatsService;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index()
    {
        $statsService = new EquipmentStatsService();

        // Determine unit context (guest may have auth user with unit)
        $user = auth()->user();
        $unitId = null;
        if ($user && $user->unit_id) {
            $unitId = $user->unit_id;
        } elseif ($user && !$user->unit_id && session('viewing_unit_id')) {
            $unitId = session('viewing_unit_id');
        }

        // Equipment stats from centralized service (case-insensitive, consistent)
        $stats = $statsService->getStatusBreakdown($unitId);

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

        // Inspection trend from centralized service
        $trendData = $statsService->getInspectionTrend($unitId, 6);

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
        
        // Calculate stats from ALL data (not just paginated) - case insensitive
        $statsQuery = Apar::forAuthUser();
        if ($unitId) {
            $statsQuery->where('unit_id', $unitId);
        }
        
        $stats = [
            'total' => $statsQuery->count(),
            'baik' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['BAIK'])->count(),
            'isi_ulang' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['ISI ULANG'])->count(),
            'rusak' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['RUSAK'])->count(),
        ];
        
        // Get selected unit info
        $selectedUnit = $unitId ? Unit::find($unitId) : null;
        
        return view('guest.apar.index', compact('apars', 'units', 'selectedUnit', 'stats'));
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
        
        // Calculate stats from ALL data (not just paginated)
        $statsQuery = Apat::forAuthUser();
        if ($unitId) {
            $statsQuery->where('unit_id', $unitId);
        }
        $stats = [
            'total' => $statsQuery->count(),
            'baik' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['BAIK'])->count(),
            'isi_ulang' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['ISI ULANG'])->count(),
            'rusak' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['RUSAK'])->count(),
        ];
        
        // Get selected unit info
        $selectedUnit = $unitId ? Unit::find($unitId) : null;
        
        return view('guest.apat.index', compact('apats', 'units', 'selectedUnit', 'stats'));
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
        
        // Calculate stats from ALL data
        $statsQuery = P3k::forAuthUser();
        if ($unitId) {
            $statsQuery->where('unit_id', $unitId);
        }
        $stats = [
            'total' => $statsQuery->count(),
            'baik' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['LENGKAP'])->count(),
            'isi_ulang' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['KURANG'])->count(),
            'rusak' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['RUSAK'])->count(),
        ];
        
        $selectedUnit = $unitId ? Unit::find($unitId) : null;
        
        return view('guest.p3k.index', compact('p3ks', 'units', 'selectedUnit', 'stats'));
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
        
        // Calculate stats from ALL data
        $statsQuery = Apab::forAuthUser();
        if ($unitId) {
            $statsQuery->where('unit_id', $unitId);
        }
        $stats = [
            'total' => $statsQuery->count(),
            'baik' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['BAIK'])->count(),
            'isi_ulang' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['ISI ULANG'])->count(),
            'rusak' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['RUSAK'])->count(),
        ];
        
        // Get selected unit info
        $selectedUnit = $unitId ? Unit::find($unitId) : null;
        
        return view('guest.apab.index', compact('apabs', 'units', 'selectedUnit', 'stats'));
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
        
        // Calculate stats from ALL data
        $statsQuery = FireAlarm::forAuthUser();
        if ($unitId) {
            $statsQuery->where('unit_id', $unitId);
        }
        $stats = [
            'total' => $statsQuery->count(),
            'baik' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['BAIK'])->count(),
            'isi_ulang' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['ISI ULANG'])->count(),
            'rusak' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['RUSAK'])->count(),
        ];
        
        // Get selected unit info
        $selectedUnit = $unitId ? Unit::find($unitId) : null;
        
        return view('guest.fire-alarm.index', compact('fireAlarms', 'units', 'selectedUnit', 'stats'));
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
        
        // Calculate stats from ALL data
        $statsQuery = BoxHydrant::forAuthUser();
        if ($unitId) {
            $statsQuery->where('unit_id', $unitId);
        }
        $stats = [
            'total' => $statsQuery->count(),
            'baik' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['BAIK'])->count(),
            'isi_ulang' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['ISI ULANG'])->count(),
            'rusak' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['RUSAK'])->count(),
        ];
        
        // Get selected unit info
        $selectedUnit = $unitId ? Unit::find($unitId) : null;
        
        return view('guest.box-hydrant.index', compact('boxHydrants', 'units', 'selectedUnit', 'stats'));
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
        
        // Calculate stats from ALL data
        $statsQuery = RumahPompa::forAuthUser();
        if ($unitId) {
            $statsQuery->where('unit_id', $unitId);
        }
        $stats = [
            'total' => $statsQuery->count(),
            'baik' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['BAIK'])->count(),
            'isi_ulang' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['ISI ULANG'])->count(),
            'rusak' => (clone $statsQuery)->whereRaw('UPPER(status) = ?', ['RUSAK'])->count(),
        ];
        
        // Get selected unit info
        $selectedUnit = $unitId ? Unit::find($unitId) : null;
        
        return view('guest.rumah-pompa.index', compact('rumahPompas', 'units', 'selectedUnit', 'stats'));
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

        // Calculate summary using case-insensitive comparison
        $summary = [
            'total_equipment' => $apars->count() + $apats->count() + $p3ks->count() +
                $apabs->count() + $fireAlarms->count() + $boxHydrants->count() +
                $rumahPompas->count(),
            'total_baik' => $apars->filter(fn($i) => strtolower($i->status) === 'baik')->count() +
                $apats->filter(fn($i) => strtolower($i->status) === 'baik')->count() +
                $p3ks->filter(fn($i) => strtolower($i->status) === 'baik')->count() +
                $apabs->filter(fn($i) => strtolower($i->status) === 'baik')->count() +
                $fireAlarms->filter(fn($i) => strtolower($i->status) === 'baik')->count() +
                $boxHydrants->filter(fn($i) => strtolower($i->status) === 'baik')->count() +
                $rumahPompas->filter(fn($i) => strtolower($i->status) === 'baik')->count(),
            'total_rusak' => $apars->filter(fn($i) => strtolower($i->status) === 'rusak')->count() +
                $apats->filter(fn($i) => strtolower($i->status) === 'rusak')->count() +
                $p3ks->filter(fn($i) => strtolower($i->status) === 'rusak')->count() +
                $apabs->filter(fn($i) => strtolower($i->status) !== 'baik')->count() +
                $fireAlarms->filter(fn($i) => strtolower($i->status) === 'rusak')->count() +
                $boxHydrants->filter(fn($i) => strtolower($i->status) === 'rusak')->count() +
                $rumahPompas->filter(fn($i) => strtolower($i->status) === 'rusak')->count(),
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

        // Calculate summary using case-insensitive comparison
        $summary = [
            'total_equipment' => $apars->count() + $apats->count() + $p3ks->count() +
                $apabs->count() + $fireAlarms->count() + $boxHydrants->count() +
                $rumahPompas->count(),
            'total_baik' => $apars->filter(fn($i) => strtolower($i->status) === 'baik')->count() +
                $apats->filter(fn($i) => strtolower($i->status) === 'baik')->count() +
                $p3ks->filter(fn($i) => strtolower($i->status) === 'baik')->count() +
                $apabs->filter(fn($i) => strtolower($i->status) === 'baik')->count() +
                $fireAlarms->filter(fn($i) => strtolower($i->status) === 'baik')->count() +
                $boxHydrants->filter(fn($i) => strtolower($i->status) === 'baik')->count() +
                $rumahPompas->filter(fn($i) => strtolower($i->status) === 'baik')->count(),
            'total_rusak' => $apars->filter(fn($i) => strtolower($i->status) === 'rusak')->count() +
                $apats->filter(fn($i) => strtolower($i->status) === 'rusak')->count() +
                $p3ks->filter(fn($i) => strtolower($i->status) === 'rusak')->count() +
                $apabs->filter(fn($i) => strtolower($i->status) !== 'baik')->count() +
                $fireAlarms->filter(fn($i) => strtolower($i->status) === 'rusak')->count() +
                $boxHydrants->filter(fn($i) => strtolower($i->status) === 'rusak')->count() +
                $rumahPompas->filter(fn($i) => strtolower($i->status) === 'rusak')->count(),
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
        $statsService = new EquipmentStatsService();

        // Determine unit context
        $user = auth()->user();
        $unitId = null;
        if ($user && $user->unit_id) {
            $unitId = $user->unit_id;
        } elseif ($user && !$user->unit_id && session('viewing_unit_id')) {
            $unitId = session('viewing_unit_id');
        }

        // Stats from centralized service
        $stats = $statsService->getStatusBreakdown($unitId);

        // Trend from centralized service
        $trendData = $statsService->getInspectionTrend($unitId, 6);

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
