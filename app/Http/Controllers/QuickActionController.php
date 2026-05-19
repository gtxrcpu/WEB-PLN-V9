<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FiltersByUnit;
use App\Models\Apar;
use App\Models\Apat;
use App\Models\Apab;
use App\Models\FireAlarm;
use App\Models\BoxHydrant;
use App\Models\RumahPompa;
use App\Models\KartuKendali;
use App\Models\KartuApat;
use App\Models\KartuApab;
use App\Models\KartuFireAlarm;
use App\Models\KartuBoxHydrant;
use App\Models\KartuRumahPompa;
use Illuminate\Http\Request;

class QuickActionController extends Controller
{
    use FiltersByUnit;

    /**
     * Check if user can access equipment from given unit
     */
    private function canAccessEquipment($equipment): bool
    {
        // Superadmin and Inspector can access all units
        if (auth()->user()->hasAnyRole(['superadmin', 'inspector'])) {
            return true;
        }

        // Check if equipment unit matches user unit
        $userUnitId = $this->getAuthUserUnitId();
        return $equipment->unit_id == $userUnitId;
    }

    // Scan QR
    public function scan()
    {
        return view('quick.scan');
    }

    public function searchQR(Request $request)
    {
        // Validate and sanitize input
        $request->validate([
            'qr' => 'required|string|max:1000'
        ]);

        // Accept both GET and POST
        $qr = $request->input('qr') ?? $request->query('qr');

        // Sanitize QR input to prevent XSS
        $qr = strip_tags($qr);

        // DEBUG: Log QR input untuk troubleshooting
        \Log::info('QR Scan Attempt', [
            'qr_input' => $qr,
            'user_id' => auth()->id(),
            'user_unit_id' => $this->getAuthUserUnitId(),
            'user_role' => auth()->user()->role
        ]);

        // Try to decode JSON format (new format)
        $decoded = json_decode($qr, true);
        if ($decoded && isset($decoded['type']) && isset($decoded['code'])) {
            $type = strtolower($decoded['type']);
            $code = $decoded['code'];

            \Log::info('QR Decoded JSON', ['type' => $type, 'code' => $code]);

            // Sanitize code to prevent SQL injection (additional safety)
            $code = strip_tags($code);

            // Search by code based on type
            if ($type === 'apar') {
                $equipment = Apar::where('barcode', $code)->orWhere('serial_no', $code)->first();
                if ($equipment) {
                    \Log::info('APAR Found', [
                        'id' => $equipment->id,
                        'serial' => $equipment->serial_no,
                        'unit_id' => $equipment->unit_id
                    ]);

                    // UNIT ACCESS CONTROL CHECK
                    if (!$this->canAccessEquipment($equipment)) {
                        \Log::warning('Access Denied', [
                            'equipment_unit' => $equipment->unit_id,
                            'user_unit' => $this->getAuthUserUnitId()
                        ]);

                        return back()->withErrors([
                            'qr' => 'Anda tidak memiliki akses ke APAR dari unit lain. QR Code ini untuk unit: ' .
                                ($equipment->unit ? $equipment->unit->name : 'Induk')
                        ]);
                    }

                    return view('quick.scan-result', [
                        'equipment' => $equipment,
                        'type' => 'apar',
                        'typeName' => 'APAR'
                    ]);
                }
            } elseif ($type === 'apat') {
                $equipment = Apat::where('barcode', $code)->orWhere('serial_no', $code)->first();
                if ($equipment) {
                    // UNIT ACCESS CONTROL CHECK
                    if (!$this->canAccessEquipment($equipment)) {
                        return back()->withErrors([
                            'qr' => 'Anda tidak memiliki akses ke APAT dari unit lain. QR Code ini untuk unit: ' .
                                ($equipment->unit ? $equipment->unit->name : 'Induk')
                        ]);
                    }

                    return view('quick.scan-result', [
                        'equipment' => $equipment,
                        'type' => 'apat',
                        'typeName' => 'APAT'
                    ]);
                }
            } elseif ($type === 'apab') {
                $equipment = Apab::where('barcode', $code)->orWhere('serial_no', $code)->first();
                if ($equipment) {
                    // UNIT ACCESS CONTROL CHECK
                    if (!$this->canAccessEquipment($equipment)) {
                        return back()->withErrors([
                            'qr' => 'Anda tidak memiliki akses ke APAB dari unit lain. QR Code ini untuk unit: ' .
                                ($equipment->unit ? $equipment->unit->name : 'Induk')
                        ]);
                    }

                    return view('quick.scan-result', [
                        'equipment' => $equipment,
                        'type' => 'apab',
                        'typeName' => 'APAB'
                    ]);
                }
            } elseif ($type === 'fire alarm' || $type === 'fire-alarm') {
                $equipment = FireAlarm::where('barcode', $code)->orWhere('serial_no', $code)->first();
                if ($equipment) {
                    // UNIT ACCESS CONTROL CHECK
                    if (!$this->canAccessEquipment($equipment)) {
                        return back()->withErrors([
                            'qr' => 'Anda tidak memiliki akses ke Fire Alarm dari unit lain. QR Code ini untuk unit: ' .
                                ($equipment->unit ? $equipment->unit->name : 'Induk')
                        ]);
                    }

                    return view('quick.scan-result', [
                        'equipment' => $equipment,
                        'type' => 'fire-alarm',
                        'typeName' => 'Fire Alarm'
                    ]);
                }
            } elseif ($type === 'box hydrant' || $type === 'box-hydrant') {
                $equipment = BoxHydrant::where('barcode', $code)->orWhere('serial_no', $code)->first();
                if ($equipment) {
                    // UNIT ACCESS CONTROL CHECK
                    if (!$this->canAccessEquipment($equipment)) {
                        return back()->withErrors([
                            'qr' => 'Anda tidak memiliki akses ke Box Hydrant dari unit lain. QR Code ini untuk unit: ' .
                                ($equipment->unit ? $equipment->unit->name : 'Induk')
                        ]);
                    }

                    return view('quick.scan-result', [
                        'equipment' => $equipment,
                        'type' => 'box-hydrant',
                        'typeName' => 'Box Hydrant'
                    ]);
                }
            } elseif ($type === 'rumah pompa' || $type === 'rumah-pompa') {
                $equipment = RumahPompa::where('barcode', $code)->orWhere('serial_no', $code)->first();
                if ($equipment) {
                    // UNIT ACCESS CONTROL CHECK
                    if (!$this->canAccessEquipment($equipment)) {
                        return back()->withErrors([
                            'qr' => 'Anda tidak memiliki akses ke Rumah Pompa dari unit lain. QR Code ini untuk unit: ' .
                                ($equipment->unit ? $equipment->unit->name : 'Induk')
                        ]);
                    }

                    return view('quick.scan-result', [
                        'equipment' => $equipment,
                        'type' => 'rumah-pompa',
                        'typeName' => 'Rumah Pompa'
                    ]);
                }
            }
        }

        // Extract ID from URL if QR contains full URL (backward compatibility)
        // Example: http://127.0.0.1:8000/apar/2/riwayat -> extract "2" and "apar"
        if (preg_match('#/(apar|apat|apab|fire-alarm|box-hydrant|rumah-pompa)/(\d+)#', $qr, $matches)) {
            $module = $matches[1];
            $id = (int) $matches[2]; // Cast to int for safety

            // Search by ID based on module
            if ($module === 'apar') {
                $equipment = Apar::find($id);
                if ($equipment) {
                    // UNIT ACCESS CONTROL CHECK
                    if (!$this->canAccessEquipment($equipment)) {
                        return back()->withErrors([
                            'qr' => 'Anda tidak memiliki akses ke APAR dari unit lain. QR Code ini untuk unit: ' .
                                ($equipment->unit ? $equipment->unit->name : 'Induk')
                        ]);
                    }

                    return view('quick.scan-result', [
                        'equipment' => $equipment,
                        'type' => 'apar',
                        'typeName' => 'APAR'
                    ]);
                }
            } elseif ($module === 'apat') {
                $equipment = Apat::find($id);
                if ($equipment) {
                    if (!$this->canAccessEquipment($equipment)) {
                        return back()->withErrors([
                            'qr' => 'Anda tidak memiliki akses ke APAT dari unit lain. QR Code ini untuk unit: ' .
                                ($equipment->unit ? $equipment->unit->name : 'Induk')
                        ]);
                    }
                    return view('quick.scan-result', [
                        'equipment' => $equipment,
                        'type' => 'apat',
                        'typeName' => 'APAT'
                    ]);
                }
            } elseif ($module === 'apab') {
                $equipment = Apab::find($id);
                if ($equipment) {
                    if (!$this->canAccessEquipment($equipment)) {
                        return back()->withErrors([
                            'qr' => 'Anda tidak memiliki akses ke APAB dari unit lain. QR Code ini untuk unit: ' .
                                ($equipment->unit ? $equipment->unit->name : 'Induk')
                        ]);
                    }
                    return view('quick.scan-result', [
                        'equipment' => $equipment,
                        'type' => 'apab',
                        'typeName' => 'APAB'
                    ]);
                }
            } elseif ($module === 'fire-alarm') {
                $equipment = FireAlarm::find($id);
                if ($equipment) {
                    if (!$this->canAccessEquipment($equipment)) {
                        return back()->withErrors([
                            'qr' => 'Anda tidak memiliki akses ke Fire Alarm dari unit lain. QR Code ini untuk unit: ' .
                                ($equipment->unit ? $equipment->unit->name : 'Induk')
                        ]);
                    }
                    return view('quick.scan-result', [
                        'equipment' => $equipment,
                        'type' => 'fire-alarm',
                        'typeName' => 'Fire Alarm'
                    ]);
                }
            } elseif ($module === 'box-hydrant') {
                $equipment = BoxHydrant::find($id);
                if ($equipment) {
                    if (!$this->canAccessEquipment($equipment)) {
                        return back()->withErrors([
                            'qr' => 'Anda tidak memiliki akses ke Box Hydrant dari unit lain. QR Code ini untuk unit: ' .
                                ($equipment->unit ? $equipment->unit->name : 'Induk')
                        ]);
                    }
                    return view('quick.scan-result', [
                        'equipment' => $equipment,
                        'type' => 'box-hydrant',
                        'typeName' => 'Box Hydrant'
                    ]);
                }
            } elseif ($module === 'rumah-pompa') {
                $equipment = RumahPompa::find($id);
                if ($equipment) {
                    if (!$this->canAccessEquipment($equipment)) {
                        return back()->withErrors([
                            'qr' => 'Anda tidak memiliki akses ke Rumah Pompa dari unit lain. QR Code ini untuk unit: ' .
                                ($equipment->unit ? $equipment->unit->name : 'Induk')
                        ]);
                    }
                    return view('quick.scan-result', [
                        'equipment' => $equipment,
                        'type' => 'rumah-pompa',
                        'typeName' => 'Rumah Pompa'
                    ]);
                }
            }
        }

        // Fallback: Search by barcode or serial_no in all modules
        $apar = Apar::where('barcode', $qr)->orWhere('serial_no', $qr)->first();
        if ($apar) {
            // UNIT ACCESS CONTROL CHECK
            if (!$this->canAccessEquipment($apar)) {
                return back()->withErrors([
                    'qr' => 'Anda tidak memiliki akses ke APAR dari unit lain. QR Code ini untuk unit: ' .
                        ($apar->unit ? $apar->unit->name : 'Induk')
                ]);
            }

            return view('quick.scan-result', [
                'equipment' => $apar,
                'type' => 'apar',
                'typeName' => 'APAR'
            ]);
        }

        $apat = Apat::where('barcode', $qr)->orWhere('serial_no', $qr)->first();
        if ($apat) {
            if (!$this->canAccessEquipment($apat)) {
                return back()->withErrors([
                    'qr' => 'Anda tidak memiliki akses ke APAT dari unit lain. QR Code ini untuk unit: ' .
                        ($apat->unit ? $apat->unit->name : 'Induk')
                ]);
            }
            return view('quick.scan-result', [
                'equipment' => $apat,
                'type' => 'apat',
                'typeName' => 'APAT'
            ]);
        }

        $apab = Apab::where('barcode', $qr)->orWhere('serial_no', $qr)->first();
        if ($apab) {
            if (!$this->canAccessEquipment($apab)) {
                return back()->withErrors([
                    'qr' => 'Anda tidak memiliki akses ke APAB dari unit lain. QR Code ini untuk unit: ' .
                        ($apab->unit ? $apab->unit->name : 'Induk')
                ]);
            }
            return view('quick.scan-result', [
                'equipment' => $apab,
                'type' => 'apab',
                'typeName' => 'APAB'
            ]);
        }

        $fireAlarm = FireAlarm::where('barcode', $qr)->orWhere('serial_no', $qr)->first();
        if ($fireAlarm) {
            if (!$this->canAccessEquipment($fireAlarm)) {
                return back()->withErrors([
                    'qr' => 'Anda tidak memiliki akses ke Fire Alarm dari unit lain. QR Code ini untuk unit: ' .
                        ($fireAlarm->unit ? $fireAlarm->unit->name : 'Induk')
                ]);
            }
            return view('quick.scan-result', [
                'equipment' => $fireAlarm,
                'type' => 'fire-alarm',
                'typeName' => 'Fire Alarm'
            ]);
        }

        $boxHydrant = BoxHydrant::where('barcode', $qr)->orWhere('serial_no', $qr)->first();
        if ($boxHydrant) {
            if (!$this->canAccessEquipment($boxHydrant)) {
                return back()->withErrors([
                    'qr' => 'Anda tidak memiliki akses ke Box Hydrant dari unit lain. QR Code ini untuk unit: ' .
                        ($boxHydrant->unit ? $boxHydrant->unit->name : 'Induk')
                ]);
            }
            return view('quick.scan-result', [
                'equipment' => $boxHydrant,
                'type' => 'box-hydrant',
                'typeName' => 'Box Hydrant'
            ]);
        }

        $rumahPompa = RumahPompa::where('barcode', $qr)->orWhere('serial_no', $qr)->first();
        if ($rumahPompa) {
            if (!$this->canAccessEquipment($rumahPompa)) {
                return back()->withErrors([
                    'qr' => 'Anda tidak memiliki akses ke Rumah Pompa dari unit lain. QR Code ini untuk unit: ' .
                        ($rumahPompa->unit ? $rumahPompa->unit->name : 'Induk')
                ]);
            }
            return view('quick.scan-result', [
                'equipment' => $rumahPompa,
                'type' => 'rumah-pompa',
                'typeName' => 'Rumah Pompa'
            ]);
        }

        return back()->with('error', 'QR Code tidak ditemukan');
    }

