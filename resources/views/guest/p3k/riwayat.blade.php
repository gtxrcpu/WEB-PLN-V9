<x-guest.layouts.guest>
    <x-slot name="title">Riwayat {{ ucfirst($jenis) }} P3K - Guest Access</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Back Button --}}
        <x-guest.back-button href="{{ route('guest.p3k') }}" />

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    @php
                        $jenisConfig = match($jenis) {
                            'pemeriksaan' => ['color' => 'emerald', 'label' => 'Pemeriksaan Kotak P3K', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                            'pemakaian'   => ['color' => 'blue',    'label' => 'Pemakaian Kotak P3K',   'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                            'stock'       => ['color' => 'purple',  'label' => 'Stock P3K',             'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                            default       => ['color' => 'slate',   'label' => 'Kartu P3K',             'icon' => 'M9 12h6m-6 4h6'],
                        };
                        $c = $jenisConfig['color'];
                    @endphp
                    <div class="w-9 h-9 rounded-xl bg-{{ $c }}-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-{{ $c }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $jenisConfig['icon'] }}"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $jenisConfig['label'] }}</h1>
                </div>
                <p class="text-sm text-slate-500 ml-11">
                    {{ $p3k->serial_no }}
                    @if($p3k->location_code) · {{ $p3k->location_code }} @endif
                    @if($p3k->unit) · {{ $p3k->unit->name }} @endif
                </p>
            </div>

            {{-- Tab switcher --}}
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('guest.p3k.riwayat', ['p3k' => $p3k, 'jenis' => 'pemeriksaan']) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-all
                          {{ $jenis === 'pemeriksaan' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/30' : 'bg-white border border-slate-200 text-slate-600 hover:border-emerald-400 hover:text-emerald-600' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Pemeriksaan
                </a>
                <a href="{{ route('guest.p3k.riwayat', ['p3k' => $p3k, 'jenis' => 'pemakaian']) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-all
                          {{ $jenis === 'pemakaian' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30' : 'bg-white border border-slate-200 text-slate-600 hover:border-blue-400 hover:text-blue-600' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Pemakaian
                </a>
                <a href="{{ route('guest.p3k.riwayat', ['p3k' => $p3k, 'jenis' => 'stock']) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-all
                          {{ $jenis === 'stock' ? 'bg-purple-600 text-white shadow-lg shadow-purple-500/30' : 'bg-white border border-slate-200 text-slate-600 hover:border-purple-400 hover:text-purple-600' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Stock
                </a>
            </div>
        </div>

        {{-- Equipment Info Card --}}
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-6">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Informasi Peralatan</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                    <p class="text-xs text-slate-500 mb-1">Serial Number</p>
                    <p class="text-sm font-bold text-slate-900">{{ $p3k->serial_no ?? '—' }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                    <p class="text-xs text-slate-500 mb-1">Tipe</p>
                    <p class="text-sm font-bold text-slate-900">{{ $p3k->type ?? '—' }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                    <p class="text-xs text-slate-500 mb-1">Lokasi</p>
                    <p class="text-sm font-bold text-slate-900">{{ $p3k->location_code ?? '—' }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-4 border border-slate-100">
                    <p class="text-xs text-slate-500 mb-1">Status</p>
                    @php
                        $statusBadge = match(strtolower($p3k->status ?? '')) {
                            'baik'  => 'bg-emerald-100 text-emerald-700',
                            'rusak' => 'bg-rose-100 text-rose-700',
                            default => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusBadge }}">
                        {{ strtoupper($p3k->status ?? 'N/A') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- History Table --}}
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-{{ $c }}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $jenisConfig['icon'] }}"/>
                    </svg>
                    Riwayat {{ $jenisConfig['label'] }}
                </h2>
                <span class="text-sm text-slate-500">{{ $riwayatInspeksi->count() }} kartu</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Petugas</th>
                            @if($jenis === 'pemeriksaan')
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Bulan/Tahun</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Kesimpulan</th>
                            @elseif($jenis === 'pemakaian')
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Item Digunakan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Keperluan</th>
                            @elseif($jenis === 'stock')
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Kesimpulan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Catatan</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($riwayatInspeksi as $kartu)
                            <tr class="hover:bg-slate-50 transition-colors">
                                {{-- Tanggal --}}
                                <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">
                                    @php
                                        $tgl = $jenis === 'pemakaian' ? ($kartu->tgl_pemakaian ?? null) : ($kartu->tgl_periksa ?? null);
                                    @endphp
                                    {{ $tgl ? \Carbon\Carbon::parse($tgl)->format('d M Y') : '—' }}
                                </td>

                                {{-- Petugas --}}
                                <td class="px-6 py-4 text-sm text-slate-700">
                                    {{ $kartu->petugas ?? '—' }}
                                </td>

                                @if($jenis === 'pemeriksaan')
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ $kartu->bulan_tahun ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $badge = match(strtolower($kartu->kesimpulan ?? '')) {
                                                'baik'  => 'bg-emerald-100 text-emerald-700',
                                                'tidak baik', 'rusak' => 'bg-rose-100 text-rose-700',
                                                default => 'bg-slate-100 text-slate-600',
                                            };
                                        @endphp
                                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full {{ $badge }}">
                                            {{ ucfirst($kartu->kesimpulan ?? 'N/A') }}
                                        </span>
                                    </td>

                                @elseif($jenis === 'pemakaian')
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ $kartu->item_digunakan ?? '—' }}
                                        @if($kartu->jumlah)
                                            <span class="ml-1 text-xs text-slate-400">({{ $kartu->jumlah }})</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ $kartu->keperluan ?? '—' }}
                                    </td>

                                @elseif($jenis === 'stock')
                                    <td class="px-6 py-4">
                                        @php
                                            $badge = match(strtolower($kartu->kesimpulan ?? '')) {
                                                'baik'  => 'bg-emerald-100 text-emerald-700',
                                                'tidak baik', 'rusak' => 'bg-rose-100 text-rose-700',
                                                default => 'bg-slate-100 text-slate-600',
                                            };
                                        @endphp
                                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full {{ $badge }}">
                                            {{ ucfirst($kartu->kesimpulan ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500">
                                        {{ $kartu->catatan ?? '—' }}
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center">
                                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-{{ $c }}-50 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-{{ $c }}-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $jenisConfig['icon'] }}"/>
                                        </svg>
                                    </div>
                                    <p class="font-semibold text-slate-700">Belum ada kartu {{ $jenisConfig['label'] }}</p>
                                    <p class="text-sm text-slate-400 mt-1">Riwayat akan muncul di sini setelah ada pengisian kartu</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-guest.layouts.guest>
