<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FiltersByUnit;
use App\Models\KartuP3kPemakaian;
use App\Models\KartuP3kPemeriksaan;
use App\Models\KartuP3kStock;
use App\Models\P3k;
use App\Models\Unit;
use Illuminate\Http\Request;

class P3kController extends Controller
{
    use FiltersByUnit;

    // ─────────────────────────────────────────────────────────────────────────
    // INDEX – daftar semua P3K milik unit user
    // ─────────────────────────────────────────────────────────────────────────

    public function index()
    {
        $p3ks = $this->getQueryForAuthUser(P3k::class)
            ->orderBy('serial_no')
            ->get();

        return view('p3k.index', compact('p3ks'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CREATE / STORE P3K BARU
    // ─────────────────────────────────────────────────────────────────────────

    public function create(Request $request)
    {
        $jenis  = $request->query('jenis', 'pemeriksaan');
        $unitId = $this->getAuthUserUnitId();

        // Resolve unit code
        if ($unitId) {
            $unit     = Unit::find($unitId);
            $unitCode = strtoupper(str_replace([' ', '-'], '', $unit->code ?? $unit->name));
        } else {
            // Superadmin tanpa unit aktif — ambil unit pertama
            $unit     = Unit::orderBy('id')->first();
            $unitCode = $unit ? strtoupper(str_replace([' ', '-'], '', $unit->code ?? $unit->name)) : 'GEN';
        }

        // Prefix berbeda per jenis
        $jenisPrefix = match ($jenis) {
            'pemakaian' => 'P3K-PMK',
            'stock'     => 'P3K-STK',
            default     => 'P3K-PKS', // pemeriksaan
        };

        $prefix = $jenisPrefix . '-' . $unitCode . '-';

        // Cari nomor terakhir untuk prefix + unit ini
        $last = P3k::where('serial_no', 'like', $prefix . '%')
            ->where('jenis', $jenis)
            ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
            ->orderByRaw('CAST(SUBSTRING_INDEX(serial_no, "-", -1) AS UNSIGNED) DESC')
            ->value('serial_no');

        $nextNum = 1;
        if ($last && preg_match('/(\d+)$/', $last, $m)) {
            $nextNum = (int) $m[1] + 1;
        }
        $nextSerial = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        return view('p3k.create', compact('jenis', 'nextSerial', 'unitId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'barcode'       => ['required', 'string', 'max:255', 'unique:p3ks,barcode'],
            'serial_no'     => ['required', 'string', 'max:255', 'unique:p3ks,serial_no'],
            'location_code' => ['nullable', 'string', 'max:255'],
            'type'          => ['nullable', 'string', 'max:255'],
            'status'        => ['nullable', 'string', 'max:50'],
            'notes'         => ['nullable', 'string'],
        ]);

        $unitId = $this->getAuthUserUnitId();
        $jenis  = in_array($request->input('jenis'), ['pemeriksaan', 'pemakaian', 'stock'])
            ? $request->input('jenis')
            : 'pemeriksaan';

        $p3k = new P3k();
        $p3k->user_id       = auth()->id();
        $p3k->unit_id       = $unitId;
        $p3k->jenis         = $jenis;
        $p3k->name          = $data['name'];
        $p3k->barcode       = $data['barcode'];
        $p3k->serial_no     = $data['serial_no'];
        $p3k->location_code = $data['location_code'] ?? null;
        $p3k->type          = $data['type'] ?? null;
        $p3k->status        = $data['status'] ?? 'lengkap';
        $p3k->notes         = $data['notes'] ?? null;
        $p3k->save();

        $p3k->generateQrSvg(true);

        return redirect()
            ->route('p3k.list-by-jenis', $jenis)
            ->with('success', 'P3K ' . $p3k->serial_no . ' berhasil ditambahkan.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EDIT / UPDATE
    // ─────────────────────────────────────────────────────────────────────────

    public function edit(P3k $p3k)
    {
        $this->authorizeUnit($p3k);
        return view('p3k.edit', compact('p3k'));
    }

    public function update(Request $request, P3k $p3k)
    {
        $this->authorizeUnit($p3k);

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'location_code' => ['nullable', 'string', 'max:255'],
            'type'          => ['nullable', 'string', 'max:255'],
            'status'        => ['nullable', 'string', 'max:50'],
            'notes'         => ['nullable', 'string'],
        ]);

        $p3k->name          = $data['name'];
        $p3k->location_code = $data['location_code'] ?? null;
        $p3k->type          = $data['type'] ?? null;
        $p3k->status        = $data['status'] ?? null;
        $p3k->notes         = $data['notes'] ?? null;
        $p3k->save();

        return redirect()
            ->route('p3k.pilih-jenis')
            ->with('success', 'P3K ' . $p3k->serial_no . ' berhasil diperbarui.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────────────────────────────────

    public function destroy(P3k $p3k)
    {
        $this->authorizeUnit($p3k);
        $jenis = $p3k->jenis ?? 'pemeriksaan';
        $p3k->delete();

        return redirect()
            ->route('p3k.list-by-jenis', $jenis)
            ->with('success', 'P3K berhasil dihapus.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VIEW KARTU — tampilkan detail satu kartu (pemeriksaan / pemakaian / stock)
    // ─────────────────────────────────────────────────────────────────────────

    public function viewKartu(P3k $p3k, int $kartuId, \Illuminate\Http\Request $request)
    {
        $this->authorizeUnit($p3k);

        $jenis = $request->query('jenis', 'pemeriksaan');

        $kartu = match ($jenis) {
            'pemakaian' => \App\Models\KartuP3kPemakaian::with(['user', 'approver', 'signature'])->findOrFail($kartuId),
            'stock'     => \App\Models\KartuP3kStock::with(['user', 'approver', 'signature'])->findOrFail($kartuId),
            default     => \App\Models\KartuP3kPemeriksaan::with(['user', 'approver', 'signature'])->findOrFail($kartuId),
        };

        $template = \App\Models\KartuTemplate::getTemplate('p3k-' . $jenis)
            ?? \App\Models\KartuTemplate::getTemplate('p3k');

        return view('p3k.view-kartu', compact('p3k', 'kartu', 'jenis', 'template'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RIWAYAT KARTU
    // ─────────────────────────────────────────────────────────────────────────

    public function riwayat(Request $request, P3k $p3k)
    {
        $this->authorizeUnit($p3k);

        $riwayatPemeriksaan = $p3k->kartuPemeriksaan()
            ->with(['user', 'approver', 'signature'])
            ->orderBy('tgl_periksa', 'desc')
            ->get()
            ->map(function ($item) {
                $item->jenis   = 'pemeriksaan';
                $item->tanggal = $item->tgl_periksa;
                return $item;
            });

        $riwayatPemakaian = $p3k->kartuPemakaian()
            ->with(['user', 'approver', 'signature'])
            ->orderBy('tgl_pemakaian', 'desc')
            ->get()
            ->map(function ($item) {
                $item->jenis   = 'pemakaian';
                $item->tanggal = $item->tgl_pemakaian;
                return $item;
            });

        $riwayatStock = $p3k->kartuStock()
            ->with(['user', 'approver', 'signature'])
            ->orderBy('tgl_periksa', 'desc')
            ->get()
            ->map(function ($item) {
                $item->jenis   = 'stock';
                $item->tanggal = $item->tgl_periksa;
                return $item;
            });

        $filterJenis = $request->query('jenis');

        if ($filterJenis === 'pemeriksaan') {
            $riwayatInspeksi = $riwayatPemeriksaan;
        } elseif ($filterJenis === 'pemakaian') {
            $riwayatInspeksi = $riwayatPemakaian;
        } elseif ($filterJenis === 'stock') {
            $riwayatInspeksi = $riwayatStock;
        } else {
            $riwayatInspeksi = $riwayatPemeriksaan
                ->concat($riwayatPemakaian)
                ->concat($riwayatStock)
                ->sortByDesc('tanggal')
                ->values();
        }

        if ($request->filled('creator')) {
            $riwayatInspeksi = $riwayatInspeksi->filter(function ($item) use ($request) {
                return $item->user && str_contains(
                    strtolower($item->user->name),
                    strtolower($request->creator)
                );
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $riwayatInspeksi = $riwayatInspeksi->filter(fn ($item) => !is_null($item->approved_at));
            } elseif ($request->status === 'pending') {
                $riwayatInspeksi = $riwayatInspeksi->filter(fn ($item) => is_null($item->approved_at));
            }
        }

        return view('p3k.riwayat', compact('p3k', 'riwayatInspeksi', 'filterJenis'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PILIH JENIS (halaman pilihan jenis kartu)
    // ─────────────────────────────────────────────────────────────────────────

    public function pilihJenis()
    {
        return view('p3k.pilih-jenis');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LIST BY JENIS – daftar P3K per jenis kartu, isolated per unit
    // ─────────────────────────────────────────────────────────────────────────

    public function listByJenis(Request $request, string $jenis)
    {
        if (!in_array($jenis, ['pemeriksaan', 'pemakaian', 'stock'])) {
            return redirect()->route('p3k.pilih-jenis');
        }

        $user = auth()->user();

        // ── Tentukan unit scope ──────────────────────────────────────────────
        if ($user && $user->unit_id) {
            // PETUGAS: unit terkunci ke unit sendiri, TIDAK bisa diubah
            $unitId = (int) $user->unit_id;
            $units  = Unit::where('id', $unitId)->get();
        } else {
            // Admin/leader: bisa pilih unit via query string
            $unitId = $request->query('unit_id') ? (int) $request->query('unit_id') : null;
            $units  = Unit::orderBy('name')->get();
        }

        // ── Query P3K ────────────────────────────────────────────────────────
        $kartuRelation = match ($jenis) {
            'pemakaian' => 'kartuPemakaian',
            'stock'     => 'kartuStock',
            default     => 'kartuPemeriksaan',
        };

        // Petugas: SELALU filter unit_id (hard filter, record NULL tidak lolos)
        // Admin tanpa unit: filter opsional via query string
        $p3kQuery = P3k::with(['unit', $kartuRelation])->orderBy('serial_no');
        if ($user && $user->unit_id) {
            $p3kQuery->where('unit_id', $unitId);
        } elseif ($unitId) {
            $p3kQuery->where('unit_id', $unitId);
        }
        $p3ks = $p3kQuery->paginate(12);

        // ── Stats: jumlah kartu per unit ─────────────────────────────────────
        $kartuModel = match ($jenis) {
            'pemeriksaan' => KartuP3kPemeriksaan::class,
            'pemakaian'   => KartuP3kPemakaian::class,
            'stock'       => KartuP3kStock::class,
        };

        $kartuQuery = fn () => $kartuModel::query()->when($unitId, fn ($q) => $q->where('unit_id', $unitId));
        if ($user && $user->unit_id) {
            $kartuQuery = fn () => $kartuModel::where('unit_id', $unitId);
        }

        $p3kCount = P3k::when($user && $user->unit_id, fn ($q) => $q->where('unit_id', $unitId))
            ->when(!($user && $user->unit_id) && $unitId, fn ($q) => $q->where('unit_id', $unitId))
            ->count();

        $stats = [
            'total_p3k'      => $p3kCount,
            'total_kartu'    => ($kartuQuery)()->count(),
            'approved_kartu' => ($kartuQuery)()->whereNotNull('approved_at')->count(),
        ];

        $selectedUnit = $unitId ? Unit::find($unitId) : null;

        return view('p3k.list-by-jenis', compact(
            'p3ks',
            'stats',
            'jenis',
            'units',
            'unitId',
            'selectedUnit'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PILIH LOKASI – untuk memilih P3K sebelum buat kartu
    // ─────────────────────────────────────────────────────────────────────────

    public function pilihLokasi(Request $request)
    {
        $jenis = $request->query('jenis', 'pemeriksaan');
        $user  = auth()->user();

        if ($user && $user->unit_id) {
            // PETUGAS: unit terkunci, tidak bisa lihat unit lain
            $unitId = (int) $user->unit_id;
            $units  = Unit::where('id', $unitId)->get();
        } else {
            $unitId = $request->query('unit_id') ? (int) $request->query('unit_id') : null;
            $units  = Unit::where('is_active', true)->orderBy('name')->get();
        }

        // Hard filter untuk petugas agar record NULL tidak lolos
        $query = P3k::with(['unit', 'kartuPemeriksaan', 'kartuPemakaian', 'kartuStock'])
            ->orderBy('serial_no');
        if ($user && $user->unit_id) {
            $query->where('unit_id', $unitId); // petugas: strict
        } elseif ($unitId) {
            $query->where('unit_id', $unitId); // admin filter opsional
        }
        $p3ks = $query->paginate(20);

        $selectedUnit = $unitId ? Unit::find($unitId) : null;

        return view('p3k.pilih-lokasi', compact('jenis', 'p3ks', 'units', 'selectedUnit'));
    }
}
