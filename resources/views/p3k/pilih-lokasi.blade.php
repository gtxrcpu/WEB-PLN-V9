<x-layouts.app :title="'Pilih P3K - ' . ucfirst($jenis)">
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

        {{-- Back Button --}}
        <div class="mb-2">
            <a href="{{ route('p3k.pilih-jenis') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border-2 border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition-all duration-200 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali</span>
            </a>
        </div>

        {{-- Header --}}
        @php
            $jenisConfig = match($jenis) {
                'pemeriksaan' => ['color' => 'emerald', 'label' => 'Pemeriksaan Kotak P3K', 'gradient' => 'from-emerald-500 to-green-600'],
                'pemakaian'   => ['color' => 'blue',    'label' => 'Pemakaian Kotak P3K',   'gradient' => 'from-blue-500 to-indigo-600'],
                'stock'       => ['color' => 'purple',  'label' => 'Stock P3K',             'gradient' => 'from-purple-500 to-pink-600'],
                default       => ['color' => 'slate',   'label' => 'Kartu P3K',             'gradient' => 'from-slate-500 to-slate-600'],
            };
            $c = $jenisConfig['color'];
        @endphp

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Pilih P3K</h1>
                <p class="text-sm text-slate-500 mt-1">
                    Jenis Kartu: 
                    <span class="font-semibold text-{{ $c }}-600">{{ $jenisConfig['label'] }}</span>
                </p>
            </div>
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-{{ $c }}-50 border border-{{ $c }}-200 text-{{ $c }}-700 text-sm font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                {{ $p3ks->total() }} P3K tersedia
            </span>
        </div>

        {{-- Stats Cards --}}
        @php
            $totalP3k = $p3ks->total();
            $statusBaik = $p3ks->where('status', 'baik')->count();
            $statusRusak = $p3ks->where('status', 'rusak')->count();
        @endphp
        <div class="grid grid-cols-3 gap-4">
            <div class="rounded-2xl bg-gradient-to-br from-{{ $c }}-500 to-{{ $c }}-600 p-4 shadow-lg">
                <p class="text-white/80 text-xs font-medium">Total P3K</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $totalP3k }}</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-4 shadow-lg">
                <p class="text-white/80 text-xs font-medium">Kondisi Baik</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $statusBaik }}</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-rose-500 to-rose-600 p-4 shadow-lg">
                <p class="text-white/80 text-xs font-medium">Rusak</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $statusRusak }}</p>
            </div>
        </div>

        {{-- Filter Unit & Search --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Filter Unit</label>
                <select id="unitFilter" onchange="filterByUnit()"
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-{{ $c }}-500 transition-all">
                    <option value="">Semua Unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ $selectedUnit && $selectedUnit->id == $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Pencarian</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" onkeyup="filterItems()"
                           placeholder="Cari serial number atau lokasi..."
                           class="w-full pl-12 pr-4 py-3 border border-slate-200 rounded-xl bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-{{ $c }}-500 transition-all">
                </div>
            </div>
        </div>

        {{-- Selected Unit Info --}}
        @if($selectedUnit)
            <div class="bg-{{ $c }}-50 border border-{{ $c }}-200 rounded-xl p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-{{ $c }}-500 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-{{ $c }}-600 font-medium">Menampilkan unit:</p>
                        <p class="text-sm font-bold text-{{ $c }}-900">{{ $selectedUnit->name }}</p>
                    </div>
                </div>
                <button onclick="clearUnitFilter()" class="px-3 py-1.5 bg-white border border-{{ $c }}-300 text-{{ $c }}-700 rounded-lg hover:bg-{{ $c }}-50 transition-colors text-xs font-medium">
                    Tampilkan Semua
                </button>
            </div>
        @endif

        {{-- No Results --}}
        <div id="noResults" style="display:none;" class="text-center py-12">
            <p class="text-slate-500">Tidak ada hasil pencarian</p>
        </div>

        {{-- Grid P3K --}}
        @if($p3ks->count())
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($p3ks as $p3k)
                    @php
                        $statusLower = strtolower($p3k->status ?? '');
                        $statusConfig = match($statusLower) {
                            'baik'  => ['badge' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'gradient' => 'from-emerald-500 to-teal-500'],
                            'rusak' => ['badge' => 'bg-rose-100 text-rose-700 border-rose-200',         'gradient' => 'from-rose-500 to-red-500'],
                            default => ['badge' => 'bg-slate-100 text-slate-600 border-slate-200',      'gradient' => 'from-slate-400 to-slate-500'],
                        };

                        // Count for this jenis
                        $kartuCount = match($jenis) {
                            'pemeriksaan' => $p3k->kartuPemeriksaan->count(),
                            'pemakaian'   => $p3k->kartuPemakaian->count(),
                            'stock'       => $p3k->kartuStock->count(),
                            default       => 0,
                        };
                    @endphp

                    <div data-item
                         data-serial="{{ $p3k->serial_no }}"
                         data-location="{{ $p3k->location_code }}"
                         class="group relative rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-xl hover:border-{{ $c }}-300 transition-all duration-300 overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r {{ $statusConfig['gradient'] }}"></div>

                        <div class="p-6">
                            {{-- Title & Status --}}
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $statusConfig['gradient'] }} flex items-center justify-center shadow-lg flex-shrink-0">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-base font-bold text-slate-900 truncate">{{ $p3k->serial_no }}</h3>
                                        @if($p3k->location_code)
                                            <p class="text-xs text-slate-500 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                </svg>
                                                {{ $p3k->location_code }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                @if($p3k->status)
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusConfig['badge'] }} flex-shrink-0">
                                        {{ strtoupper($p3k->status) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="grid grid-cols-2 gap-2 mb-4">
                                <div class="rounded-lg bg-slate-50 p-2.5 border border-slate-100">
                                    <p class="text-xs text-slate-400">Tipe</p>
                                    <p class="text-xs font-semibold text-slate-800 mt-0.5">{{ $p3k->type ?? '—' }}</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 p-2.5 border border-slate-100">
                                    <p class="text-xs text-slate-400">Unit</p>
                                    <p class="text-xs font-semibold text-slate-800 mt-0.5 truncate">{{ $p3k->unit->name ?? '—' }}</p>
                                </div>
                            </div>

                            {{-- QR Code --}}
                            <div class="relative mb-4">
                                <div class="flex flex-col items-center justify-center p-4 rounded-xl border-2 border-dashed border-slate-200 bg-white">
                                    <img src="{{ $p3k->qr_url }}"
                                         alt="QR {{ $p3k->serial_no }}"
                                         class="w-36 h-36 object-contain rounded-lg shadow-md ring-2 ring-white bg-white">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mt-2">Scan QR Code</p>
                                </div>
                            </div>

                            {{-- Action Button --}}
                            <a href="{{ route('p3k.kartu.create', ['jenis' => $jenis, 'p3k_id' => $p3k->id]) }}"
                               class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-gradient-to-r {{ $jenisConfig['gradient'] }} text-white text-sm font-semibold hover:opacity-90 shadow-lg transition-all duration-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Buat Kartu {{ ucfirst($jenis) }}
                                @if($kartuCount > 0)
                                    <span class="ml-1 px-2 py-0.5 bg-white/20 rounded-full text-xs font-bold">{{ $kartuCount }}</span>
                                @endif
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $p3ks->appends(['jenis' => $jenis, 'unit_id' => request('unit_id')])->links() }}
            </div>
        @else
            <div class="rounded-2xl border-2 border-dashed border-slate-300 p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-{{ $c }}-50 flex items-center justify-center">
                    <svg class="w-8 h-8 text-{{ $c }}-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-700 mb-1">Belum Ada Data P3K</h3>
                <p class="text-sm text-slate-400">Tidak ada P3K yang tersedia</p>
            </div>
        @endif

    </div>

    <script>
    function filterByUnit() {
        const unitId = document.getElementById('unitFilter').value;
        const url = new URL(window.location.href);
        if (unitId) {
            url.searchParams.set('unit_id', unitId);
        } else {
            url.searchParams.delete('unit_id');
        }
        window.location.href = url.toString();
    }

    function clearUnitFilter() {
        const url = new URL(window.location.href);
        url.searchParams.delete('unit_id');
        window.location.href = url.toString();
    }

    function filterItems() {
        const q = document.getElementById('searchInput').value.toLowerCase();
        const items = document.querySelectorAll('[data-item]');
        let visible = 0;
        items.forEach(item => {
            const match = (item.dataset.serial || '').toLowerCase().includes(q) ||
                          (item.dataset.location || '').toLowerCase().includes(q);
            item.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        document.getElementById('noResults').style.display = (visible === 0 && q.length > 0) ? 'block' : 'none';
    }
    </script>
</x-layouts.app>
