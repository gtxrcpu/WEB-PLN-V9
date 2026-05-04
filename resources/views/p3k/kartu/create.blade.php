<x-kartu-layout 
    :title="'Kartu ' . ucfirst($jenis) . ' P3K'" 
    :subtitle="$lokasi ?? ''"
    back-route="p3k.list-by-jenis"
    :back-params="['jenis' => $jenis]"
    module="p3k-{{ $jenis }}"
    :next-revisi="$nextRevisi ?? null"
    :template="$template">

    {{-- INFO LOKASI & JENIS --}}
    <div class="mb-6 p-4 bg-slate-50 rounded-lg border border-slate-200">
        <h3 class="font-bold text-gray-900 mb-3">Informasi</h3>
        <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
            <div>
                <span class="text-gray-600">Jenis Kartu:</span>
                <span class="font-semibold ml-2">{{ ucfirst($jenis) }} P3K</span>
            </div>
            <div>
                <span class="text-gray-600">Lokasi:</span>
                <span class="font-semibold ml-2">{{ $lokasi }}</span>
            </div>
        </div>
    </div>

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
        <input type="hidden" name="jenis" value="{{ $jenis }}">
        <input type="hidden" name="lokasi" value="{{ $lokasi }}">

        {{-- PILIH P3K --}}
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Kotak P3K</label>
            <select name="p3k_id" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">-- Pilih Kotak P3K --</option>
                @foreach(\App\Models\P3k::orderBy('name')->get() as $p3k)
                    <option value="{{ $p3k->id }}" {{ old('p3k_id') == $p3k->id ? 'selected' : '' }}>
                        {{ $p3k->serial_no }} - {{ $p3k->name }} ({{ $p3k->location_code }})
                    </option>
                @endforeach
            </select>
            @error('p3k_id')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        @if($jenis === 'stock')
            {{-- TABEL STOCK P3K --}}
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3">
                    {{ $template && $template->table_header ? $template->table_header : 'Pemeriksaan Stock P3K' }}
                </h3>
                <div class="border border-gray-300 rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-3 text-center font-semibold text-gray-700 border-r border-gray-300 w-12">NO</th>
                                <th class="px-3 py-3 text-left font-semibold text-gray-700 border-r border-gray-300">Item P3K</th>
                                <th class="px-3 py-3 text-center font-semibold text-gray-700 w-48">Kondisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @php
                                $items = [];
                                if ($template && $template->inspection_fields) {
                                    foreach ($template->inspection_fields as $field) {
                                        $items[] = $field['label'] ?? '';
                                    }
                                }
                                if (empty($items)) {
                                    $items = [
                                        'Kotak P3K',
                                        'Plester',
                                        'Perban',
                                        'Kasa Steril',
                                        'Antiseptik',
                                        'Gunting',
                                        'Sarung Tangan',
                                        'Masker',
                                        'Alkohol 70%',
                                        'Betadine',
                                    ];
                                }
                            @endphp
                            @foreach($items as $index => $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3 text-center border-r border-gray-200">{{ $index + 1 }}</td>
                                <td class="px-3 py-3 font-medium text-gray-900 border-r border-gray-200">{{ $item }}</td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center justify-center gap-4">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="stock_items[{{ $index }}][kondisi]" value="baik"
                                                   class="w-4 h-4 border-gray-300 text-green-600 focus:ring-green-500"
                                                   {{ old('stock_items.'.$index.'.kondisi') === 'baik' ? 'checked' : '' }}>
                                            <span class="ml-2 text-gray-700">Baik</span>
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="stock_items[{{ $index }}][kondisi]" value="tidak_baik"
                                                   class="w-4 h-4 border-gray-300 text-red-600 focus:ring-red-500"
                                                   {{ old('stock_items.'.$index.'.kondisi') === 'tidak_baik' ? 'checked' : '' }}>
                                            <span class="ml-2 text-gray-700">Tidak Baik</span>
                                        </label>
                                    </div>
                                    <input type="hidden" name="stock_items[{{ $index }}][item]" value="{{ $item }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @elseif($jenis === 'pemeriksaan')
            {{-- FORM PEMERIKSAAN --}}
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3">
                    {{ $template && $template->table_header ? $template->table_header : 'Checklist Pemeriksaan' }}
                </h3>
                <div class="border border-gray-300 rounded-lg overflow-hidden">
                    <div class="p-4 space-y-3">
                        @php
                            $checkItems = [];
                            if ($template && $template->inspection_fields) {
                                foreach ($template->inspection_fields as $field) {
                                    $checkItems[] = $field['label'] ?? '';
                                }
                            }
                            if (empty($checkItems)) {
                                $checkItems = [
                                    'Kotak P3K dalam kondisi baik dan bersih',
                                    'Semua item tersedia lengkap',
                                    'Tidak ada item yang kadaluarsa',
                                    'Obat-obatan tersimpan dengan baik',
                                    'Label dan instruksi terbaca jelas',
                                ];
                            }
                        @endphp
                        @foreach($checkItems as $index => $checkItem)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                            <input type="checkbox" name="checklist[{{ $index }}][checked]" value="1"
                                   class="w-5 h-5 border-gray-300 text-green-600 focus:ring-green-500 rounded"
                                   {{ old('checklist.'.$index.'.checked') ? 'checked' : '' }}>
                            <label class="text-sm text-gray-700">{{ $checkItem }}</label>
                            <input type="hidden" name="checklist[{{ $index }}][item]" value="{{ $checkItem }}">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        @else
            {{-- FORM PEMAKAIAN --}}
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3">
                    {{ $template && $template->table_header ? $template->table_header : 'Detail Pemakaian' }}
                </h3>
                <div class="space-y-4">
                    @php
                        $itemOptions = [];
                        if ($template && $template->inspection_fields) {
                            foreach ($template->inspection_fields as $field) {
                                if (!empty($field['label'])) {
                                    $itemOptions[] = $field['label'];
                                }
                            }
                        }
                    @endphp

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Item yang Digunakan</label>
                        @if(!empty($itemOptions))
                            <select name="item_digunakan" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">-- Pilih Item --</option>
                                @foreach($itemOptions as $opt)
                                    <option value="{{ $opt }}" {{ old('item_digunakan') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                                <option value="lainnya" {{ old('item_digunakan') === 'lainnya' ? 'selected' : '' }}>Lainnya...</option>
                            </select>
                            <input type="text" name="item_digunakan_lainnya" value="{{ old('item_digunakan_lainnya') }}"
                                   class="mt-2 w-full px-4 py-2 border border-gray-300 rounded-lg text-sm hidden"
                                   placeholder="Masukkan nama item lainnya" id="item_lainnya_input">
                        @else
                            <input type="text" name="item_digunakan" value="{{ old('item_digunakan') }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm"
                                   placeholder="Contoh: Plester, Perban">
                        @endif
                        @error('item_digunakan')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah</label>
                        <input type="number" name="jumlah" value="{{ old('jumlah', 1) }}" required min="1"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm"
                               placeholder="Jumlah item yang digunakan">
                        @error('jumlah')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Pengguna</label>
                        <input type="text" name="nama_pengguna" value="{{ old('nama_pengguna') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm"
                               placeholder="Nama orang yang menggunakan">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Keperluan</label>
                        <textarea name="keperluan" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm"
                                  placeholder="Jelaskan keperluan penggunaan">{{ old('keperluan') }}</textarea>
                    </div>
                </div>
            </div>
        @endif

        {{-- KESIMPULAN & INFO --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Kesimpulan *</label>
                <select name="kesimpulan" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">-- Pilih Kesimpulan --</option>
                    <option value="baik" {{ old('kesimpulan') === 'baik' ? 'selected' : '' }}>Baik</option>
                    <option value="tidak baik" {{ old('kesimpulan') === 'tidak baik' ? 'selected' : '' }}>Tidak Baik</option>
                </select>
                @error('kesimpulan')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Tanggal {{ ucfirst($jenis) }} *
                </label>
                <input type="date"
                       name="{{ $jenis === 'pemakaian' ? 'tgl_pemakaian' : 'tgl_periksa' }}"
                       value="{{ old($jenis === 'pemakaian' ? 'tgl_pemakaian' : 'tgl_periksa', now()->toDateString()) }}"
                       required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('tgl_periksa')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
                @error('tgl_pemakaian')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Petugas *</label>
                <input type="text" name="petugas" required
                       value="{{ old('petugas', auth()->user()->name ?? '') }}"
                       placeholder="Nama Petugas"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('petugas')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                <textarea name="catatan" rows="2"
                          placeholder="Catatan tambahan (opsional)"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('catatan') }}</textarea>
            </div>
        </div>

        {{-- TTD SECTION - USING TEMPLATE --}}
        <div class="mt-8 pt-6 border-t-2 border-gray-200">
            <div class="flex justify-end">
                <div class="text-center">
                    @php
                        $lokasi_ttd = 'Surabaya';
                        $labelPimpinan = 'Team Leader K3L & KAM';
                        if ($template && $template->footer_fields) {
                            $lokasiField = collect($template->footer_fields)->firstWhere('label', 'Lokasi');
                            if ($lokasiField && isset($lokasiField['value'])) {
                                $lokasi_ttd = $lokasiField['value'];
                            }
                            $pimpinanField = collect($template->footer_fields)->firstWhere('label', 'Label Pimpinan');
                            if ($pimpinanField && isset($pimpinanField['value'])) {
                                $labelPimpinan = $pimpinanField['value'];
                            }
                        }
                    @endphp
                    <p class="text-sm text-gray-600 mb-1">{{ $lokasi_ttd }}, {{ now()->format('d-m-Y') }}</p>
                    <p class="text-sm font-semibold text-gray-900 mb-16">{{ $labelPimpinan }}</p>
                    <div class="border-t-2 border-gray-400 pt-2 w-56">
                        <p class="text-sm text-gray-600">(Tanda Tangan & Nama)</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- BUTTONS --}}
        <div class="no-print mt-8 pt-6 border-t border-gray-200 flex items-center justify-between">
            <p class="text-sm text-gray-600">
                <span class="text-red-600">*</span> Wajib diisi
            </p>
            <div class="flex gap-3">
                <a href="{{ route('p3k.list-by-jenis', $jenis) }}" 
                    class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                    Batal
                </a>
                <button type="submit" 
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold shadow-md">
                    Simpan Kartu Kendali
                </button>
            </div>
        </div>
    </form>

    @if($jenis === 'pemakaian' && !empty($itemOptions))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.querySelector('select[name="item_digunakan"]');
            const inputLainnya = document.getElementById('item_lainnya_input');
            
            if (select && inputLainnya) {
                select.addEventListener('change', function() {
                    if (this.value === 'lainnya') {
                        inputLainnya.classList.remove('hidden');
                        inputLainnya.required = true;
                    } else {
                        inputLainnya.classList.add('hidden');
                        inputLainnya.required = false;
                    }
                });
                
                if (select.value === 'lainnya') {
                    inputLainnya.classList.remove('hidden');
                    inputLainnya.required = true;
                }
            }
        });
    </script>
    @endif
</x-kartu-layout>
