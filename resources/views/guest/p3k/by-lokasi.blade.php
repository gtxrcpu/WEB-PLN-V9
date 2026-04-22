<x-guest.layouts.guest>
    <x-slot name="title">P3K {{ $lokasi }} - Guest Access</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Back Button --}}
        <x-guest.back-button href="{{ route('guest.p3k.pilih-lokasi', ['jenis' => $jenis]) }}" />

        {{-- Header --}}
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-emerald-600 to-green-600 bg-clip-text text-transparent">
                            P3K - {{ $lokasi }}
                        </h1>
                    </div>
                    <p class="text-sm text-slate-600">
                        Jenis: <span class="font-semibold">{{ ucfirst($jenis) }} Kotak P3K</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Search Box --}}
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" 
                   id="searchInput"
                   placeholder="Cari serial number atau barcode..." 
                   class="w-full pl-12 pr-4 py-3 border border-slate-200 rounded-xl bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                   onkeyup="filterItems()">
        </div>

        {{-- No Results Message --}}
        <div id="noResults" style="display: none;" class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <p class="text-lg font-semibold text-slate-900 mb-2">Tidak ada hasil</p>
            <p class="text-sm text-slate-600">Coba kata kunci lain</p>
        </div>

        {{-- Grid P3K --}}
        @if($p3ks->count())
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($p3ks as $p3k)
                    @php
                        $serial = $p3k->serial_no ?? '—';
                        $kodePendek = $serial;
                        $statusLower = strtolower($p3k->status ?? '');
                        
                        $statusConfig = match($statusLower) {
                            'baik' => [
                                'badge' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'gradient' => 'from-emerald-500 to-teal-500',
                                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                            ],
                            'rusak' => [
                                'badge' => 'bg-rose-100 text-rose-700 border-rose-200',
                                'gradient' => 'from-rose-500 to-red-500',
                                'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
                            ],
                            default => [
                                'badge' => 'bg-slate-100 text-slate-600 border-slate-200',
                                'gradient' => 'from-slate-500 to-slate-600',
                                'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                            ],
                        };
                    @endphp

                    <div data-item 
                         data-serial="{{ $p3k->serial_no }}" 
                         data-barcode="{{ $p3k->barcode }}"
                         class="group relative rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-xl hover:border-slate-300 transition-all duration-300 overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r {{ $statusConfig['gradient'] }}"></div>
                        
                        <div class="p-6">
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $statusConfig['gradient'] }} flex items-center justify-center shadow-lg">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-lg font-bold text-slate-900 truncate">
                                                P3K {{ $kodePendek }}
                                            </h3>
                                            <p class="text-sm text-slate-600">{{ $lokasi }}</p>
                                        </div>
                                    </div>
                                </div>

                                @if($p3k->status)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold {{ $statusConfig['badge'] }} shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusConfig['icon'] }}"/>
                                        </svg>
                                        {{ strtoupper($p3k->status) }}
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-3 mb-5">
                                <div class="rounded-xl bg-slate-50 p-3 border border-slate-100">
                                    <p class="text-xs text-slate-500 mb-1">Tipe</p>
                                    <p class="text-sm font-semibold text-slate-900">{{ $p3k->type ?? '—' }}</p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-3 border border-slate-100">
                                    <p class="text-xs text-slate-500 mb-1">Kategori</p>
                                    <p class="text-sm font-semibold text-slate-900">{{ $p3k->category ?? '—' }}</p>
                                </div>
                            </div>

                            <div class="relative mb-5">
                                <div class="absolute inset-0 bg-gradient-to-br {{ $statusConfig['gradient'] }} opacity-5 rounded-2xl"></div>
                                <div class="relative flex flex-col items-center justify-center p-5 rounded-2xl border-2 border-dashed border-slate-200 bg-white">
                                    <div class="mb-3">
                                        <img src="{{ $p3k->qr_url }}"
                                             alt="QR P3K {{ $kodePendek }}"
                                             class="w-40 h-40 object-contain rounded-xl shadow-lg ring-4 ring-white bg-white">
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Scan QR Code</p>
                                        <p class="text-xs text-slate-500 mt-0.5">untuk akses cepat</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Action Button --}}
                            <div class="flex flex-col gap-2">
                                <a href="{{ route('guest.p3k.riwayat', $p3k) }}"
                                   class="group/btn inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-green-600 text-white text-sm font-semibold hover:from-emerald-700 hover:to-green-700 shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:shadow-emerald-500/40 transition-all duration-300">
                                    <svg class="w-5 h-5 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <span>Lihat Riwayat</span>
                                    @php
                                        $jumlahKartu = $p3k->kartuP3ks->count();
                                    @endphp
                                    @if($jumlahKartu > 0)
                                        <span class="ml-1 px-2 py-0.5 bg-white/20 rounded-full text-xs font-bold">
                                            {{ $jumlahKartu }}
                                        </span>
                                    @endif
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $p3ks->links() }}
            </div>
        @else
            <div class="relative rounded-2xl border-2 border-dashed border-slate-300 p-12 text-center bg-gradient-to-br from-slate-50 to-white overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/5 rounded-full -mr-32 -mt-32"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-green-500/5 rounded-full -ml-32 -mb-32"></div>
                
                <div class="relative">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center shadow-xl">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                    </div>
                    
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Belum Ada Data P3K</h3>
                    <p class="text-slate-600 text-sm">
                        Tidak ada data P3K di lokasi <strong>{{ $lokasi }}</strong>
                    </p>
                </div>
            </div>
        @endif

    </div>

    <script>
    function filterItems() {
        const searchValue = document.getElementById('searchInput').value.toLowerCase();
        const items = document.querySelectorAll('[data-item]');
        let visibleCount = 0;

        items.forEach(item => {
            const serialNo = item.getAttribute('data-serial')?.toLowerCase() || '';
            const barcode = item.getAttribute('data-barcode')?.toLowerCase() || '';
            
            const isMatch = serialNo.includes(searchValue) || barcode.includes(searchValue);
            
            if (isMatch) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        const noResults = document.getElementById('noResults');
        if (noResults) {
            noResults.style.display = visibleCount === 0 && searchValue.length > 0 ? 'block' : 'none';
        }
    }
    </script>
</x-guest.layouts.guest>