    // Buat Inspeksi
    public function inspeksi()
    {
        // Get items scoped to user's unit
        $apars = Apar::forAuthUser()->orderBy('serial_no')->get();
        $apats = Apat::forAuthUser()->orderBy('serial_no')->get();
        $apabs = Apab::forAuthUser()->orderBy('serial_no')->get();
        $fireAlarms = FireAlarm::forAuthUser()->orderBy('serial_no')->get();
        $boxHydrants = BoxHydrant::forAuthUser()->orderBy('serial_no')->get();
        $rumahPompas = RumahPompa::forAuthUser()->orderBy('serial_no')->get();

        return view('quick.inspeksi', compact('apars', 'apats', 'apabs', 'fireAlarms', 'boxHydrants', 'rumahPompas'));
    }

    // Rekap & Export
    public function rekap(Request $request)
    {
        // Get filter parameters
        $selectedYear = $request->input('year');
        $selectedMonth = $request->input('month');

        // Helper function to safely count table
        $safeCount = function ($tableName) {
            return collect();
        };

        // Get statistics scoped to user's unit
        $stats = [
            'apar' => [
                'total' => Apar::forAuthUser()->count(),
                'baik' => Apar::forAuthUser()->where('status', 'baik')->count(),
                'rusak' => Apar::forAuthUser()->where('status', 'rusak')->count(),
                'inspeksi' => \DB::table('kartu_apars')->count(),
            ],
            'apat' => [
                'total' => Apat::forAuthUser()->count(),
                'baik' => Apat::forAuthUser()->where('status', 'baik')->count(),
                'rusak' => Apat::forAuthUser()->where('status', 'rusak')->count(),
                'inspeksi' => \DB::table('kartu_apats')->count(),
            ],
            'apab' => [
                'total' => Apab::forAuthUser()->count(),
                'baik' => Apab::forAuthUser()->where('status', 'baik')->count(),
                'rusak' => Apab::forAuthUser()->where('status', 'tidak_baik')->count(),
                'inspeksi' => \DB::table('kartu_apabs')->count(),
            ],
            'fire_alarm' => [
                'total' => FireAlarm::forAuthUser()->count(),
                'baik' => FireAlarm::forAuthUser()->where('status', 'baik')->count(),
                'rusak' => FireAlarm::forAuthUser()->where('status', 'rusak')->count(),
                'inspeksi' => \DB::table('kartu_fire_alarms')->count(),
            ],
            'box_hydrant' => [
                'total' => BoxHydrant::forAuthUser()->count(),
                'baik' => BoxHydrant::forAuthUser()->where('status', 'baik')->count(),
                'rusak' => BoxHydrant::forAuthUser()->where('status', 'rusak')->count(),
                'inspeksi' => \DB::table('kartu_box_hydrants')->count(),
            ],
            'rumah_pompa' => [
                'total' => RumahPompa::forAuthUser()->count(),
                'baik' => RumahPompa::forAuthUser()->where('status', 'baik')->count(),
                'rusak' => RumahPompa::forAuthUser()->where('status', 'rusak')->count(),
                'inspeksi' => \DB::table('kartu_rumah_pompas')->count(),
            ],
        ];

        // Generate year options (current year and next 5 years)
        $currentYear = date('Y');
        $years = [];
        for ($i = 0; $i < 6; $i++) {
            $years[] = $currentYear + $i;
        }

        // Month options
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return view('quick.rekap', compact('stats', 'years', 'months', 'selectedYear', 'selectedMonth'));
    }

