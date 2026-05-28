<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu P3K - {{ $p3k->serial_no }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
/* ── PRINT: Remove browser header/footer (URL, date, page number) ── */
@page {
    size: A4;
    margin: 0;
}

@media print {
    /* Hide non-printable elements */
    .no-print { display: none !important; }

    /* Clean background + add margin via body padding */
    body {
        margin: 0 !important;
        padding: 15mm !important;
        background: #fff !important;
        
        
    }
    html { background: #fff !important; }

    /* Remove card shadows/borders for print */
    .sheet-a4 {
        box-shadow: none !important;
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* TABLE FIT: Force tables to fit A4 width, no overflow */
    table {
        width: 100% !important;
        table-layout: fixed !important;
        overflow: visible !important;
        page-break-inside: auto;
        font-size: 9pt !important;
    }

    /* Prevent table wrapper from scrolling */
    .overflow-x-auto,
    [style*="overflow-x"] {
        overflow: visible !important;
        overflow-x: visible !important;
    }

    /* Table cells wrap text instead of overflowing */
    th, td {
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        white-space: normal !important;
        font-size: 9pt !important;
    }

    tr { page-break-inside: avoid; page-break-after: auto; }

    /* Clean form inputs for print */
    input, select, textarea {
        border: none !important;
        outline: none !important;
        background: transparent !important;
        padding: 0 !important;
        -webkit-appearance: none !important;
    }

    /* Ensure full width */
    .max-w-5xl, .max-w-4xl, .max-w-3xl, .max-w-7xl {
        max-width: 100% !important;
    }

    /* Hide info & approval sections — only print the kartu content */
    .print-hide { display: none !important; }
}
</style>
</head>
<body class="bg-slate-50">

{{-- HEADER (NO PRINT) --}}
<div class="no-print bg-white border-b shadow-sm sticky top-0 z-10">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-gray-900">Kartu {{ ucfirst($jenis) }} P3K</h1>
            <p class="text-sm text-gray-600">{{ $p3k->serial_no }} &mdash; {{ $p3k->location_code ?? '-' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('p3k.riwayat', ['p3k' => $p3k, 'jenis' => $jenis]) }}"
               class="px-4 py-2 border rounded-lg hover:bg-gray-50 text-sm font-medium">
                &larr; Kembali
            </a>
            <button onclick="window.print()"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                Cetak
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
                        @if($template->company_name)
                            <div class="font-bold text-sm">{{ $template->company_name }}</div>
                        @endif
                        @if($template->resolved_address ?? $template->company_address)
                            <div class="text-xs">{{ $template->resolved_address ?? $template->company_address }}</div>
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
                    <td rowspan="{{ count($template->header_fields ?? [1]) }}" class="border-r-2 border-gray-800 p-4 text-center align-middle w-2/3">
                        <div class="font-bold text-2xl">{{ $template->title }}</div>
                        <div class="font-semibold text-lg mt-2">{{ $template->subtitle }}</div>
                        <div class="font-semibold text-base">TAHUN {{ date('Y') }}</div>
                    </td>
                    @php
                        $headerFields = $template->header_fields ?? [];
                        foreach ($headerFields as &$field) {
                            if (isset($field['label']) && strtolower($field['label']) === 'revisi') {
                                $field['value'] = $kartu->revisi ?? '00';
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
                <h2 class="text-2xl font-bold text-gray-900 mb-2">KARTU {{ strtoupper($jenis) }} P3K</h2>
                <p class="text-sm text-gray-600">{{ $p3k->serial_no }} &mdash; {{ $p3k->location_code ?? '-' }}</p>
            </div>
        </div>
        @endif

        {{-- INFO P3K --}}
        <div class="print-hide mb-6 p-4 bg-slate-50 rounded-lg border border-slate-200">
            <h3 class="font-bold text-gray-900 mb-3">Informasi P3K</h3>
            <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                <div><span class="text-gray-600">Serial No:</span> <span class="font-semibold ml-2">{{ $p3k->serial_no }}</span></div>
                <div><span class="text-gray-600">Lokasi:</span> <span class="font-semibold ml-2">{{ $p3k->location_code ?? '-' }}</span></div>
                <div><span class="text-gray-600">Tipe:</span> <span class="font-semibold ml-2">{{ $p3k->type ?? '-' }}</span></div>
                <div><span class="text-gray-600">Unit:</span> <span class="font-semibold ml-2">{{ $p3k->unit->name ?? '-' }}</span></div>
            </div>
        </div>

        {{-- APPROVAL HISTORY --}}
        <div class="print-hide mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <h3 class="font-bold text-gray-900 mb-4">Riwayat Approval</h3>
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900">Dibuat oleh</p>
                        <p class="text-sm text-gray-700">{{ get_user_display_name($kartu->user, 'Unknown') }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $kartu->created_at->format('d M Y, H:i') }} WIB</p>
                    </div>
                </div>

                @if($kartu->isApproved())
                    @if($kartu->leader_approved_at)
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">Di-approve oleh Leader</p>
                            <p class="text-sm text-gray-700">{{ get_user_display_name($kartu->leaderApprover ?? null, 'Unknown') }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $kartu->leader_approved_at->format('d M Y, H:i') }} WIB</p>
                        </div>
                    </div>
                    @endif
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">Di-approve oleh Admin</p>
                            <p class="text-sm text-gray-700">{{ get_user_display_name($kartu->approver, 'Unknown') }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $kartu->approved_at->format('d M Y, H:i') }} WIB</p>
                        </div>
                    </div>
                @elseif($kartu->rejected_at || $kartu->leader_rejected_at)
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-red-600 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">Ditolak / Perlu Revisi <span class="text-xs font-normal text-red-600">(Revisi {{ $kartu->revisi }})</span></p>
                            @php $rejector = \App\Models\User::find($kartu->rejected_by ?? $kartu->leader_rejected_by); @endphp
                            <p class="text-sm text-gray-700">{{ get_user_display_name($rejector, 'Unknown') }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ ($kartu->rejected_at ?? $kartu->leader_rejected_at)->format('d M Y, H:i') }} WIB</p>
                            @if($kartu->rejection_reason ?? $kartu->leader_rejection_reason)
                                <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <p class="text-xs font-semibold text-red-800 mb-1">Alasan:</p>
                                    <p class="text-sm text-red-700">{{ $kartu->rejection_reason ?? $kartu->leader_rejection_reason }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif($kartu->leader_approved_at)
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">Di-approve oleh Leader</p>
                            <p class="text-sm text-yellow-700 font-medium mt-1">Menunggu approval Admin</p>
                        </div>
                    </div>
                @else
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">Status</p>
                            <p class="text-sm text-yellow-700 font-medium mt-1">Menunggu approval Leader</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- RINCIAN KARTU --}}
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-3">Rincian {{ ucfirst($jenis) }}</h3>

            @if($jenis === 'pemeriksaan' && !empty($kartu->inspection_items))
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-3 py-2 text-center font-bold w-10">NO</th>
                                <th class="border border-gray-300 px-3 py-2 text-left font-bold">NAMA BARANG</th>
                                <th class="border border-gray-300 px-3 py-2 text-center font-bold w-20">SATUAN</th>
                                <th class="border border-gray-300 px-3 py-2 text-center font-bold w-20">JUMLAH</th>
                                <th class="border border-gray-300 px-3 py-2 text-center font-bold">FISIK/VISUAL</th>
                                <th class="border border-gray-300 px-3 py-2 text-center font-bold">TGL KADALUARSA</th>
                                <th class="border border-gray-300 px-3 py-2 text-left font-bold">CATATAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kartu->inspection_items as $idx => $item)
                                <tr class="{{ $idx % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                    <td class="border border-gray-300 px-3 py-2 text-center">{{ $idx + 1 }}</td>
                                    <td class="border border-gray-300 px-3 py-2">{{ $item['nama_barang'] ?? '-' }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-center">{{ $item['satuan'] ?? '-' }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-center font-semibold">{{ $item['jumlah'] ?? '-' }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-center">
                                        @php $fv = strtolower($item['fisik_visual'] ?? ''); @endphp
                                        @if($fv === 'baik')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Baik</span>
                                        @elseif($fv && $fv !== '-')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">{{ ucfirst($item['fisik_visual']) }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="border border-gray-300 px-3 py-2 text-center whitespace-nowrap">
                                        {{ !empty($item['tgl_kadaluarsa']) ? \Carbon\Carbon::parse($item['tgl_kadaluarsa'])->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="border border-gray-300 px-3 py-2">{{ $item['catatan'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @elseif($jenis === 'pemakaian' && !empty($kartu->usage_entries))
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-3 py-2 text-center font-bold w-10">NO</th>
                                <th class="border border-gray-300 px-3 py-2 text-left font-bold">NAMA</th>
                                <th class="border border-gray-300 px-3 py-2 text-left font-bold">ITEM / BARANG</th>
                                <th class="border border-gray-300 px-3 py-2 text-center font-bold w-20">JUMLAH</th>
                                <th class="border border-gray-300 px-3 py-2 text-left font-bold">KEPERLUAN</th>
                                <th class="border border-gray-300 px-3 py-2 text-center font-bold">TANGGAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kartu->usage_entries as $idx => $entry)
                                @if(!empty($entry['nama']) || !empty($entry['item']))
                                <tr class="{{ $idx % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                    <td class="border border-gray-300 px-3 py-2 text-center">{{ $idx + 1 }}</td>
                                    <td class="border border-gray-300 px-3 py-2">{{ $entry['nama'] ?? '-' }}</td>
                                    <td class="border border-gray-300 px-3 py-2">{{ $entry['item'] ?? '-' }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-center font-semibold">{{ $entry['jumlah'] ?? '-' }}</td>
                                    <td class="border border-gray-300 px-3 py-2">{{ $entry['keperluan'] ?? '-' }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-center whitespace-nowrap">
                                        {{ !empty($entry['tanggal']) ? \Carbon\Carbon::parse($entry['tanggal'])->format('d/m/Y') : '-' }}
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @elseif($jenis === 'stock' && !empty($kartu->stock_items))
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-3 py-2 text-center font-bold w-10">NO</th>
                                <th class="border border-gray-300 px-3 py-2 text-left font-bold">NAMA ITEM</th>
                                <th class="border border-gray-300 px-3 py-2 text-center font-bold w-24">KONDISI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kartu->stock_items as $idx => $item)
                                <tr class="{{ $idx % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                    <td class="border border-gray-300 px-3 py-2 text-center">{{ $idx + 1 }}</td>
                                    <td class="border border-gray-300 px-3 py-2">{{ $item['item'] ?? $item['nama'] ?? '-' }}</td>
                                    <td class="border border-gray-300 px-3 py-2 text-center">
                                        @php $k = strtolower($item['kondisi'] ?? ''); @endphp
                                        @if($k === 'baik')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Baik</span>
                                        @elseif($k === 'tidak_baik' || $k === 'tidak baik')
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Tidak Baik</span>
                                        @else
                                            <span class="text-gray-500">{{ $item['kondisi'] ?? '-' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-400 text-sm italic">Tidak ada data rincian.</p>
            @endif
        </div>

        {{-- INFO PEMERIKSAAN --}}
        <div class="grid grid-cols-2 gap-6 mb-6 p-4 bg-gray-50 rounded-lg">
            <div>
                <p class="text-sm text-gray-600">Petugas</p>
                <p class="font-semibold text-gray-900">{{ $kartu->petugas ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Tanggal</p>
                @php
                    $tgl = $kartu->tgl_periksa ?? $kartu->tgl_pemakaian ?? null;
                @endphp
                <p class="font-semibold text-gray-900">{{ $tgl ? \Carbon\Carbon::parse($tgl)->format('d M Y') : '-' }}</p>
            </div>
            @if($jenis === 'pemeriksaan' && $kartu->unit_kerja)
            <div>
                <p class="text-sm text-gray-600">Unit Kerja</p>
                <p class="font-semibold text-gray-900">{{ $kartu->unit_kerja }}</p>
            </div>
            @endif
            @if($jenis === 'pemakaian' && $kartu->lokasi)
            <div>
                <p class="text-sm text-gray-600">Lokasi</p>
                <p class="font-semibold text-gray-900">{{ $kartu->lokasi }}</p>
            </div>
            @endif
            @if($kartu->catatan)
            <div class="col-span-2">
                <p class="text-sm text-gray-600">Catatan</p>
                <p class="font-semibold text-gray-900">{{ $kartu->catatan }}</p>
            </div>
            @endif
        </div>

        {{-- SIGNATURE --}}
        @if($kartu->signature)
        <div class="mt-8 pt-6 border-t-2 border-gray-200">
            <div class="flex justify-end">
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-1">Menyetujui,</p>
                    <div class="my-4">
                        <img src="{{ asset('storage/' . $kartu->signature->signature_path) }}"
                             alt="Tanda Tangan"
                             class="h-16 w-auto object-contain mx-auto">
                    </div>
                    <div class="border-t-2 border-gray-400 pt-2 w-48 mx-auto">
                        <p class="text-sm font-semibold text-gray-900">{{ $kartu->signature->name }}</p>
                        <p class="text-xs text-gray-600">{{ $kartu->signature->position }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>


<script>
    window.addEventListener('beforeprint', () => {
        document.title = '';
    });
    window.addEventListener('afterprint', () => {
        document.title = 'Kartu P3K';
    });
</script>
</body>

</html>
