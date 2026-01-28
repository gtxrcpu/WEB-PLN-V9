<?php

namespace App\Http\Controllers;

use App\Models\P3k;
use App\Models\KartuP3k;
use App\Models\KartuP3kPemeriksaan;
use App\Models\KartuP3kPemakaian;
use App\Models\KartuP3kStock;
use App\Models\KartuTemplate;
use Illuminate\Http\Request;

class KartuP3kController extends Controller
{
    public function create(Request $request)
    {
        $jenis = $request->query('jenis', 'stock');
        $lokasi = $request->query('lokasi', null);

        // For pemeriksaan, use dedicated view with template support
        if ($jenis === 'pemeriksaan') {
            $template = KartuTemplate::getTemplate('p3k-pemeriksaan');

            // Calculate next revisi number
            $lastUnapproved = KartuP3kPemeriksaan::whereNull('approved_at')
                ->orderBy('revisi', 'desc')
                ->first();

            $nextRevisi = $lastUnapproved
                ? str_pad((int) $lastUnapproved->revisi + 1, 2, '0', STR_PAD_LEFT)
                : '00';

            return view('p3k.kartu.kartu-pemeriksaan', compact('template', 'nextRevisi'));
        }

        // For pemakaian, use dedicated view with template support
        if ($jenis === 'pemakaian') {
            $template = KartuTemplate::getTemplate('p3k-pemakaian');

            // Calculate next revisi number
            $lastUnapproved = KartuP3kPemakaian::whereNull('approved_at')
                ->orderBy('revisi', 'desc')
                ->first();

            $nextRevisi = $lastUnapproved
                ? str_pad((int) $lastUnapproved->revisi + 1, 2, '0', STR_PAD_LEFT)
                : '00';

            return view('p3k.kartu.kartu-pemakaian', compact('template', 'nextRevisi'));
        }

        // Set default lokasi if not provided (for other types)
        if (!$lokasi) {
            $lokasi = 'Area Limbah B3';
        }

        // Get template berdasarkan jenis
        $templateModule = 'p3k-' . $jenis;
        $template = KartuTemplate::getTemplate($templateModule);

        // Fallback ke template p3k lama jika belum ada
        if (!$template) {
            $template = KartuTemplate::getTemplate('p3k');
        }

        // Get P3K berdasarkan lokasi
        $p3ks = P3k::where('location_code', 'like', '%' . $lokasi . '%')
            ->orWhere('name', 'like', '%' . $lokasi . '%')
            ->get();

        return view('p3k.kartu.create', compact('jenis', 'lokasi', 'template', 'p3ks'));
    }

    public function store(Request $request)
    {
        $jenis = $request->input('jenis', 'stock');

        // Validasi berdasarkan jenis
        if ($jenis === 'pemeriksaan') {
            return $this->storePemeriksaan($request);
        } elseif ($jenis === 'pemakaian') {
            return $this->storePemakaian($request);
        } else {
            return $this->storeStock($request);
        }
    }

    protected function storePemeriksaan(Request $request)
    {
        $validated = $request->validate([
            'unit_kerja' => ['required', 'string', 'max:255'],
            'tgl_periksa' => ['required', 'date'],
            'bulan_tahun' => ['required', 'string'],
            'petugas' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array'],
            'items.*.nama_barang' => ['required', 'string'],
            'items.*.satuan' => ['required', 'string'],
            'items.*.jumlah' => ['required'],
            'items.*.fisik_visual' => ['nullable', 'string'],
            'items.*.tgl_kadaluarsa' => ['nullable', 'date'],
            'items.*.catatan' => ['nullable', 'string'],
        ]);

        // Calculate revisi number
        $lastUnapproved = KartuP3kPemeriksaan::whereNull('approved_at')
            ->orderBy('revisi', 'desc')
            ->first();

        $revisi = $lastUnapproved
            ? str_pad((int) $lastUnapproved->revisi + 1, 2, '0', STR_PAD_LEFT)
            : '00';

        KartuP3kPemeriksaan::create([
            'user_id' => auth()->id(),
            'unit_kerja' => $validated['unit_kerja'],
            'tgl_periksa' => $validated['tgl_periksa'],
            'bulan_tahun' => $validated['bulan_tahun'],
            'petugas' => $validated['petugas'],
            'revisi' => $revisi,
            'inspection_items' => $validated['items'],
            // Leave approved_at and approved_by null for approval workflow
        ]);

        return redirect()
            ->route('p3k.pilih-jenis')
            ->with('success', 'Kartu Pemeriksaan P3K berhasil disimpan.');
    }

    protected function storePemakaian(Request $request)
    {
        $validated = $request->validate([
            'bulan' => ['required', 'string'],
            'nomor' => ['nullable', 'string', 'max:255'],
            'lokasi' => ['required', 'string', 'max:255'],
            'entries' => ['nullable', 'array'],
            'entries.*.nama' => ['nullable', 'string'],
            'entries.*.item' => ['nullable', 'string'],
            'entries.*.jumlah' => ['nullable', 'integer', 'min:1'],
            'entries.*.keperluan' => ['nullable', 'string'],
            'entries.*.tanggal' => ['nullable', 'date'],
        ]);

        // Filter out empty entries
        $entries = [];
        if (isset($validated['entries'])) {
            foreach ($validated['entries'] as $entry) {
                // Only include entry if at least name or item is filled
                if (!empty($entry['nama']) || !empty($entry['item'])) {
                    $entries[] = $entry;
                }
            }
        }

        // Calculate revisi number
        $lastUnapproved = KartuP3kPemakaian::whereNull('approved_at')
            ->orderBy('revisi', 'desc')
            ->first();

        $revisi = $lastUnapproved
            ? str_pad((int) $lastUnapproved->revisi + 1, 2, '0', STR_PAD_LEFT)
            : '00';

        KartuP3kPemakaian::create([
            'user_id' => auth()->id(),
            'bulan' => $validated['bulan'],
            'nomor' => $validated['nomor'] ?? null,
            'lokasi' => $validated['lokasi'],
            'revisi' => $revisi,
            'usage_entries' => $entries,
            // Leave approved_at and approved_by null for approval workflow
        ]);

        return redirect()
            ->route('p3k.pilih-jenis')
            ->with('success', 'Kartu Pemakaian P3K berhasil disimpan.');
    }

    protected function storeStock(Request $request)
    {
        $validated = $request->validate([
            'p3k_id' => ['required', 'exists:p3ks,id'],
            'stock_items' => ['required', 'array'],
            'kesimpulan' => ['required', 'string'],
            'tgl_periksa' => ['required', 'date'],
            'petugas' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
        ]);

        KartuP3kStock::create([
            'p3k_id' => $validated['p3k_id'],
            'user_id' => auth()->id(),
            'stock_items' => $validated['stock_items'],
            'kesimpulan' => $validated['kesimpulan'],
            'tgl_periksa' => $validated['tgl_periksa'],
            'petugas' => $validated['petugas'],
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()
            ->route('p3k.pilih-jenis')
            ->with('success', 'Kartu Stock P3K berhasil disimpan.');
    }
}

