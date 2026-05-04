<x-layouts.app :title="'Pilih Jenis Kartu P3K'">
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

        {{-- Back Button --}}
        <div class="mb-4">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border-2 border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition-all duration-200 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>

        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-emerald-600 to-green-600 bg-clip-text text-transparent">
                Pilih Jenis Kartu P3K
            </h1>
            <p class="text-sm text-slate-600 mt-2">
                Pilih jenis kartu yang ingin Anda isi
            </p>
        </div>

        {{-- Pilihan Jenis Kartu --}}
        <div class="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto">

            {{-- Pemeriksaan --}}
            <a href="{{ route('p3k.list-by-jenis', 'pemeriksaan') }}"
               class="group relative overflow-hidden rounded-2xl bg-white border-2 border-slate-200 hover:border-emerald-500 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-100 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative p-6">
                    <div class="flex justify-center mb-4">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Pemeriksaan</h3>
                    <p class="text-xs text-gray-500 text-center mb-4">Periksa kelengkapan dan kondisi isi kotak P3K</p>
                    <div class="flex items-center justify-center gap-2 text-emerald-600 font-semibold text-sm group-hover:gap-4 transition-all">
                        <span>Pilih</span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>
                </div>
            </a>

            {{-- Pemakaian --}}
            <a href="{{ route('p3k.list-by-jenis', 'pemakaian') }}"
               class="group relative overflow-hidden rounded-2xl bg-white border-2 border-slate-200 hover:border-blue-500 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-100 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative p-6">
                    <div class="flex justify-center mb-4">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Pemakaian</h3>
                    <p class="text-xs text-gray-500 text-center mb-4">Catat penggunaan obat dan alat dari kotak P3K</p>
                    <div class="flex items-center justify-center gap-2 text-blue-600 font-semibold text-sm group-hover:gap-4 transition-all">
                        <span>Pilih</span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>
                </div>
            </a>

            {{-- Stock --}}
            <a href="{{ route('p3k.list-by-jenis', 'stock') }}"
               class="group relative overflow-hidden rounded-2xl bg-white border-2 border-slate-200 hover:border-purple-500 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="absolute top-0 right-0 w-32 h-32 bg-purple-100 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative p-6">
                    <div class="flex justify-center mb-4">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Stock</h3>
                    <p class="text-xs text-gray-500 text-center mb-4">Kartu kendali stock obat dan alat P3K</p>
                    <div class="flex items-center justify-center gap-2 text-purple-600 font-semibold text-sm group-hover:gap-4 transition-all">
                        <span>Pilih</span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </div>
                </div>
            </a>

        </div>
    </div>
</x-layouts.app>