    public function exportExcel(Request $request)
    {
        $module = $request->input('module', 'all');
        $type = $request->input('type', 'equipment'); // 'equipment' or 'kartu'
        $year = $request->input('year');
        $month = $request->input('month');

        return \Excel::download(
            new \App\Exports\RekapExport($module, $type, $year, $month),
            "rekap_{$type}_{$module}_" . date('Y-m-d') . ".xlsx"
        );
    }

    public function exportPdf(Request $request)
    {
        $module = $request->input('module', 'all');
        $type = $request->input('type', 'equipment'); // 'equipment' or 'kartu'
        $year = $request->input('year');
        $month = $request->input('month');

        // Collect data with period filter
        $data = $type === 'kartu'
            ? $this->collectKartuExportData($module, $year, $month)
            : $this->collectExportData($module, $year, $month);

        $pdf = \PDF::loadView('exports.rekap-pdf', [
            'data' => $data,
            'module' => $module,
            'type' => $type,
            'date' => date('d/m/Y'),
            'year' => $year,
            'month' => $month
        ]);

        return $pdf->download("rekap_{$type}_{$module}_" . date('Y-m-d') . ".pdf");
    }

    private function collectExportData($module, $year = null, $month = null)
    {
        $data = [];

        if ($module === 'all' || $module === 'apar') {
            $items = Apar::forAuthUser()->get();
            foreach ($items as $item) {
                $data[] = [
                    'modul' => 'APAR',
                    'serial_no' => $item->serial_no,
                    'barcode' => $item->barcode,
                    'lokasi' => $item->location_code ?? '-',
                    'status' => $item->status ?? '-',
                    'kapasitas' => $item->capacity ?? '-',
                ];
            }
        }

        if ($module === 'all' || $module === 'apat') {
            $items = Apat::forAuthUser()->get();
            foreach ($items as $item) {
                $data[] = [
                    'modul' => 'APAT',
                    'serial_no' => $item->serial_no,
                    'barcode' => $item->barcode,
                    'lokasi' => $item->location_code ?? '-',
                    'status' => $item->status ?? '-',
                    'kapasitas' => $item->capacity ?? '-',
                ];
            }
        }

        if ($module === 'all' || $module === 'apab') {
            $items = Apab::forAuthUser()->get();
            foreach ($items as $item) {
                $data[] = [
                    'modul' => 'APAB',
                    'serial_no' => $item->serial_no,
                    'barcode' => $item->barcode,
                    'lokasi' => $item->location_code ?? '-',
                    'status' => $item->status ?? '-',
                    'kapasitas' => $item->capacity ?? '-',
                ];
            }
        }

        if ($module === 'all' || $module === 'fire_alarm') {
            $items = FireAlarm::forAuthUser()->get();
            foreach ($items as $item) {
                $data[] = [
                    'modul' => 'Fire Alarm',
                    'serial_no' => $item->serial_no,
                    'barcode' => $item->barcode,
                    'lokasi' => $item->location_code ?? '-',
                    'status' => $item->status ?? '-',
                    'kapasitas' => '-',
                ];
            }
        }

        if ($module === 'all' || $module === 'box_hydrant') {
            $items = BoxHydrant::forAuthUser()->get();
            foreach ($items as $item) {
                $data[] = [
                    'modul' => 'Box Hydrant',
                    'serial_no' => $item->serial_no,
                    'barcode' => $item->barcode,
                    'lokasi' => $item->location_code ?? '-',
                    'status' => $item->status ?? '-',
                    'kapasitas' => '-',
                ];
            }
        }

        if ($module === 'all' || $module === 'rumah_pompa') {
            $items = RumahPompa::forAuthUser()->get();
            foreach ($items as $item) {
                $data[] = [
                    'modul' => 'Rumah Pompa',
                    'serial_no' => $item->serial_no,
                    'barcode' => $item->barcode,
                    'lokasi' => $item->location_code ?? '-',
                    'status' => $item->status ?? '-',
                    'kapasitas' => '-',
                ];
            }
        }

        return $data;
    }

