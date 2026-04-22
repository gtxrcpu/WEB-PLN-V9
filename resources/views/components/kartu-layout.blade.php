{{-- Reusable Kartu Layout Component --}}
@props(['title', 'subtitle' => '', 'template', 'module', 'backRoute', 'backParams' => [], 'nextRevisi' => null])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
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
    </style>
    
    <x-kartu-auto-refresh :module="$module" :template-version="$template ? $template->updated_at->timestamp : 0" />
</head>
<body class="bg-slate-50">

{{-- HEADER (NO PRINT) --}}
<div class="no-print bg-white border-b shadow-sm sticky top-0 z-10">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-gray-900">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-sm text-gray-600">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="flex gap-2">
            <a href="{{ route($backRoute, $backParams) }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50 text-sm font-medium">
                ← Kembali
            </a>
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                🖨️ Cetak
            </button>
        </div>
    </div>
</div>

{{-- CONTENT --}}
<div class="max-w-5xl mx-auto px-4 py-6">
    <div class="sheet-a4 bg-white rounded-xl shadow-lg border p-8">
        
        {{-- HEADER KARTU - FROM TEMPLATE --}}
        @if($template)
        <x-kartu-header :template="$template" :next-revisi="$nextRevisi" />
        @else
        {{-- FALLBACK HEADER (jika template belum dibuat) --}}
        <div class="border-2 border-gray-800 mb-6">
            <div class="flex items-center justify-between p-4 border-b-2 border-gray-800">
                {{-- Logo PLN Kiri --}}
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logoo.png') }}" alt="PLN Logo" class="h-16 w-auto object-contain">
                    <div class="text-left">
                        <div class="font-bold text-sm">PT PLN (Persero)</div>
                        <div class="text-xs">Jl. Trunojoyo No. 135, Surabaya</div>
                        <div class="text-xs">Telp: (031) 1234567</div>
                        <div class="text-xs">Fax: (031) 7654321</div>
                        <div class="text-xs">info@pln.co.id</div>
                        <p class="text-xs text-red-600 mt-1 font-semibold">⚠️ Template belum dibuat. Buat template di Kartu Kendali Settings.</p>
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
                    <td rowspan="4" class="border-r-2 border-gray-800 p-4 text-center align-middle w-2/3">
                        <div class="font-bold text-2xl">{{ $title }}</div>
                        @if($subtitle)
                        <div class="font-semibold text-lg mt-2">{{ $subtitle }}</div>
                        @endif
                        <div class="font-semibold text-base">TAHUN {{ date('Y') }}</div>
                    </td>
                    <td class="border-r border-b border-gray-800 p-2 font-semibold bg-gray-100 w-1/6">No. Dokumen</td>
                    <td class="border-b border-gray-800 p-2">-</td>
                </tr>
                <tr>
                    <td class="border-r border-b border-gray-800 p-2 font-semibold bg-gray-100">Revisi</td>
                    <td class="border-b border-gray-800 p-2">{{ $nextRevisi ?? '00' }}</td>
                </tr>
                <tr>
                    <td class="border-r border-b border-gray-800 p-2 font-semibold bg-gray-100">Tanggal</td>
                    <td class="border-b border-gray-800 p-2">{{ date('d-m-Y') }}</td>
                </tr>
                <tr>
                    <td class="border-r border-gray-800 p-2 font-semibold bg-gray-100">Halaman</td>
                    <td class="border-gray-800 p-2">1 dari 1</td>
                </tr>
            </table>
        </div>
        @endif

        {{ $slot }}

    </div>
</div>

</body>
</html>

