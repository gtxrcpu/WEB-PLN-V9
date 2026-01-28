<x-kartu-layout
    title="Kartu Kendali Pemeriksaan P3K"
    :subtitle="$template->subtitle ?? ''"
    back-route="p3k.pilih-jenis"
    module="p3k-pemeriksaan"
    :next-revisi="$nextRevisi ?? null"
    :template="$template">

    {{-- ERROR MESSAGES --}}
    @if($errors->any())
        <div class="no-print mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="font-semibold text-red-800 mb-2">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM --}}
    <form method="POST" action="{{ route('p3k.kartu.store') }}">
        @csrf
        <input type="hidden" name="jenis" value="pemeriksaan">

        {{-- Header Section --}}
        <div class="grid grid-cols-2 gap-6 mb-6 text-sm">
            <div>
                <label class="block font-semibold text-gray-700 mb-2">Unit Kerja / Lokasi</label>
                <input type="text" name="unit_kerja" value="{{ old('unit_kerja') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="Contoh: Area Limbah B3">
                @error('unit_kerja')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-semibold text-gray-700 mb-2">Tanggal Pemeriksaan</label>
                <input type="date" name="tgl_periksa" value="{{ old('tgl_periksa', now()->toDateString()) }}"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500">
                @error('tgl_periksa')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-semibold text-gray-700 mb-2">Bulan dan Tahun</label>
                <input type="month" name="bulan_tahun" value="{{ old('bulan_tahun', now()->format('Y-m')) }}"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500">
                @error('bulan_tahun')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block font-semibold text-gray-700 mb-2">Petugas Pemeriksa</label>
                <input type="text" name="petugas" value="{{ old('petugas', auth()->user()->name ?? '') }}"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="Nama Petugas">
                @error('petugas')
                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Main Inspection Table --}}
        <div class="border border-gray-400 rounded-lg overflow-hidden mb-6">
            <table class="w-full text-xs">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-2 py-2 text-center font-bold text-gray-700 border-r border-gray-400 w-10">
                            NO</th>
                        <th class="px-3 py-2 text-left font-bold text-gray-700 border-r border-gray-400">NAMA
                            BARANG*)</th>
                        <th class="px-2 py-2 text-center font-bold text-gray-700 border-r border-gray-400 w-20">
                            SATUAN</th>
                        <th class="px-2 py-2 text-center font-bold text-gray-700 border-r border-gray-400 w-20">
                            JUMLAH</th>
                        <th class="px-3 py-2 text-center font-bold text-gray-700 border-r border-gray-400"
                            colspan="3">JENIS PEMERIKSAAN</th>
                        <th class="px-2 py-2 text-center font-bold text-gray-700 w-24">PARAF PETUGAS</th>
                    </tr>
                    <tr class="bg-gray-50">
                        <th class="border-t border-gray-300" colspan="4"></th>
                        <th
                            class="px-2 py-1 text-center text-[10px] font-semibold text-gray-600 border-t border-r border-gray-300">
                            FISIK / VISUAL**)</th>
                        <th
                            class="px-2 py-1 text-center text-[10px] font-semibold text-gray-600 border-t border-r border-gray-300">
                            TANGGAL KADALUARSA</th>
                        <th
                            class="px-2 py-1 text-center text-[10px] font-semibold text-gray-600 border-t border-r border-gray-300">
                            CATATAN</th>
                        <th class="border-t border-gray-300"></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $items = [];

                        if ($template && is_array($template->inspection_fields)) {
                            foreach ($template->inspection_fields as $field) {
                                $label = $field['label'] ?? null;
                                if ($label) {
                                    $items[] = [
                                        'name' => $label,
                                        'satuan' => $field['satuan'] ?? 'Bh',
                                        'jumlah' => $field['jumlah'] ?? 1,
                                    ];
                                }
                            }
                        }

                        if (empty($items)) {
                            $items = [
                                ['name' => 'Kasa Steril Terbungkus', 'satuan' => 'Bh', 'jumlah' => 20],
                                ['name' => 'Perban (lebar 5 cm)', 'satuan' => 'Bh', 'jumlah' => 2],
                                ['name' => 'Perban (lebar 10 cm)', 'satuan' => 'Bh', 'jumlah' => 2],
                                ['name' => 'Perban (lebar 1,25 cm)', 'satuan' => 'Bh', 'jumlah' => 2],
                                ['name' => 'Plester Cepat', 'satuan' => 'Bh', 'jumlah' => 10],
                                ['name' => 'Kapas (25 gram)', 'satuan' => 'Bh', 'jumlah' => 1],
                                ['name' => 'Kain segitiga/mittela', 'satuan' => 'Bh', 'jumlah' => 2],
                                ['name' => 'Gunting', 'satuan' => 'Bh', 'jumlah' => 1],
                                ['name' => 'Peniti', 'satuan' => 'Bh', 'jumlah' => 12],
                                ['name' => 'Sarung tangan sekali pakai', 'satuan' => 'Bh', 'jumlah' => 2],
                                ['name' => '(pasangan)', 'satuan' => 'Bh', 'jumlah' => 2],
                                ['name' => 'Masker', 'satuan' => 'Bh', 'jumlah' => 1],
                                ['name' => 'Pinset', 'satuan' => 'Bh', 'jumlah' => 1],
                                ['name' => 'Lampu senter', 'satuan' => 'Bh', 'jumlah' => 1],
                                ['name' => 'Gelas untuk cuci mata', 'satuan' => 'Bh', 'jumlah' => 1],
                                ['name' => 'Kantong plastik bersih', 'satuan' => 'Bh', 'jumlah' => 1],
                                ['name' => 'Aquades (100 ml lar. Saline)', 'satuan' => 'Bh', 'jumlah' => 1],
                                ['name' => 'Povidon Iodin (60 ml)', 'satuan' => 'Bh', 'jumlah' => 1],
                                ['name' => 'Alkohol 70 %', 'satuan' => 'Bh', 'jumlah' => 1],
                                ['name' => 'Buku panduan P3K di tempat kerja', 'satuan' => 'Bh', 'jumlah' => 1],
                                ['name' => 'Buku catatan Daftar isi kotak P3K', 'satuan' => 'Bh', 'jumlah' => 1],
                            ];
                        }
                    @endphp
                    @foreach($items as $index => $item)
                        <tr class="border-t border-gray-300">
                            <td class="px-2 py-2 text-center border-r border-gray-300">{{ $index + 1 }}</td>
                            <td class="px-3 py-2 border-r border-gray-300">{{ $item['name'] }}</td>
                            <td class="px-2 py-2 text-center border-r border-gray-300">{{ $item['satuan'] }}</td>
                            <td class="px-2 py-2 text-center border-r border-gray-300">{{ $item['jumlah'] }}</td>
                            <td class="px-2 py-2 text-center border-r border-gray-300">
                                <input type="text" name="items[{{ $index }}][fisik_visual]"
                                    value="{{ old('items.' . $index . '.fisik_visual') }}"
                                    class="w-full px-1 py-1 border border-gray-300 rounded text-center text-xs"
                                    placeholder="-">
                            </td>
                            <td class="px-2 py-2 text-center border-r border-gray-300">
                                <input type="date" name="items[{{ $index }}][tgl_kadaluarsa]"
                                    value="{{ old('items.' . $index . '.tgl_kadaluarsa') }}"
                                    class="w-full px-1 py-1 border border-gray-300 rounded text-xs">
                            </td>
                            <td class="px-2 py-2 text-center border-r border-gray-300">
                                <input type="text" name="items[{{ $index }}][catatan]"
                                    value="{{ old('items.' . $index . '.catatan') }}"
                                    class="w-full px-1 py-1 border border-gray-300 rounded text-xs" placeholder="-">
                            </td>
                            <td class="px-2 py-2 text-center">
                                <div class="h-8"></div>
                            </td>
                            <input type="hidden" name="items[{{ $index }}][nama_barang]"
                                value="{{ $item['name'] }}">
                            <input type="hidden" name="items[{{ $index }}][satuan]" value="{{ $item['satuan'] }}">
                            <input type="hidden" name="items[{{ $index }}][jumlah]" value="{{ $item['jumlah'] }}">
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Footer Notes --}}
        <div class="text-xs text-gray-600 mb-6 space-y-1">
            <p><strong>*)</strong> Sesuai Standar Permenakertrans No.PER.15/MEN/VIII/2008</p>
            <p class="ml-4"><strong>- "V"</strong> bila kurang dari standar.</p>
            <p class="ml-4"><strong>- "Angka"</strong> jika jurang dari standar.</p>
            <p><strong>**)</strong> Saat pemeriksaan bulanan diisi sesuai dengan standar.</p>
        </div>

        {{-- Signature Section --}}
        <div class="mt-8 pt-6 border-t-2 border-gray-200">
            <div class="grid grid-cols-3 gap-8 text-center text-sm">
                <div>
                    <p class="font-semibold text-gray-700 mb-1">Kota, Tanggal</p>
                    <p class="text-gray-600 mb-1">{{ now()->format('d-m-Y') }}</p>
                    <p class="font-semibold text-gray-900 mb-16">Assistant Manager K3L & KAM</p>
                    <div class="border-t-2 border-gray-400 pt-2 mx-auto" style="width: 200px;">
                        <p class="text-gray-600">(Tanda Tangan & Nama)</p>
                    </div>
                </div>
                <div>
                    <p class="font-semibold text-gray-700 mb-1">Kota, Tanggal</p>
                    <p class="text-gray-600 mb-1">{{ now()->format('d-m-Y') }}</p>
                    <p class="font-semibold text-gray-900 mb-16">Team Leader K3L & KAM</p>
                    <div class="border-t-2 border-gray-400 pt-2 mx-auto" style="width: 200px;">
                        <p class="text-gray-600">(Tanda Tangan & Nama)</p>
                    </div>
                </div>
                <div>
                    <p class="font-semibold text-gray-700 mb-1">Kota, Tanggal</p>
                    <p class="text-gray-600 mb-1">{{ now()->format('d-m-Y') }}</p>
                    <p class="font-semibold text-gray-900 mb-16">Petugas Pemeriksa</p>
                    <div class="border-t-2 border-gray-400 pt-2 mx-auto" style="width: 200px;">
                        <p class="text-gray-600">(Tanda Tangan & Nama)</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- FOOTER TOMBOL (HANYA LAYAR) --}}
        <div
            class="no-print pt-6 mt-6 border-t border-dashed border-slate-200 flex items-center justify-between gap-3 text-sm">
            <p class="text-slate-500">
                Data akan disimpan dan bisa dicetak ulang dari modul P3K.
            </p>
            <div class="flex gap-2">
                <a href="{{ route('p3k.pilih-jenis') }}"
                    class="px-4 py-2 rounded-lg border text-sm hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2 rounded-lg bg-gradient-to-r from-emerald-600 to-green-600 text-white text-sm font-medium hover:from-emerald-700 hover:to-green-700">
                    Simpan Kartu
                </button>
            </div>
        </div>
    </form>

    {{-- Print Styles --}}
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                font-size: 10pt;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</x-kartu-layout>