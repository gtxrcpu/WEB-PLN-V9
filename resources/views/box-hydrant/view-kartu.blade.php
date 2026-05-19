<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Kendali Box Hydrant - {{ $boxHydrant->barcode ?? $boxHydrant->serial_no }}</title>
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
            }
        }
        @page {
            margin: 0;
        }
        @media print {
            body {
                margin: 10mm;
            }
        }
    </style>
</head>
<body class="bg-slate-50">

{{-- HEADER (NO PRINT) --}}
<div class="no-print bg-white border-b shadow-sm sticky top-0 z-10">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-gray-900">Kartu Kendali Box Hydrant</h1>
            <p class="text-sm text-gray-600">{{ $boxHydrant->barcode ?? $boxHydrant->serial_no }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('box-hydrant.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50 text-sm font-medium">
                ← Kembali
            </a>
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                🖨️ Cetak
            </button>
        </div>
    </div>
</div>

{{-- APPROVAL STATUS (NO PRINT) --}}
<div class="no-print max-w-5xl mx-auto px-4 pt-4">
    <div class="p-4 rounded-lg border
        @if($kartu->isApproved()) bg-green-50 border-green-200
        @elseif($kartu->rejected_at) bg-red-50 border-red-200
        @else bg-yellow-50 border-yellow-200
        @endif">
        <div class="flex items-center gap-3">
            @if($kartu->isApproved())
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <div>
                    <p class="font-semibold text-green-800">Kartu Telah Disetujui</p>
                    <p class="text-sm text-green-700">
                        Disetujui oleh {{ get_user_display_name($kartu->approver, 'User') }}
                        pada {{ $kartu->approved_at->format('d M Y, H:i') }} WIB
                    </p>
                </div>
            @elseif($kartu->rejected_at)
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <div>
                    <p class="font-semibold text-red-800">Kartu Ditolak / Perlu Revisi</p>
                    @if($kartu->rejection_reason)
                        <p class="text-sm text-red-700 mt-1">Alasan: {{ $kartu->rejection_reason }}</p>
                    @endif
                </div>
            @else
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-semibold text-yellow-800">Menunggu Approval</p>
                    <p class="text-sm text-yellow-700">Kartu belum disetujui</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- CONTENT --}}