    private function collectKartuExportData($module, $year = null, $month = null)
    {
        $data = [];

        if ($module === 'all' || $module === 'apar') {
            $query = \App\Models\KartuApar::with(['apar', 'user', 'approver']);

            if ($year) {
                $query->whereYear('tgl_periksa', $year);
            }
            if ($month) {
                $query->whereMonth('tgl_periksa', $month);
            }

            $kartus = $query->get();
            foreach ($kartus as $kartu) {
                $data[] = [
                    'modul' => 'APAR',
                    'serial_no' => $kartu->apar->serial_no ?? '-',
                    'tgl_periksa' => $kartu->tgl_periksa ? $kartu->tgl_periksa->format('d/m/Y') : '-',
                    'kesimpulan' => $kartu->kesimpulan ?? '-',
                    'dibuat_oleh' => $kartu->user->name ?? 'User Deleted',
                    'tgl_dibuat' => $kartu->created_at ? $kartu->created_at->format('d/m/Y H:i') : '-',
                    'status' => $kartu->isApproved() ? 'Approved' : 'Pending',
                    'approved_oleh' => $kartu->approver->name ?? '-',
                    'tgl_approval' => $kartu->approved_at ? $kartu->approved_at->format('d/m/Y H:i') : '-',
                ];
            }
        }

        if ($module === 'all' || $module === 'apat') {
            $query = KartuApat::with(['apat', 'user', 'approver']);

            if ($year) {
                $query->whereYear('tgl_periksa', $year);
            }
            if ($month) {
                $query->whereMonth('tgl_periksa', $month);
            }

            $kartus = $query->get();
            foreach ($kartus as $kartu) {
                $data[] = [
                    'modul' => 'APAT',
                    'serial_no' => $kartu->apat->serial_no ?? '-',
                    'tgl_periksa' => $kartu->tgl_periksa ? $kartu->tgl_periksa->format('d/m/Y') : '-',
                    'kesimpulan' => $kartu->kesimpulan ?? '-',
                    'dibuat_oleh' => $kartu->user->name ?? 'User Deleted',
                    'tgl_dibuat' => $kartu->created_at ? $kartu->created_at->format('d/m/Y H:i') : '-',
                    'status' => $kartu->isApproved() ? 'Approved' : 'Pending',
                    'approved_oleh' => $kartu->approver->name ?? '-',
                    'tgl_approval' => $kartu->approved_at ? $kartu->approved_at->format('d/m/Y H:i') : '-',
                ];
            }
        }

        if ($module === 'all' || $module === 'apab') {
            $query = KartuApab::with(['apab', 'user', 'approver']);

            if ($year) {
                $query->whereYear('tgl_periksa', $year);
            }
            if ($month) {
                $query->whereMonth('tgl_periksa', $month);
            }

            $kartus = $query->get();
            foreach ($kartus as $kartu) {
                $data[] = [
                    'modul' => 'APAB',
                    'serial_no' => $kartu->apab->serial_no ?? '-',
                    'tgl_periksa' => $kartu->tgl_periksa ? $kartu->tgl_periksa->format('d/m/Y') : '-',
                    'kesimpulan' => $kartu->kesimpulan ?? '-',
                    'dibuat_oleh' => $kartu->user->name ?? 'User Deleted',
                    'tgl_dibuat' => $kartu->created_at ? $kartu->created_at->format('d/m/Y H:i') : '-',
                    'status' => $kartu->isApproved() ? 'Approved' : 'Pending',
                    'approved_oleh' => $kartu->approver->name ?? '-',
                    'tgl_approval' => $kartu->approved_at ? $kartu->approved_at->format('d/m/Y H:i') : '-',
                ];
            }
        }

        if ($module === 'all' || $module === 'fire_alarm') {
            $query = KartuFireAlarm::with(['fireAlarm', 'user', 'approver']);

            if ($year) {
                $query->whereYear('tgl_periksa', $year);
            }
            if ($month) {
                $query->whereMonth('tgl_periksa', $month);
            }

            $kartus = $query->get();
            foreach ($kartus as $kartu) {
                $data[] = [
                    'modul' => 'Fire Alarm',
                    'serial_no' => $kartu->fireAlarm->serial_no ?? '-',
                    'tgl_periksa' => $kartu->tgl_periksa ? $kartu->tgl_periksa->format('d/m/Y') : '-',
                    'kesimpulan' => $kartu->kesimpulan ?? '-',
                    'dibuat_oleh' => $kartu->user->name ?? 'User Deleted',
                    'tgl_dibuat' => $kartu->created_at ? $kartu->created_at->format('d/m/Y H:i') : '-',
                    'status' => $kartu->isApproved() ? 'Approved' : 'Pending',
                    'approved_oleh' => $kartu->approver->name ?? '-',
                    'tgl_approval' => $kartu->approved_at ? $kartu->approved_at->format('d/m/Y H:i') : '-',
                ];
            }
        }

        if ($module === 'all' || $module === 'box_hydrant') {
            $query = KartuBoxHydrant::with(['boxHydrant', 'user', 'approver']);

            if ($year) {
                $query->whereYear('tgl_periksa', $year);
            }
            if ($month) {
                $query->whereMonth('tgl_periksa', $month);
            }

            $kartus = $query->get();
            foreach ($kartus as $kartu) {
                $data[] = [
                    'modul' => 'Box Hydrant',
                    'serial_no' => $kartu->boxHydrant->serial_no ?? '-',
                    'tgl_periksa' => $kartu->tgl_periksa ? $kartu->tgl_periksa->format('d/m/Y') : '-',
                    'kesimpulan' => $kartu->kesimpulan ?? '-',
                    'dibuat_oleh' => $kartu->user->name ?? 'User Deleted',
                    'tgl_dibuat' => $kartu->created_at ? $kartu->created_at->format('d/m/Y H:i') : '-',
                    'status' => $kartu->isApproved() ? 'Approved' : 'Pending',
                    'approved_oleh' => $kartu->approver->name ?? '-',
                    'tgl_approval' => $kartu->approved_at ? $kartu->approved_at->format('d/m/Y H:i') : '-',
                ];
            }
        }

        if ($module === 'all' || $module === 'rumah_pompa') {
            $query = KartuRumahPompa::with(['rumahPompa', 'user', 'approver']);

            if ($year) {
                $query->whereYear('tgl_periksa', $year);
            }
            if ($month) {
                $query->whereMonth('tgl_periksa', $month);
            }

            $kartus = $query->get();
            foreach ($kartus as $kartu) {
                $data[] = [
                    'modul' => 'Rumah Pompa',
                    'serial_no' => $kartu->rumahPompa->serial_no ?? '-',
                    'tgl_periksa' => $kartu->tgl_periksa ? $kartu->tgl_periksa->format('d/m/Y') : '-',
                    'kesimpulan' => $kartu->kesimpulan ?? '-',
                    'dibuat_oleh' => $kartu->user->name ?? 'User Deleted',
                    'tgl_dibuat' => $kartu->created_at ? $kartu->created_at->format('d/m/Y H:i') : '-',
                    'status' => $kartu->isApproved() ? 'Approved' : 'Pending',
                    'approved_oleh' => $kartu->approver->name ?? '-',
                    'tgl_approval' => $kartu->approved_at ? $kartu->approved_at->format('d/m/Y H:i') : '-',
                ];
            }
        }

        return $data;
    }
}
