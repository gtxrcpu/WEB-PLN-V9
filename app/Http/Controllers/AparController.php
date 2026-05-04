<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FiltersByUnit;
use App\Models\Apar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AparController extends Controller
{
    use FiltersByUnit;
    /**
     * Tampilkan daftar APAR.
     */
    public function index()
    {
        $apars = $this->getQueryForAuthUser(Apar::class)
            ->orderBy('id')
            ->get();

        return view('apar.index', compact('apars'));
    }

    /**
     * Form tambah APAR.
     */
    public function create()
    {
        // Preview serial without incrementing counter
        $nextSerial = Apar::generateNextSerial(null, false);

        // default value kalau mau ditampilkan di form
        // Serial already contains "APAR A1.xxx"
        $default = [
            'serial_no' => $nextSerial,
            'name' => $nextSerial,
            'barcode' => $nextSerial,
            'status' => 'BAIK',
            'location_code' => 'BDG',
            'type' => 'UUV',
            'capacity' => '5 Liter',
            'agent' => '500',
        ];

        return view('apar.create', compact('nextSerial', 'default'));
    }

    /**
     * Simpan APAR baru.
     */
    public function store(Request $request)
    {
        // ── Idempotency: cegah double-submit ──────────────────────────────────
        $submissionToken = $request->input('_submission_token');
        if ($submissionToken) {
            $cacheKey = 'apar_create_' . Auth::id() . '_' . $submissionToken;
            if (cache()->has($cacheKey)) {
                Log::warning('AparController: Duplicate submission blocked', ['token' => $submissionToken]);
                return redirect()->route('apar.index')
                    ->with('warning', 'APAR sudah berhasil disimpan sebelumnya.');
            }
            cache()->put($cacheKey, true, 60);
        }

        $request->validate([
            'location_code' => 'required|string|max:50',
            'type'          => 'required|string|max:100',
            'capacity'      => 'required|string|max:100',
            'agent'         => 'nullable|string|max:100',
            'status'        => 'required|string|max:20',
            'notes'         => 'nullable|string',
            'floor_plan_id' => 'nullable|exists:floor_plans,id',
            'floor_plan_x'  => 'nullable|numeric|min:0|max:100',
            'floor_plan_y'  => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $apar = DB::transaction(function () use ($request) {
                // Generate serial dan increment counter (hanya sekali di dalam transaksi)
                $serial  = Apar::generateNextSerial(null, true);
                $barcode = $serial;

                $apar = Apar::create([
                    'user_id'       => Auth::id(),
                    'unit_id'       => $this->getAuthUserUnitId(),
                    'name'          => $serial,
                    'barcode'       => $barcode,
                    'serial_no'     => $serial,
                    'location_code' => $request->location_code,
                    'type'          => $request->type,
                    'capacity'      => $request->capacity,
                    'agent'         => $request->agent,
                    'status'        => $request->status,
                    'notes'         => $request->notes,
                    'floor_plan_id' => $request->floor_plan_id,
                    'floor_plan_x'  => $request->floor_plan_x,
                    'floor_plan_y'  => $request->floor_plan_y,
                ]);

                // generate QR untuk APAR baru
                $apar->generateQrSvg(true);

                return $apar;
            });
        } catch (\Throwable $e) {
            Log::error('AparController: Gagal menyimpan APAR', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'Gagal menyimpan APAR. Silakan coba lagi.');
        }

        return redirect()
            ->route('apar.index')
            ->with('success', 'APAR baru berhasil ditambahkan dengan barcode ' . $apar->serial_no);
    }

    /**
     * Form edit APAR.
     */
    public function edit(Apar $apar)
    {
        $this->authorizeUnit($apar);
        return view('apar.edit', compact('apar'));
    }

    /**
     * Update APAR.
     */
    public function update(Request $request, Apar $apar)
    {
        $this->authorizeUnit($apar);
        $request->validate([
            'location_code' => 'required|string|max:50',
            'type' => 'required|string|max:100',
            'capacity' => 'required|string|max:100',
            'agent' => 'nullable|string|max:100',
            'status' => 'required|string|max:20',
            'notes' => 'nullable|string',
            'floor_plan_id' => 'nullable|exists:floor_plans,id',
            'floor_plan_x' => 'nullable|numeric|min:0|max:100',
            'floor_plan_y' => 'nullable|numeric|min:0|max:100',
        ]);

        $apar->update([
            'location_code' => $request->location_code,
            'type' => $request->type,
            'capacity' => $request->capacity,
            'agent' => $request->agent,
            'status' => $request->status,
            'notes' => $request->notes,
            'floor_plan_id' => $request->floor_plan_id,
            'floor_plan_x' => $request->floor_plan_x,
            'floor_plan_y' => $request->floor_plan_y,
        ]);

        // kalau mau bisa regenerate QR (opsional, tapi nggak masalah)
        $apar->generateQrSvg(true);

        return redirect()
            ->route('apar.index')
            ->with('success', 'Data APAR ' . $apar->serial_no . ' berhasil diperbarui.');
    }

    /**
     * Tampilkan riwayat kartu kendali & kartu pemeriksaan APAR
     */
    public function riwayat(Request $request, Apar $apar)
    {
        // Check unit access
        $userUnitId = $this->getAuthUserUnitId();
        if (!auth()->user()->hasAnyRole(['superadmin', 'inspector'])) {
            if ($apar->unit_id != $userUnitId) {
                abort(403, 'Anda tidak memiliki akses ke APAR dari unit lain. QR Code ini untuk unit: ' .
                    ($apar->unit ? $apar->unit->name : 'Induk'));
            }
        }

        $query = $apar->kartuApars()->with(['signature', 'user', 'approver', 'leaderApprover']);

        // Filter by creator
        if ($request->filled('creator')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->creator . '%');
            });
        }

        // Filter by approver
        if ($request->filled('approver')) {
            $query->whereHas('approver', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->approver . '%');
            });
        }

        // Filter by approval status
        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->whereNotNull('approved_at');
            } elseif ($request->status === 'pending') {
                $query->whereNull('approved_at');
            }
        }

        // Filter by jenis kartu (kendali vs pemeriksaan)
        if ($request->filled('jenis')) {
            if ($request->jenis === 'pemeriksaan') {
                $query->where(function ($q) {
                    $q->whereNotNull('catatan')->where('catatan', 'like', '[PMK]%');
                });
            } elseif ($request->jenis === 'kendali') {
                $query->where(function ($q) {
                    $q->whereNull('catatan')->orWhere('catatan', 'not like', '[PMK]%');
                });
            }
        }

        $kartuKendali = $query->latest()->get();

        return view('apar.riwayat', compact('apar', 'kartuKendali'));
    }

    /**
     * Form kartu pemeriksaan APAR (tabel NO/LOKASI/NO.SERI/KONDISI/KETERANGAN)
     * Menerima apar_id untuk menampilkan form untuk APAR spesifik.
     */
    public function createPemeriksaan(Request $request)
    {
        $aparId = $request->query('apar_id');
        $apar   = Apar::findOrFail($aparId);

        $template = \App\Models\KartuTemplate::getTemplate('apar', $apar->unit_id);

        // Hitung nextRevisi — sama persis dengan kartu kendali
        $latestKartu = \App\Models\KartuApar::where('apar_id', $aparId)
            ->where(function ($q) {
                // Hanya kartu pemeriksaan (catatan diawali "[PMK]")
                $q->whereNotNull('catatan')->where('catatan', 'like', '[PMK]%');
            })
            ->orderBy('id', 'desc')
            ->first();

        if ($latestKartu && ($latestKartu->leader_rejected_at || $latestKartu->rejected_at)) {
            $nextRevisi = str_pad((int)($latestKartu->revisi ?? 0) + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $nextRevisi = '00';
        }

        return view('apar.kartu-pemeriksaan', compact('apar', 'template', 'nextRevisi'));
    }

    /**
     * Simpan kartu pemeriksaan APAR untuk satu APAR.
     */
    public function storePemeriksaan(Request $request)
    {
        $request->validate([
            'apar_id'     => ['required', 'exists:apars,id'],
            'tgl_periksa' => ['required', 'date'],
            'petugas'     => ['required', 'string', 'max:100'],
            'rows'        => ['required', 'array', 'min:1'],
            'rows.*.lokasi'         => ['nullable', 'string', 'max:255'],
            'rows.*.no_seri'        => ['nullable', 'string', 'max:100'],
            'rows.*.jenis_kimia'    => ['nullable', 'string', 'max:100'],
            'rows.*.berat'          => ['nullable', 'string', 'max:50'],
            'rows.*.kondisi'        => ['nullable', 'string', 'max:50'],
            'rows.*.tgl_pengisian'  => ['nullable', 'date'],
            'rows.*.tgl_kadaluarsa' => ['nullable', 'date'],
            'rows.*.keterangan'     => ['nullable', 'string', 'max:255'],
        ]);

        $apar = Apar::findOrFail($request->apar_id);

        // Tentukan revisi berdasarkan kartu pemeriksaan terakhir yang ditolak
        $latestKartu = \App\Models\KartuApar::where('apar_id', $apar->id)
            ->where(function ($q) {
                $q->whereNotNull('catatan')->where('catatan', 'like', '[PMK]%');
            })
            ->orderBy('id', 'desc')
            ->first();

        $revisi = ($latestKartu && ($latestKartu->leader_rejected_at || $latestKartu->rejected_at))
            ? str_pad((int)($latestKartu->revisi ?? 0) + 1, 2, '0', STR_PAD_LEFT)
            : '00';

        // Simpan setiap baris yang kondisinya diisi
        $saved = 0;
        foreach ($request->rows as $row) {
            if (empty($row['kondisi'])) continue;

            // Encode semua field ke catatan dengan prefix [PMK]
            $parts = [];
            if (!empty($row['lokasi']))         $parts[] = 'Lokasi: '         . $row['lokasi'];
            if (!empty($row['no_seri']))         $parts[] = 'No. Seri: '       . $row['no_seri'];
            if (!empty($row['jenis_kimia']))     $parts[] = 'Jenis Kimia: '    . $row['jenis_kimia'];
            if (!empty($row['berat']))           $parts[] = 'Berat: '          . $row['berat'];
            if (!empty($row['tgl_pengisian']))   $parts[] = 'Tgl Pengisian: '  . $row['tgl_pengisian'];
            if (!empty($row['tgl_kadaluarsa']))  $parts[] = 'Tgl Kadaluarsa: ' . $row['tgl_kadaluarsa'];
            if (!empty($row['keterangan']))      $parts[] = 'Ket: '            . $row['keterangan'];

            $catatan = '[PMK] ' . implode(' | ', $parts);

            \App\Models\KartuApar::create([
                'apar_id'       => $apar->id,
                'user_id'       => auth()->id(),
                'pressure_gauge'=> $row['kondisi'],
                'pin_segel'     => $row['kondisi'],
                'selang'        => $row['kondisi'],
                'tabung'        => $row['kondisi'],
                'label'         => $row['kondisi'],
                'kondisi_fisik' => $row['kondisi'],
                'kesimpulan'    => $row['kondisi'],
                'tgl_periksa'   => $request->tgl_periksa,
                'petugas'       => $request->petugas,
                'catatan'       => $catatan,
                'revisi'        => $revisi,
            ]);
            $saved++;
        }

        return redirect()
            ->route('apar.index')
            ->with('success', "Kartu Pemeriksaan APAR {$apar->serial_no} berhasil disimpan ({$saved} baris).");
    }

    /**
     * View detail kartu kendali dengan TTD
     * 
     * UNIT ACCESS CONTROL: Only allow access to APAR from same unit
     */
    public function viewKartu($aparId, $kartuId)
    {
        $apar = Apar::findOrFail($aparId);

        // Check unit access: petugas hanya bisa akses APAR dari unit mereka sendiri
        $userUnitId = $this->getAuthUserUnitId();

        // Superadmin dan Inspector bisa akses semua unit
        if (!auth()->user()->hasAnyRole(['superadmin', 'inspector'])) {
            // Cek apakah APAR ini dari unit yang sama
            if ($apar->unit_id != $userUnitId) {
                // Berbeda unit - tidak boleh akses
                abort(403, 'Anda tidak memiliki akses ke APAR dari unit lain. Kartu ini untuk unit: ' .
                    ($apar->unit ? $apar->unit->name : 'Induk'));
            }
        }

        $kartu = \App\Models\KartuApar::with(['signature', 'user', 'approver', 'leaderSignature', 'leaderApprover'])->findOrFail($kartuId);

        // Get template for APAR module with unit-specific address
        $template = \App\Models\KartuTemplate::getTemplate('apar', $apar->unit_id);

        // Fill template with real data
        if ($template) {
            // Map data berdasarkan label field
            $labelMap = [
                'No. Dokumen' => 'APAR-' . str_pad($kartu->id, 4, '0', STR_PAD_LEFT),
                'Revisi' => str_pad((string) ($kartu->revisi ?? '0'), 2, '0', STR_PAD_LEFT),
                'Tanggal' => \Carbon\Carbon::parse($kartu->tgl_periksa)->format('d F Y'),
                'Halaman' => '1 dari 1',
            ];

            // Update header fields dengan data real
            $headerFields = collect($template->header_fields)->map(function ($field) use ($labelMap) {
                // Cek apakah label ada di map
                if (isset($labelMap[$field['label']])) {
                    $field['value'] = $labelMap[$field['label']];
                }
                return $field;
            })->toArray();

            $template->header_fields = $headerFields;

            // Update footer fields dengan data real (lokasi tetap dari template)
            // Footer fields sudah OK dari template
        }

        return view('apar.view-kartu', compact('apar', 'kartu', 'template'));
    }
}