<div class="max-w-5xl mx-auto px-4 py-6">
    <div class="sheet-a4 bg-white rounded-xl shadow-lg border p-8">
        
        {{-- HEADER KARTU - FROM TEMPLATE --}}
        @if($template)
        {{-- Company Header with Logos --}}
        <div class="border-2 border-gray-800 mb-6">
            <div class="flex items-center justify-between p-4 border-b-2 border-gray-800">
                {{-- Logo PLN Kiri --}}
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logoo.png') }}" alt="PLN Logo" class="h-16 w-auto object-contain">
                    <div class="text-left">
                        @php
                            $resolvedAddr = $template->resolved_address ?? $template->company_address;
                            $isMultiLine = $resolvedAddr && strpos($resolvedAddr, "\n") !== false;
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
                
                {{-- 5 Logo Sertifikasi Kanan --}}
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo1.png') }}" alt="Cert 1" class="h-12 w-auto object-contain">
                    <img src="{{ asset('images/logo2.png') }}" alt="Cert 2" class="h-12 w-auto object-contain">
                    <img src="{{ asset('images/logo3.jpg') }}" alt="Cert 3" class="h-12 w-auto object-contain">
                    <img src="{{ asset('images/logo4.png') }}" alt="Cert 4" class="h-12 w-auto object-contain">
                    <img src="{{ asset('images/logo5.png') }}" alt="Cert 5" class="h-12 w-auto object-contain">
                </div>
            </div>
            
            {{-- Title & Document Info --}}
            <table class="w-full text-sm">
                <tr>
                    <td rowspan="{{ count($template->header_fields) }}" class="border-r-2 border-gray-800 p-4 text-center align-middle w-2/3">
                        <div class="font-bold text-2xl">{{ $template->title }}</div>
                        <div class="font-semibold text-lg mt-2">{{ $template->subtitle }}</div>
                        <div class="font-semibold text-base">TAHUN {{ \Carbon\Carbon::parse($kartu->tgl_periksa)->format('Y') }}</div>
                    </td>
                    @php
                        $headerFields = $template->header_fields;
                        foreach ($headerFields as &$field) {
                            if (isset($field['label']) && strtolower($field['label']) === 'revisi') {
                                $field['value'] = str_pad((string) ($kartu->revisi ?? '0'), 2, '0', STR_PAD_LEFT);
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
                            <td class="border-r @if($index < count($template->header_fields) - 1) border-b @endif border-gray-800 p-2 font-semibold bg-gray-100">{{ $field['label'] }}</td>
                            <td class="@if($index < count($template->header_fields) - 1) border-b @endif border-gray-800 p-2">{{ $field['value'] }}</td>
                        </tr>
                    @endif
                @endforeach
            </table>
        </div>
        @else
        {{-- FALLBACK HEADER --}}
        <div class="flex items-start justify-between mb-6 pb-4 border-b-2 border-gray-200">
            <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">KARTU KENDALI BOX HYDRANT</h2>
                <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    <div>
                        <span class="text-gray-600">Kode/Barcode:</span>
                        <span class="font-semibold ml-2">{{ $boxHydrant->barcode ?? $boxHydrant->serial_no }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Lokasi:</span>
                        <span class="font-semibold ml-2">{{ $boxHydrant->location_code ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- INFO BOX HYDRANT (format tabel formal) --}}
        <table class="w-full text-sm border-collapse border border-gray-800 mb-0">
            <tr>
                <td class="border border-gray-800 px-3 py-2 font-bold w-28">LOKASI</td>
                <td class="border border-gray-800 px-3 py-2">: {{ $boxHydrant->location_code ?? '-' }}</td>
                <td class="border border-gray-800 px-3 py-2 font-bold w-28">TIPE</td>
                <td class="border border-gray-800 px-3 py-2">: {{ $boxHydrant->type ?? '-' }}</td>
            </tr>
        </table>

        {{-- TABEL PEMERIKSAAN --}}
        <table class="w-full text-sm border-collapse border border-gray-800 mb-0">
            <thead>
                <tr>
                    <th class="border border-gray-800 px-4 py-3 text-center font-bold w-1/2">PEMERIKSAAN</th>
                    <th class="border border-gray-800 px-4 py-3 text-center font-bold">KONDISI</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $inspectionFields = [
                        'pilar_hydrant' => 'Pilar Hydrant',
                        'box_hydrant' => 'Box Hydrant',
                        'nozzle' => 'Nozzle',
                        'selang' => 'Selang',
                        'uji_fungsi' => 'Uji Fungsi',
                    ];
                @endphp
                @foreach($inspectionFields as $field => $label)
                    @php
                        $val = $kartu->$field ?? null;
                        $vLow = strtolower($val ?? '');
                        $isBaik = in_array($vLow, ['baik']);
                        $isTidakBaik = in_array($vLow, ['tidak_baik', 'tidak baik', 'rusak']);
                    @endphp
                    <tr>
                        <td class="border border-gray-800 px-4 py-2 text-center">{{ $label }}</td>
                        <td class="border border-gray-800 px-4 py-2 text-center">
                            <span class="inline-flex items-center gap-6 justify-center">
                                <span class="inline-flex items-center gap-1">
                                    @if($isBaik)
                                        <span class="text-base">☑</span>
                                    @else
                                        <span class="text-base">☐</span>
                                    @endif
                                    <span>Baik</span>
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    @if($isTidakBaik)
                                        <span class="text-base">☑</span>
                                    @else
                                        <span class="text-base">☐</span>
                                    @endif
                                    <span>Tidak Baik</span>
                                </span>
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- TABEL KESIMPULAN & INFO --}}
        <table class="w-full text-sm border-collapse border border-gray-800 mb-0">
            @php
                $kLow = strtolower($kartu->kesimpulan ?? '');
                $isBaikKesimpulan = $kLow === 'baik';
                $isTidakBaikKesimpulan = in_array($kLow, ['tidak baik', 'tidak_baik', 'rusak']);
            @endphp
            <tr>
                <td class="border border-gray-800 px-4 py-2 text-center font-bold w-1/2">KESIMPULAN</td>
                <td class="border border-gray-800 px-4 py-2 text-center">
                    <span class="inline-flex items-center gap-6 justify-center">
                        <span class="inline-flex items-center gap-1">
                            @if($isBaikKesimpulan)
                                <span class="text-base">☑</span>
                            @else
                                <span class="text-base">☐</span>
                            @endif
                            <span>Baik</span>
                        </span>
                        <span class="inline-flex items-center gap-1">
                            @if($isTidakBaikKesimpulan)
                                <span class="text-base">☑</span>
                            @else
                                <span class="text-base">☐</span>
                            @endif
                            <span>Tidak Baik</span>
                        </span>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="border border-gray-800 px-4 py-2 text-center">Tanggal Pemeriksaan</td>
                <td class="border border-gray-800 px-4 py-2 text-center">{{ \Carbon\Carbon::parse($kartu->tgl_periksa)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="border border-gray-800 px-4 py-2 text-center">Petugas</td>
                <td class="border border-gray-800 px-4 py-2 text-center">{{ $kartu->petugas }}</td>
            </tr>
            <tr>
                <td class="border border-gray-800 px-4 py-2 text-center">Pengawas</td>
                <td class="border border-gray-800 px-4 py-2 text-center">{{ $kartu->pengawas ?? '-' }}</td>
            </tr>
        </table>

        {{-- FOOTER: Catatan kiri + TTD kanan --}}
        <div class="mt-6 flex justify-between items-start">
            {{-- Catatan kiri --}}
            <div class="text-sm text-gray-800">
                <p class="font-semibold">Catatan : Bila ada penyimpangan segera dilaporkan</p>
                <p class="font-semibold">ke Team Leader K3L KAM</p>
            </div>

            {{-- TTD kanan --}}
            <div class="text-center">
                @php
                    $lokasi = 'Surabaya';
                    $labelPimpinan = 'Team Leader K3L & Kam';
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

                    $displaySignature = null;
                    if ($kartu->signature && $kartu->signature->signature_path) {
                        $displaySignature = $kartu->signature;
                    }
                @endphp
                <p class="text-sm">{{ $lokasi }}, {{ \Carbon\Carbon::parse($kartu->tgl_periksa)->format('d F Y') }}</p>
                <p class="text-sm font-bold mt-1">{{ $labelPimpinan }}</p>

                @if($displaySignature)
                    <div class="h-20 flex items-center justify-center my-2">
                        <img src="{{ asset('storage/' . $displaySignature->signature_path) }}" 
                             alt="Tanda Tangan" 
                             class="max-h-16 w-auto">
                    </div>
                    <div class="border-t border-gray-800 pt-1 mx-auto w-48">
                        <p class="text-sm font-bold">{{ $displaySignature->name }}</p>
                        @if($displaySignature->nip)
                            <p class="text-xs text-gray-600">NIP: {{ $displaySignature->nip }}</p>
                        @endif
                    </div>
                @else
                    <div class="h-20 my-2"></div>
                    <div class="border-t border-gray-800 pt-1 mx-auto w-48">
                        <p class="text-sm text-gray-500">(Tanda Tangan & Nama)</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

</body>
</html>
