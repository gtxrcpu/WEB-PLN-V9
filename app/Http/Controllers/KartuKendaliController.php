<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FiltersByUnit;
use App\Http\Controllers\Traits\AuthorizesEquipmentAccess;
use App\Models\Apar;
use App\Models\KartuApar;
use App\Models\KartuTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KartuKendaliController extends Controller
{
    use FiltersByUnit, AuthorizesEquipmentAccess;
    /**
     * Tampilkan form Kartu Kendali untuk 1 APAR.
     * URL: /kartu/create?apar_id=ID
     */
    public function create(Request $request)
    {
        $aparId = $request->query('apar_id');

        // kalau apar_id nggak ada / salah, langsung 404
        $apar = Apar::findOrFail($aparId);

        // Verify user has access to this APAR's unit
        $this->authorizeEquipmentUnit($apar, 'APAR');

        // Get template for APAR module with unit-specific address
        $template = \App\Models\KartuTemplate::getTemplate('apar', $apar->unit_id);

        // Calculate revisi number for display
        $latestKartu = KartuApar::where('apar_id', $aparId)
            ->orderBy('id', 'desc')
            ->first();

        if ($latestKartu && ($latestKartu->leader_rejected_at || $latestKartu->rejected_at)) {
            // Jika kartu sebelumnya ditolak, increment revisi
            $nextRevisi = str_pad((int)($latestKartu->revisi ?? 0) + 1, 2, '0', STR_PAD_LEFT);
        } else {
            // Default untuk kartu pertama atau setelah approved
            $nextRevisi = '00';
        }

        // pake view yang kamu kirim: resources/views/kartu/create.blade.php
        return view('kartu.create', compact('apar', 'template', 'nextRevisi'));
    }

    /**
     * Simpan kartu kendali ke database.
     * Route: POST /kartu  (name: kartu.store)
     */
    public function store(Request $request)
    {
        // ──────────────────────────────────────────────────────────────────────
        // 1. IDEMPOTENCY CHECK: Cek apakah submission dengan token yang sama
        //    sudah pernah diproses. Ini mencegah double-submit.
        // ──────────────────────────────────────────────────────────────────────
        $submissionToken = $request->input('_submission_token');
        if ($submissionToken) {
            $cacheKey = 'kartu_submit_' . auth()->id() . '_' . $submissionToken;
            if (cache()->has($cacheKey)) {
                Log::warning('KartuKendali: Duplicate submission detected', [
                    'token'   => $submissionToken,
                    'user_id' => auth()->id(),
                    'apar_id' => $request->input('apar_id'),
                ]);
                return redirect()
                    ->route('apar.riwayat', $request->input('apar_id'))
                    ->with('warning', 'Kartu kendali sudah berhasil disimpan sebelumnya.');
            }
            // Tandai token ini (expire 60 detik)
            cache()->put($cacheKey, true, 60);
        }

        // Get template untuk validasi dinamis
        $template = KartuTemplate::getTemplate('apar', $request->input('apar_id') ? Apar::find($request->input('apar_id'))->unit_id ?? null : null);

        // Verify user has access to this APAR's unit
        $aparForAuth = Apar::find($request->input('apar_id'));
        if ($aparForAuth) {
            $this->authorizeEquipmentUnit($aparForAuth, 'APAR');
        }

        // Kolom DB valid untuk kartu APAR
        $aparDbColumns = ['pressure_gauge', 'pin_segel', 'selang', 'tabung', 'label', 'kondisi_fisik'];

        // Build validation rules untuk field dasar
        $rules = [
            'apar_id'     => ['required', 'exists:apars,id'],
            'kesimpulan'  => ['required', 'string', 'max:50'],
            'tgl_periksa' => ['required', 'date'],
            'petugas'     => ['required', 'string', 'max:100'],
        ];

        // Custom messages
        $messages = [];

        if ($template && $template->inspection_fields) {
            foreach ($template->inspection_fields as $index => $field) {
                $fieldName = !empty($field['key']) ? $field['key'] : ('inspection_' . $index);
                $rules[$fieldName] = ['required', 'string', 'max:255'];
                $messages[$fieldName . '.required'] = 'Field "' . $field['label'] . '" wajib diisi.';
            }
        } else {
            foreach ($aparDbColumns as $col) {
                $rules[$col] = ['required', 'string', 'max:50'];
            }
        }

        $data = $request->validate($rules, $messages);

        // Map inspection_N fields ke kolom DB (ketika template key = NULL)
        foreach ($aparDbColumns as $i => $col) {
            $inspectionKey = 'inspection_' . $i;
            if (isset($data[$inspectionKey]) && !isset($data[$col])) {
                $data[$col] = $data[$inspectionKey];
                unset($data[$inspectionKey]);
            }
        }

        // Bersihkan key yang tidak ada di kolom DB
        $allowedKeys = array_merge($aparDbColumns, ['apar_id', 'kesimpulan', 'tgl_periksa', 'petugas']);
        $saveData    = array_intersect_key($data, array_flip($allowedKeys));

        // Pastikan semua kolom APAR minimal ada
        foreach ($aparDbColumns as $col) {
            if (empty($saveData[$col])) {
                $saveData[$col] = '-';
            }
        }

        $saveData['user_id'] = auth()->id();

        // ──────────────────────────────────────────────────────────────────────
        // 2. RACE-CONDITION CHECK: Cegah duplikat dari request bersamaan
        //    (apar_id + user_id + tgl_periksa yang sama dalam 10 detik)
        // ──────────────────────────────────────────────────────────────────────
        $recentDuplicate = KartuApar::where('apar_id', $saveData['apar_id'])
            ->where('user_id', $saveData['user_id'])
            ->where('tgl_periksa', $saveData['tgl_periksa'])
            ->where('created_at', '>=', now()->subSeconds(10))
            ->first();

        if ($recentDuplicate) {
            Log::warning('KartuKendali: Race-condition duplicate prevented', [
                'existing_id' => $recentDuplicate->id,
                'apar_id'     => $saveData['apar_id'],
                'user_id'     => $saveData['user_id'],
            ]);
            return redirect()
                ->route('apar.riwayat', $saveData['apar_id'])
                ->with('success', 'Kartu Kendali berhasil disimpan dan menunggu persetujuan leader.');
        }

        // Tentukan revisi berdasarkan kartu terakhir
        $latestKartu = KartuApar::where('apar_id', $saveData['apar_id'])
            ->orderBy('id', 'desc')
            ->first();

        if ($latestKartu && ($latestKartu->leader_rejected_at || $latestKartu->rejected_at)) {
            // Jika kartu terakhir ditolak, increment revisi
            $saveData['revisi'] = str_pad((int)($latestKartu->revisi ?? 0) + 1, 2, '0', STR_PAD_LEFT);
        } else {
            // Jika kartu terakhir di-approve atau belum ada kartu, reset ke 00
            $saveData['revisi'] = '00';
        }

        // ──────────────────────────────────────────────────────────────────────
        // 3. SIMPAN DALAM DB TRANSACTION untuk atomic operation
        // ──────────────────────────────────────────────────────────────────────
        try {
            $kartu = DB::transaction(function () use ($saveData) {
                return KartuApar::create($saveData);
            });
        } catch (\Throwable $e) {
            Log::error('KartuKendali: Gagal menyimpan kartu', [
                'error'   => $e->getMessage(),
                'apar_id' => $saveData['apar_id'] ?? null,
            ]);
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan kartu kendali. Silakan coba lagi.');
        }

        return redirect()
            ->route('apar.riwayat', $saveData['apar_id'])
            ->with('success', 'Kartu Kendali berhasil disimpan dan menunggu persetujuan leader.');
    }
}

