<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Pemeriksaan Box Hydrant — {{ $boxHydrant->serial_no }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .sheet-a4 {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 12mm !important;
            }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            input, select {
                border: none !important;
                outline: none !important;
                background: transparent !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50">

{{-- STICKY HEADER --}}
<div class="no-print bg-white border-b shadow-sm sticky top-0 z-10">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-gray-900">Kartu Pemeriksaan Box Hydrant</h1>
            <p class="text-sm text-gray-500">{{ $boxHydrant->serial_no }}
                @if($boxHydrant->location_code) — {{ $boxHydrant->location_code }} @endif
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('box-hydrant.index') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium text-gray-700">
                ← Kembali
            </a>
            <button onclick="window.print()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                🖨️ Cetak
            </button>
        </div>
    </div>
</div>

{{-- CONTENT --}}
<div class="max-w-5xl mx-auto px-4 py-6">
    <div class="sheet-a4 bg-white rounded-xl shadow-lg border p-8">

        {{-- HEADER KARTU --}}
        @if($template)
        <div class="border-2 border-gray-800 mb-6">
            <div class="flex items-center justify-between p-4 border-b-2 border-gray-800">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logoo.png') }}" alt="PLN Logo" class="h-16 w-auto object-contain">
                    <div class="text-left">
                        @php
                            $resolvedAddr = $template->resolved_address ?? $template->company_address;
                            $isMultiLine  = $resolvedAddr && strpos($resolvedAddr, "\n") !== false;
                        @endphp
                        @if($isMultiLine)
                            @foreach(explode("\n", $resolvedAddr) as $line)
                                <div class="text-xs {{ $loop->first ? 'font-bold text-sm' : '' }}">{{ $line }}</div>
                            @endforeach
                        @else
                            @if($template->company_name)
                                <div class="font-bold text-sm">{{ $template->company_name }}</div>
                            @endif
                            @if($resolvedAddr)
                                <div class="text-xs">{{ $resolvedAddr }}</div>
                            @endif
                            @if($template->company_phone)
                                <div class="text-xs">{{ $template->company_phone }}</div>
                            @endif
                            @if($template->company_fax)
                                <div class="text-xs">{{ $template->company_fax }}</div>
                            @endif
                            @if($template->company_email)
                                <div class="text-xs">{{ $template->company_email }}</div>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo1.png') }}" alt="Cert 1" class="h-12 w-auto object-contain">
                    <img src="{{ asset('images/logo2.png') }}" alt="Cert 2" class="h-12 w-auto object-contain">
                    <img src="{{ asset('images/logo3.jpg') }}" alt="Cert 3" class="h-12 w-auto object-contain">
                    <img src="{{ asset('images/logo4.png') }}" alt="Cert 4" class="h-12 w-auto object-contain">
                    <img src="{{ asset('images/logo5.png') }}" alt="Cert 5" class="h-12 w-auto object-contain">
                </div>
            </div>
            <table class="w-full text-sm">
                <tr>
                    <td rowspan="{{ max(1, count($template->header_fields ?? [])) }}"
                        class="border-r-2 border-gray-800 p-4 text-center align-middle w-2/3">
                        <div class="font-bold text-2xl">KARTU PEMERIKSAAN BOX HYDRANT</div>
                        <div class="font-semibold text-base mt-1">TAHUN {{ date('Y') }}</div>
                    </td>
                    @php
                        $headerFields = $template->header_fields ?? [];
                        foreach ($headerFields as &$field) {
                            if (isset($field['label']) && strtolower($field['label']) === 'revisi') {
                                $field['value'] = $nextRevisi ?? '00';
                            }
                        }
                        unset($field);
                        $firstField = $headerFields[0] ?? null;
                    @endphp
                    @if($firstField)
                        <td class="border-r border-b border-gray-800 p-2 font-semibold bg-gray-100 w-1/6">{{ $firstField['label'] }}</td>
                        <td class="border-b border-gray-800 p-2">{{ $firstField['value'] }}</td>
                    @endif
                </tr>
                @foreach($headerFields as $index => $field)
                    @if($index > 0)
                        <tr>
                            <td class="border-r @if($index < count($headerFields) - 1) border-b @endif border-gray-800 p-2 font-semibold bg-gray-100">{{ $field['label'] }}</td>
                            <td class="@if($index < count($headerFields) - 1) border-b @endif border-gray-800 p-2">{{ $field['value'] }}</td>
                        </tr>
                    @endif
                @endforeach
            </table>
        </div>
        @else
        <div class="flex items-start justify-between mb-6 pb-4 border-b-2 border-gray-200">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">KARTU PEMERIKSAAN BOX HYDRANT</h2>
                <p class="text-sm text-gray-500 mt-1">Tahun {{ date('Y') }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500">Revisi</p>
                <p class="font-bold text-lg {{ ($nextRevisi ?? '00') !== '00' ? 'text-red-700' : 'text-gray-700' }}">
                    {{ $nextRevisi ?? '00' }}
                </p>
            </div>
        </div>
        @endif

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
        <form method="POST" action="{{ route('box-hydrant.kartu-pemeriksaan.store') }}" id="pemeriksaanForm">
            @csrf
            <input type="hidden" name="box_hydrant_id" value="{{ $boxHydrant->id }}">

            {{-- TABEL PEMERIKSAAN: NO | NAMA BARANG | LOKASI | NO.SERI | KONDISI | KETERANGAN --}}
            <div class="overflow-x-auto mb-8">
                <table class="w-full text-xs border-collapse" style="min-width: 750px;">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-400 px-2 py-2.5 text-center font-bold text-gray-800 w-8">NO.</th>
                            <th class="border border-gray-400 px-2 py-2.5 text-center font-bold text-gray-800">NAMA BARANG</th>
                            <th class="border border-gray-400 px-2 py-2.5 text-center font-bold text-gray-800">LOKASI</th>
                            <th class="border border-gray-400 px-2 py-2.5 text-center font-bold text-gray-800 w-28">NO. SERI</th>
                            <th class="border border-gray-400 px-2 py-2.5 text-center font-bold text-gray-800 w-28">KONDISI</th>
                            <th class="border border-gray-400 px-2 py-2.5 text-center font-bold text-gray-800 w-32">KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 0; $i < 10; $i++)
                            <tr class="hover:bg-gray-50">
                                {{-- NO --}}
                                <td class="border border-gray-300 px-1 py-1 text-center text-gray-500">
                                    {{ $i + 1 }}
                                </td>

                                {{-- NAMA BARANG --}}
                                <td class="border border-gray-300 px-1 py-0.5">
                                    <input type="text"
                                           name="rows[{{ $i }}][nama_barang]"
                                           value="{{ old("rows.{$i}.nama_barang") }}"
                                           placeholder="Nama barang"
                                           class="w-full px-1.5 py-1 text-xs border-0 focus:ring-1 focus:ring-cyan-400 rounded bg-transparent placeholder-gray-300">
                                </td>

                                {{-- LOKASI --}}
                                <td class="border border-gray-300 px-1 py-0.5">
                                    <input type="text"
                                           name="rows[{{ $i }}][lokasi]"
                                           value="{{ old("rows.{$i}.lokasi") }}"
                                           placeholder="Lokasi"
                                           class="w-full px-1.5 py-1 text-xs border-0 focus:ring-1 focus:ring-cyan-400 rounded bg-transparent placeholder-gray-300">
                                </td>

                                {{-- NO. SERI --}}
                                <td class="border border-gray-300 px-1 py-0.5">
                                    <input type="text"
                                           name="rows[{{ $i }}][no_seri]"
                                           value="{{ old("rows.{$i}.no_seri") }}"
                                           placeholder="No. Seri"
                                           class="w-full px-1.5 py-1 text-xs border-0 focus:ring-1 focus:ring-cyan-400 rounded bg-transparent placeholder-gray-300 font-mono">
                                </td>

                                {{-- KONDISI --}}
                                <td class="border border-gray-300 px-1 py-0.5">
                                    <select name="rows[{{ $i }}][kondisi]"
                                            class="w-full px-1 py-1 text-xs border border-gray-200 rounded focus:ring-1 focus:ring-cyan-400 bg-white">
                                        <option value="">—</option>
                                        <option value="Baik"       {{ old("rows.{$i}.kondisi") === 'Baik'       ? 'selected' : '' }}>Baik</option>
                                        <option value="Tidak Baik" {{ old("rows.{$i}.kondisi") === 'Tidak Baik' ? 'selected' : '' }}>Tidak Baik</option>
                                        <option value="Rusak"      {{ old("rows.{$i}.kondisi") === 'Rusak'      ? 'selected' : '' }}>Rusak</option>
                                    </select>
                                </td>

                                {{-- KETERANGAN --}}
                                <td class="border border-gray-300 px-1 py-0.5">
                                    <input type="text"
                                           name="rows[{{ $i }}][keterangan]"
                                           value="{{ old("rows.{$i}.keterangan") }}"
                                           placeholder="Keterangan"
                                           class="w-full px-1.5 py-1 text-xs border-0 focus:ring-1 focus:ring-cyan-400 rounded bg-transparent placeholder-gray-300">
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {{-- Info Revisi --}}
            @php $nextRevisiVal = $nextRevisi ?? '00'; @endphp
            @if($nextRevisiVal !== '00')
            <div class="no-print mb-6 p-4 rounded-xl border-2 border-red-300 bg-red-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-200 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-red-800">Kartu Revisi</p>
                            <p class="text-xs text-red-600">Kartu sebelumnya ditolak, silakan perbaiki sesuai feedback</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-red-600 font-medium">Nomor Revisi</p>
                        <p class="text-2xl font-bold text-red-700">{{ $nextRevisiVal }}</p>
                    </div>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-2 gap-8 items-end mt-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Tanggal Pemeriksaan <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tgl_periksa" required
                               value="{{ old('tgl_periksa', now()->toDateString()) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 text-sm">
                        @error('tgl_periksa')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Petugas Pemeriksa <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="petugas" required
                               value="{{ old('petugas', auth()->user()->name ?? '') }}"
                               placeholder="Nama petugas"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 text-sm">
                        @error('petugas')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="text-center">
                    @php
                        $lokasi = 'Surabaya';
                        if ($template && $template->footer_fields) {
                            $lokasiField = collect($template->footer_fields)->firstWhere('label', 'Lokasi');
                            if ($lokasiField && isset($lokasiField['value'])) {
                                $lokasi = $lokasiField['value'];
                            }
                        }
                    @endphp
                    <p class="text-sm text-gray-600 mb-1">{{ $lokasi }}, {{ now()->format('d-m-Y') }}</p>
                    <p class="text-sm font-semibold text-gray-900 mb-16">Petugas Pemeriksa</p>
                    <div class="border-t-2 border-gray-400 pt-2 mx-auto w-56">
                        <p class="text-sm text-gray-600">(Tanda Tangan & Nama)</p>
                    </div>
                </div>
            </div>

            {{-- TOMBOL AKSI --}}
            <div class="no-print mt-8 pt-6 border-t border-gray-200 flex items-center justify-between gap-3">
                <p class="text-sm text-gray-500">
                    <span class="text-red-500">*</span> Wajib diisi. Baris tanpa kondisi tidak akan disimpan.
                </p>
                <div class="flex gap-3">
                    <a href="{{ route('box-hydrant.index') }}"
                       class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium text-gray-700">
                        Batal
                    </a>
                    <button type="submit" id="submitBtn"
                            class="px-6 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 text-sm font-semibold shadow-md transition-colors">
                        Simpan Kartu Pemeriksaan
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

<script>
(function () {
    var form = document.getElementById('pemeriksaanForm');
    var btn  = document.getElementById('submitBtn');
    var submitted = false;
    if (form && btn) {
        form.addEventListener('submit', function (e) {
            if (submitted) { e.preventDefault(); return false; }
            submitted = true;
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';
            btn.classList.replace('bg-cyan-600', 'bg-gray-400');
            btn.classList.add('cursor-not-allowed');
        });
    }
})();
</script>

</body>
</html>
