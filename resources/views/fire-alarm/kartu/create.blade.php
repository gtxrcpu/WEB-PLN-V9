<x-kartu-layout 
    title="Kartu Kendali Fire Alarm" 
    :subtitle="$fireAlarm->barcode ?? $fireAlarm->serial_no ?? 'Fire Alarm'"
    back-route="fire-alarm.index"
    module="fire-alarm"
    :next-revisi="$nextRevisi ?? null"
    :template="$template">

    {{-- INFO FIRE ALARM --}}
    <div class="mb-6 p-4 bg-slate-50 rounded-lg border border-slate-200">
        <h3 class="font-bold text-gray-900 mb-3">Informasi Fire Alarm</h3>
        <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
            <div>
                <span class="text-gray-600">Nama Barang:</span>
                <span class="font-semibold ml-2">{{ $fireAlarm->name ?? 'PANEL KONTROL MCFA' }}</span>
            </div>
            <div>
                <span class="text-gray-600">No. Seri:</span>
                <span class="font-semibold ml-2">{{ $fireAlarm->serial_no ?? 'FI.001' }}</span>
            </div>
            <div>
                <span class="text-gray-600">Lokasi:</span>
                <span class="font-semibold ml-2">{{ $fireAlarm->location_code ?? '-' }}</span>
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

    <form method="POST" action="{{ route('fire-alarm.kartu.store') }}">
        @csrf
        <input type="hidden" name="fire_alarm_id" value="{{ $fireAlarm->id }}">

        {{-- TABEL PEMERIKSAAN - FROM TEMPLATE --}}
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-3">Hasil Pemeriksaan</h3>
            <div class="border border-gray-300 rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 w-1/3">Komponen</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Kondisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @if($template && $template->inspection_fields)
                            @foreach($template->inspection_fields as $index => $field)
                                @php $fieldName = 'inspection_' . $index; @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $field['label'] }}</td>
                                    <td class="px-4 py-3">
                                        @if($field['type'] === 'checkbox')
                                            <div class="flex gap-4">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="radio" name="{{ $fieldName }}" value="baik" 
                                                        {{ old($fieldName) === 'baik' ? 'checked' : '' }}
                                                        class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500">
                                                    <span class="ml-2 text-gray-700">Baik</span>
                                                </label>
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="radio" name="{{ $fieldName }}" value="tidak_baik"
                                                        {{ old($fieldName) === 'tidak_baik' ? 'checked' : '' }}
                                                        class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                                                    <span class="ml-2 text-gray-700">Tidak Baik</span>
                                                </label>
                                            </div>
                                        @elseif($field['type'] === 'text')
                                            <input type="text" name="{{ $fieldName }}" value="{{ old($fieldName) }}"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        @elseif($field['type'] === 'textarea')
                                            <textarea name="{{ $fieldName }}" rows="2"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old($fieldName) }}</textarea>
                                        @endif
                                        @error($fieldName)
                                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            @foreach([
                                'panel_kontrol'     => 'Panel Kontrol',
                                'detector'          => 'Detector',
                                'manual_call_point' => 'Manual Call Point',
                                'alarm_bell'        => 'Alarm Bell',
                                'battery_backup'    => 'Battery Backup',
                                'uji_fungsi'        => 'Uji Fungsi'
                            ] as $field => $label)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $label }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-4">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="radio" name="{{ $field }}" value="baik" 
                                                    {{ old($field) === 'baik' ? 'checked' : '' }}
                                                    class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500">
                                                <span class="ml-2 text-gray-700">Baik</span>
                                            </label>
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="radio" name="{{ $field }}" value="tidak_baik"
                                                    {{ old($field) === 'tidak_baik' ? 'checked' : '' }}
                                                    class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                                                <span class="ml-2 text-gray-700">Tidak Baik</span>
                                            </label>
                                        </div>
                                        @error($field)
                                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

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
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Pemeriksaan *</label>
                <input type="date" name="tgl_periksa" required
                    value="{{ old('tgl_periksa', now()->toDateString()) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('tgl_periksa')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Petugas Pemeriksa *</label>
                <input type="text" name="petugas" required
                    value="{{ old('petugas') }}"
                    placeholder="Nama petugas"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('petugas')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor Revisi</label>
                <div class="w-full px-4 py-2 border-2 rounded-lg 
                    @if(($nextRevisi ?? '00') !== '00') border-red-300 bg-red-50 @else border-gray-300 bg-gray-50 @endif">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-lg 
                            @if(($nextRevisi ?? '00') !== '00') text-red-700 @else text-gray-700 @endif">
                            {{ $nextRevisi ?? '00' }}
                        </span>
                        @if(($nextRevisi ?? '00') !== '00')
                            <span class="text-xs font-medium text-red-600 bg-red-100 px-2 py-1 rounded">Kartu Revisi</span>
                        @else
                            <span class="text-xs text-gray-500">Kartu Baru</span>
                        @endif
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    @if(($nextRevisi ?? '00') !== '00')
                        Kartu sebelumnya ditolak, silakan perbaiki sesuai feedback leader
                    @else
                        Kartu kendali baru untuk Fire Alarm ini
                    @endif
                </p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Pengawas</label>
                <input type="text" name="pengawas"
                    value="{{ old('pengawas') }}"
                    placeholder="Nama pengawas (opsional)"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        {{-- TTD SECTION - USING TEMPLATE --}}
        <div class="mt-8 pt-6 border-t-2 border-gray-200">
            <div class="flex justify-end">
                <div class="text-center">
                    @php
                        $lokasi = 'Surabaya';
                        $labelPimpinan = 'Team Leader K3L & KAM';
                        if ($template && $template->footer_fields) {
                            $lokasiField = collect($template->footer_fields)->firstWhere('label', 'Lokasi');
                            if ($lokasiField && isset($lokasiField['value'])) {
                                $lokasi = $lokasiField['value'];
                            }
                            $pimpinanField = collect($template->footer_fields)->firstWhere('label', 'Label Pimpinan');
                            if ($pimpinanField && isset($pimpinanField['value'])) {
                                $labelPimpinan = $pimpinanField['value'];
                            }
                        }
                    @endphp
                    <p class="text-sm text-gray-600 mb-1">{{ $lokasi }}, {{ now()->format('d-m-Y') }}</p>
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
                <a href="{{ route('fire-alarm.index') }}" 
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
</x-kartu-layout>
