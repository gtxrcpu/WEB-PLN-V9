<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FiltersByUnit;
use App\Models\P3k;
use App\Models\KartuP3k;
use App\Models\KartuP3kPemeriksaan;
use App\Models\KartuP3kPemakaian;
use App\Models\KartuP3kStock;
use App\Models\KartuTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KartuP3kController extends Controller
{
    use FiltersByUnit;

    /**
     * =========================================================
     * FORMAT KODE NOMOR KARTU P3K
     * =========================================================
     * Setiap jenis transaksi P3K memiliki prefix kode yang UNIK
     *
     *   Pemeriksaan : P3K-PMKS-{NNN}
     *   Pemakaian   : P3K-PMK-{NNN}
     *   Stock       : P3K-STK-{NNN}
     *
     * Contoh:
     *   P3K-PMKS-001  → Kartu Pemeriksaan no. urut 001
     *   P3K-PMK-001   → Kartu Pemakaian no. urut 001
     *   P3K-STK-001   → Kartu Stock no. urut 001
     *
     * Kode dan sequence dijamin unik per UNIT dengan:
     *   1. UNIQUE INDEX pada (nomor_kartu, unit_id)
     *   2. DB::transaction + SELECT ... FOR UPDATE filter unit_id
     * =========================================================
     */

    /**
     * Prefix map per jenis transaksi.
     * JANGAN diubah tanpa migrasi data + unique index baru.
     *
     * @var array<string, string>
     */
    private const PREFIX_MAP = [
        'pemeriksaan' => 'P3K-PMKS',
        'pemakaian'   => 'P3K-PMK',
        'stock'       => 'P3K-STK',
    ];

    /**
     * Model class map per jenis transaksi.
     *
     * @var array<string, class-string>
     */
    private const MODEL_MAP = [
        'pemeriksaan' => KartuP3kPemeriksaan::class,
        'pemakaian'   => KartuP3kPemakaian::class,
        'stock'       => KartuP3kStock::class,
    ];

    /**
     * Generate nomor kartu unik per jenis dan unit.
     *
     * Format: {PREFIX}-{NNN}
     *
     *   Pemeriksaan : P3K-PMKS-{NNN}
     *   Pemakaian   : P3K-PMK-{NNN}
     *   Stock       : P3K-STK-{NNN}
     *
     * Menggunakan SELECT ... FOR UPDATE di dalam transaksi agar
     * concurrent requests tidak menghasilkan nomor duplikat per unit.
     *
     * @param  string   $jenis   'pemeriksaan' | 'pemakaian' | 'stock'
     * @param  int|null $unitId  ID unit
     * @return string
     * @throws \InvalidArgumentException jika $jenis tidak dikenal
     */
    public function generateNomorKartu(string $jenis, ?int $unitId): string
    {
        if (!array_key_exists($jenis, self::PREFIX_MAP)) {
            throw new \InvalidArgumentException(
                "Jenis kartu tidak dikenal: '{$jenis}'. " .
                "Gunakan salah satu dari: " . implode(', ', array_keys(self::PREFIX_MAP))
            );
        }

        $prefix = self::PREFIX_MAP[$jenis];
        $model  = self::MODEL_MAP[$jenis];

        // Format: P3K-PMKS-{NNN}
        $fullPrefix = $prefix . '-';

        // Transaksi + SELECT ... FOR UPDATE untuk mencegah race condition
        return DB::transaction(function () use ($model, $fullPrefix, $unitId) {
            // Lock baris dengan prefix yang sama dan unit_id yang sama untuk serialisasi
            $query = $model::where('nomor_kartu', 'like', $fullPrefix . '%');
            
            if ($unitId) {
                $query->where('unit_id', $unitId);
            } else {
                $query->whereNull('unit_id');
            }

            $last = $query->lockForUpdate()
                ->orderByRaw('CAST(SUBSTRING_INDEX(nomor_kartu, "-", -1) AS UNSIGNED) DESC')
                ->value('nomor_kartu');

            $nextNum = 1;
            if ($last && preg_match('/-(\d+)$/', $last, $m)) {
                $nextNum = (int) $m[1] + 1;
            }

            return $fullPrefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────────────────────────────────

    public function create(Request $request)
    {
        $jenis  = $request->query('jenis', 'stock');
        $p3kId  = $request->query('p3k_id');
        $lokasi = $request->query('lokasi', null);

        // Resolve P3K dari p3k_id jika tersedia (flow pilih-lokasi)
        $selectedP3k = $p3kId ? P3k::find($p3kId) : null;

        // Tentukan unit_id dari auth user (prioritas) atau dari P3K
        $unitId = $this->getAuthUserUnitId()
            ?? $selectedP3k?->unit_id;

        // Pemeriksaan — view khusus dengan dukungan template
        if ($jenis === 'pemeriksaan') {
            $template = KartuTemplate::getTemplate('p3k-pemeriksaan');

            $lastUnapproved = KartuP3kPemeriksaan::whereNull('approved_at')
                ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
                ->orderBy('revisi', 'desc')
                ->first();

            $nextRevisi = $lastUnapproved
                ? str_pad((int) $lastUnapproved->revisi + 1, 2, '0', STR_PAD_LEFT)
                : '00';

            return view('p3k.kartu.kartu-pemeriksaan', compact('template', 'nextRevisi', 'selectedP3k'));
        }

        // Pemakaian — view khusus dengan dukungan template
        if ($jenis === 'pemakaian') {
            $template = KartuTemplate::getTemplate('p3k-pemakaian');

            $lastUnapproved = KartuP3kPemakaian::whereNull('approved_at')
                ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
                ->orderBy('revisi', 'desc')
                ->first();

            $nextRevisi = $lastUnapproved
                ? str_pad((int) $lastUnapproved->revisi + 1, 2, '0', STR_PAD_LEFT)
                : '00';

            return view('p3k.kartu.kartu-pemakaian', compact('template', 'nextRevisi', 'selectedP3k'));
        }

        // Stock
        $template = KartuTemplate::getTemplate('p3k-stock') ?? KartuTemplate::getTemplate('p3k');

        if ($selectedP3k) {
            $p3ks = collect([$selectedP3k]);
        } else {
            $lokasi = $lokasi ?? 'Area Limbah B3';
            $p3ks = P3k::where('location_code', 'like', '%' . $lokasi . '%')
                ->orWhere('name', 'like', '%' . $lokasi . '%')
                ->get();
        }

        return view('p3k.kartu.create', compact('jenis', 'lokasi', 'template', 'p3ks', 'selectedP3k'));
    }

    public function store(Request $request)
    {
        $jenis = $request->input('jenis', 'stock');

        if ($jenis === 'pemeriksaan') {
            return $this->storePemeriksaan($request);
        } elseif ($jenis === 'pemakaian') {
            return $this->storePemakaian($request);
        } else {
            return $this->storeStock($request);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE PEMERIKSAAN  →  kode P3K-PMKS-{NNN}
    // ─────────────────────────────────────────────────────────────────────────

    protected function storePemeriksaan(Request $request)
    {
        $validated = $request->validate([
            'p3k_id'                 => ['nullable', 'exists:p3ks,id'],
            'unit_kerja'             => ['required', 'string', 'max:255'],
            'tgl_periksa'            => ['required', 'date'],
            'bulan_tahun'            => ['required', 'string'],
            'petugas'                => ['required', 'string', 'max:255'],
            'items'                  => ['required', 'array'],
            'items.*.nama_barang'    => ['required', 'string'],
            'items.*.satuan'         => ['required', 'string'],
            'items.*.jumlah'         => ['required'],
            'items.*.fisik_visual'   => ['nullable', 'string'],
            'items.*.tgl_kadaluarsa' => ['nullable', 'date'],
            'items.*.catatan'        => ['nullable', 'string'],
        ]);

        // Prioritas: unit dari auth user (petugas), fallback ke unit P3K
        $unitId = $this->getAuthUserUnitId();
        if (!$unitId && !empty($validated['p3k_id'])) {
            $p3k    = P3k::find($validated['p3k_id']);
            $unitId = $p3k?->unit_id;
        }

        // Revisi scoped per unit
        $lastUnapproved = KartuP3kPemeriksaan::whereNull('approved_at')
            ->when($unitId, fn($q) => $q->where('unit_id', $unitId))
            ->orderBy('revisi', 'desc')
            ->first();

        $revisi = $lastUnapproved
            ? str_pad((int) $lastUnapproved->revisi + 1, 2, '0', STR_PAD_LEFT)
            : '00';

        // Generate kode P3K-PMKS-{NNN}
        $nomorKartu = $this->generateNomorKartu('pemeriksaan', $unitId);

        KartuP3kPemeriksaan::create([
            'nomor_kartu'      => $nomorKartu,
            'unit_id'          => $unitId,
            'p3k_id'           => $validated['p3k_id'] ?? null,
            'user_id'          => auth()->id(),
            'unit_kerja'       => $validated['unit_kerja'],
            'tgl_periksa'      => $validated['tgl_periksa'],
            'bulan_tahun'      => $validated['bulan_tahun'],
            'petugas'          => $validated['petugas'],
            'revisi'           => $revisi,
            'inspection_items' => $validated['items'],
        ]);

        return redirect()
            ->route('p3k.list-by-jenis', 'pemeriksaan')
            ->with('success', "Kartu Pemeriksaan {$nomorKartu} berhasil disimpan.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE PEMAKAIAN  →  kode P3K-PMK-{NNN}
    // ─────────────────────────────────────────────────────────────────────────

    protected function storePemakaian(Request $request)
    {
        $validated = $request->validate([
            'p3k_id'              => ['nullable', 'exists:p3ks,id'],
            'bulan'               => ['required', 'string'],
            'nomor'               => ['nullable', 'string', 'max:255'],
            'lokasi'              => ['required', 'string', 'max:255'],
            'entries'             => ['nullable', 'array'],
            'entries.*.nama'      => ['nullable', 'string'],
            'entries.*.item'      => ['nullable', 'string'],
            'entries.*.jumlah'    => ['nullable', 'integer', 'min:1'],
            'entries.*.keperluan' => ['nullable', 'string'],
            'entries.*.tanggal'   => ['nullable', 'date'],
        ]);

        // Filter entri kosong
        $entries = [];
        if (isset($validated['entries'])) {
            foreach ($validated['entries'] as $entry) {
                if (!empty($entry['nama']) || !empty($entry['item'])) {
                    $entries[] = $entry;
                }
            }
        }

        // Prioritas: unit dari auth user (petugas), fallback ke unit P3K
        $unitId = $this->getAuthUserUnitId();
        if (!$unitId && !empty($validated['p3k_id'])) {
            $p3k    = P3k::find($validated['p3k_id']);
            $unitId = $p3k?->unit_id;
        }

        // Revisi scoped per unit
        $lastUnapproved = KartuP3kPemakaian::whereNull('approved_at')
            ->when($unitId, fn($q) => $q->where('unit_id', $unitId))
            ->orderBy('revisi', 'desc')
            ->first();

        $revisi = $lastUnapproved
            ? str_pad((int) $lastUnapproved->revisi + 1, 2, '0', STR_PAD_LEFT)
            : '00';

        // Generate kode P3K-PMK-{NNN}
        $nomorKartu = $this->generateNomorKartu('pemakaian', $unitId);

        KartuP3kPemakaian::create([
            'nomor_kartu'   => $nomorKartu,
            'unit_id'       => $unitId,
            'p3k_id'        => $validated['p3k_id'] ?? null,
            'user_id'       => auth()->id(),
            'bulan'         => $validated['bulan'],
            'nomor'         => $validated['nomor'] ?? null,
            'lokasi'        => $validated['lokasi'],
            'revisi'        => $revisi,
            'usage_entries' => $entries,
        ]);

        return redirect()
            ->route('p3k.list-by-jenis', 'pemakaian')
            ->with('success', "Kartu Pemakaian {$nomorKartu} berhasil disimpan.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE STOCK  →  kode P3K-STK-{NNN}
    // ─────────────────────────────────────────────────────────────────────────

    protected function storeStock(Request $request)
    {
        $validated = $request->validate([
            'p3k_id'      => ['required', 'exists:p3ks,id'],
            'stock_items' => ['required', 'array'],
            'kesimpulan'  => ['required', 'string'],
            'tgl_periksa' => ['required', 'date'],
            'petugas'     => ['required', 'string', 'max:255'],
            'catatan'     => ['nullable', 'string'],
        ]);

        $p3k    = P3k::find($validated['p3k_id']);
        $unitId = $p3k?->unit_id;

        // Generate kode P3K-STK-{NNN}
        $nomorKartu = $this->generateNomorKartu('stock', $unitId);

        KartuP3kStock::create([
            'nomor_kartu' => $nomorKartu,
            'unit_id'     => $unitId,
            'p3k_id'      => $validated['p3k_id'],
            'user_id'     => auth()->id(),
            'stock_items' => $validated['stock_items'],
            'kesimpulan'  => $validated['kesimpulan'],
            'tgl_periksa' => $validated['tgl_periksa'],
            'petugas'     => $validated['petugas'],
            'catatan'     => $validated['catatan'] ?? null,
        ]);

        return redirect()
            ->route('p3k.list-by-jenis', 'stock')
            ->with('success', "Kartu Stock {$nomorKartu} berhasil disimpan.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BACKFILL / MIGRATION HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Backfill nomor_kartu untuk record lama (nomor_kartu = null).
     *
     * Jalankan sekali via Tinker:
     *   App\Http\Controllers\KartuP3kController::backfillNomorKartu();
     */
    public static function backfillNomorKartu(): void
    {
        $controller = new self();

        // Pemeriksaan — prefix P3K-PMKS
        $pemeriksaan = KartuP3kPemeriksaan::whereNull('nomor_kartu')->get();
        foreach ($pemeriksaan as $kartu) {
            $unitId = $kartu->unit_id ?? $kartu->p3k?->unit_id;
            $kartu->nomor_kartu = $controller->generateNomorKartu('pemeriksaan', $unitId);
            $kartu->unit_id     = $unitId;
            $kartu->save();
        }

        // Pemakaian — prefix P3K-PMK
        $pemakaian = KartuP3kPemakaian::whereNull('nomor_kartu')->get();
        foreach ($pemakaian as $kartu) {
            $unitId = $kartu->unit_id ?? $kartu->p3k?->unit_id;
            $kartu->nomor_kartu = $controller->generateNomorKartu('pemakaian', $unitId);
            $kartu->unit_id     = $unitId;
            $kartu->save();
        }

        // Stock — prefix P3K-STK
        $stock = KartuP3kStock::whereNull('nomor_kartu')->get();
        foreach ($stock as $kartu) {
            $unitId = $kartu->unit_id ?? $kartu->p3k?->unit_id;
            $kartu->nomor_kartu = $controller->generateNomorKartu('stock', $unitId);
            $kartu->unit_id     = $unitId;
            $kartu->save();
        }

        echo "Backfill selesai:\n";
        echo "  Pemeriksaan : " . $pemeriksaan->count() . " records\n";
        echo "  Pemakaian   : " . $pemakaian->count() . " records\n";
        echo "  Stock       : " . $stock->count() . " records\n";
    }

    /**
     * Perbaiki nomor_kartu lama yang menggunakan prefix format lama.
     *
     * Format lama yang harus diperbaiki:
     *   - Pemeriksaan: P3K-PMK-*  (harus → P3K-PMKS-*)
     *   - Pemakaian:   P3K-PKI-*  (harus → P3K-PMK-*)
     *
     * Jalankan via Tinker:
     *   App\Http\Controllers\KartuP3kController::fixLegacyNomorKartu();
     */
    public static function fixLegacyNomorKartu(): array
    {
        $controller = new self();
        $fixed = ['pemeriksaan' => 0, 'pemakaian' => 0];

        // Pemeriksaan yang masih menggunakan prefix P3K-PMK (bukan P3K-PMKS)
        $wrongPemeriksaan = KartuP3kPemeriksaan::where('nomor_kartu', 'like', 'P3K-PMK-%')
            ->where('nomor_kartu', 'not like', 'P3K-PMKS-%')
            ->get();

        foreach ($wrongPemeriksaan as $kartu) {
            $unitId = $kartu->unit_id ?? $kartu->p3k?->unit_id;
            $kartu->nomor_kartu = $controller->generateNomorKartu('pemeriksaan', $unitId);
            $kartu->save();
            $fixed['pemeriksaan']++;
        }

        // Pemakaian yang masih menggunakan prefix P3K-PKI (bukan P3K-PMK)
        $wrongPemakaian = KartuP3kPemakaian::where('nomor_kartu', 'like', 'P3K-PKI-%')->get();

        foreach ($wrongPemakaian as $kartu) {
            $unitId = $kartu->unit_id ?? $kartu->p3k?->unit_id;
            $kartu->nomor_kartu = $controller->generateNomorKartu('pemakaian', $unitId);
            $kartu->save();
            $fixed['pemakaian']++;
        }

        return $fixed;
    }
}
