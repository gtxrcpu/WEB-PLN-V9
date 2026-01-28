<x-kartu-layout
    title="Kartu Kendali Pemakaian P3K"
    :subtitle="$template->subtitle ?? ''"
    backRoute="p3k.pilih-jenis"
    module="p3k-pemakaian"
    :next-revisi="$nextRevisi ?? null"
    :template="$template">

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

    <form method="POST" action="{{ route('p3k.kartu.store') }}">
        @csrf
        <input type="hidden" name="jenis" value="pemakaian">

        <div class="grid grid-cols-3 gap-4 mb-6 text-sm">
            <div class="flex items-center">
                <label class="font-semibold text-gray-700 mr-2 w-20">Bulan</label>
                <span class="mr-2">:</span>
                <input type="month" name="bulan" value="{{ old('bulan', now()->format('Y-m')) }}" required
                    class="flex-1 px-3 py-1 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex items-center">
                <label class="font-semibold text-gray-700 mr-2 w-20">Nomor</label>
                <span class="mr-2">:</span>
                <input type="text" name="nomor" value="{{ old('nomor') }}"
                    class="flex-1 px-3 py-1 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Nomor form">
            </div>

            <div class="flex items-center">
                <label class="font-semibold text-gray-700 mr-2 w-20">Lokasi</label>
                <span class="mr-2">:</span>
                <input type="text" name="lokasi" value="{{ old('lokasi') }}" required
                    class="flex-1 px-3 py-1 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Lokasi P3K">
            </div>
        </div>

        @php
            $itemOptions = [];
            if ($template && $template->inspection_fields) {
                foreach ($template->inspection_fields as $field) {
                    $itemOptions[] = $field['label'] ?? '';
                }
            }
            if (empty($itemOptions)) {
                $itemOptions = [
                    'Plester',
                    'Perban',
                    'Kasa Steril',
                    'Antiseptik',
                    'Sarung Tangan',
                    'Masker',
                    'Alkohol 70%',
                    'Betadine',
                    'Obat Luka',
                    'Kapas',
                ];
            }
        @endphp

        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-900 mb-3">
                {{ $template && $template->table_header ? $template->table_header : 'Catatan Pemakaian Obat/Alat P3K' }}
            </h3>
            <div class="border border-gray-400 rounded-lg overflow-hidden mb-6">
                <table class="w-full text-xs" id="usageTable">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-2 py-2 text-center font-bold text-gray-700 border-r border-gray-400 w-12">NO</th>
                            <th class="px-3 py-2 text-center font-bold text-gray-700 border-r border-gray-400">NAMA</th>
                            <th class="px-3 py-2 text-center font-bold text-gray-700 border-r border-gray-400">ITEM / BARANG</th>
                            <th class="px-2 py-2 text-center font-bold text-gray-700 border-r border-gray-400 w-20">JUMLAH</th>
                            <th class="px-3 py-2 text-center font-bold text-gray-700 border-r border-gray-400">KEPERLUAN PEMAKAIAN</th>
                            <th class="px-2 py-2 text-center font-bold text-gray-700 border-r border-gray-400 w-32">TANGGAL</th>
                            <th class="px-2 py-2 text-center font-bold text-gray-700 w-24">PARAF</th>
                        </tr>
                    </thead>
                    <tbody id="usageTableBody">
                        @for($i = 0; $i < 15; $i++)
                            <tr class="border-t border-gray-300">
                                <td class="px-2 py-2 text-center border-r border-gray-300">{{ $i + 1 }}</td>
                                <td class="px-2 py-2 border-r border-gray-300">
                                    <input type="text" name="entries[{{ $i }}][nama]"
                                        value="{{ old('entries.' . $i . '.nama') }}"
                                        class="w-full px-1 py-1 border border-gray-300 rounded text-xs"
                                        placeholder="Nama pengguna">
                                </td>
                                <td class="px-2 py-2 border-r border-gray-300">
                                    <select name="entries[{{ $i }}][item]"
                                        class="w-full px-1 py-1 border border-gray-300 rounded text-xs">
                                        <option value="">-- Pilih Item --</option>
                                        @foreach($itemOptions as $option)
                                            <option value="{{ $option }}" {{ old('entries.' . $i . '.item') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-2 py-2 text-center border-r border-gray-300">
                                    <input type="number" name="entries[{{ $i }}][jumlah]"
                                        value="{{ old('entries.' . $i . '.jumlah') }}" min="1"
                                        class="w-full px-1 py-1 border border-gray-300 rounded text-center text-xs"
                                        placeholder="Qty">
                                </td>
                                <td class="px-2 py-2 border-r border-gray-300">
                                    <input type="text" name="entries[{{ $i }}][keperluan]"
                                        value="{{ old('entries.' . $i . '.keperluan') }}"
                                        class="w-full px-1 py-1 border border-gray-300 rounded text-xs"
                                        placeholder="Keperluan">
                                </td>
                                <td class="px-2 py-2 text-center border-r border-gray-300">
                                    <input type="date" name="entries[{{ $i }}][tanggal]"
                                        value="{{ old('entries.' . $i . '.tanggal') }}"
                                        class="w-full px-1 py-1 border border-gray-300 rounded text-xs">
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <div class="h-8"></div>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t-2 border-gray-200">
            <div class="flex justify-end">
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-900 mb-16">Asman K3L & Kam/ TL K3L & Kam</p>
                    <div class="border-t-2 border-gray-400 pt-2" style="width: 250px;">
                        <p class="text-xs">xxxxxxxxxxxxxxxxx</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="no-print pt-6 mt-6 border-t border-dashed border-slate-200 flex items-center justify-between gap-3 text-sm">
            <p class="text-slate-500">Data akan disimpan dan bisa dicetak ulang dari modul P3K.</p>
            <div class="flex gap-2">
                <a href="{{ route('p3k.pilih-jenis') }}" class="px-4 py-2 rounded-lg border text-sm hover:bg-slate-50">Batal</a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium hover:from-blue-700 hover:to-indigo-700">Simpan Kartu</button>
            </div>
        </div>
    </form>

    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 10pt; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
        }
    </style>
</x-kartu-layout>
