<x-layouts.app :title="'Leader - Video Referensi'">
    {{-- Header Section --}}
    <section class="mb-6 p-6 sm:p-8 shadow-lg rounded-xl bg-white">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('leader.dashboard') }}"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h2
                        class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">
                        Video Referensi
                    </h2>
                </div>
                <p class="text-sm text-gray-600 ml-[52px]">Panduan dan tutorial untuk membantu pekerjaan Anda</p>
            </div>

            <a href="{{ route('leader.reference-videos.create') }}"
                class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white text-sm font-semibold rounded-xl hover:from-blue-700 hover:to-cyan-700 transition-all shadow-lg hover:shadow-xl hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Upload Video</span>
            </a>
        </div>

        {{-- Stats Summary --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-4 ring-1 ring-blue-100">
                <div class="flex items-center gap-3 mb-2">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-blue-600 font-medium">Total Videos</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $videos->total() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-4 ring-1 ring-purple-100">
                <div class="flex items-center gap-3 mb-2">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-500 flex items-center justify-center shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-purple-600 font-medium">This Page</p>
                        <p class="text-2xl font-bold text-purple-900">{{ $videos->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl p-4 ring-1 ring-emerald-100">
                <div class="flex items-center gap-3 mb-2">
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-emerald-600 font-medium">Latest</p>
                        <p class="text-sm font-bold text-emerald-900">
                            {{ $videos->first()?->created_at->format('d M') ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($videos->count() > 0)
        {{-- Video Grid --}}
        <section class="mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($videos as $video)
                    <div
                        class="group bg-white rounded-xl shadow-md ring-1 ring-slate-200 hover:shadow-2xl hover:scale-105 transition-all duration-300 overflow-hidden">
                        {{-- Thumbnail --}}
                        <div class="relative aspect-video overflow-hidden bg-slate-100">
                            @if($video->thumbnail_url)
                                <img src="{{ $video->thumbnail_url }}"
                                    class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500"
                                    alt="{{ $video->title }}">
                            @else
                                <div
                                    class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-blue-100 to-cyan-100">
                                    <svg class="w-16 h-16 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif

                            {{-- Play Overlay --}}
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <div
                                    class="bg-white/95 rounded-full p-4 transform scale-90 group-hover:scale-100 transition-transform duration-300">
                                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Unit Badge --}}
                            @if($video->unit_id)
                                <div
                                    class="absolute top-3 left-3 px-3 py-1.5 bg-gradient-to-r from-blue-600 to-cyan-600 text-white text-xs font-bold rounded-lg shadow-lg">
                                    {{ $video->unit->code }}
                                </div>
                            @else
                                <div
                                    class="absolute top-3 left-3 px-3 py-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-xs font-bold rounded-lg shadow-lg">
                                    All Units
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="p-5">
                            <h3
                                class="font-bold text-gray-900 text-base mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                {{ $video->title }}
                            </h3>

                            @if($video->description)
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                    {{ $video->description }}
                                </p>
                            @endif

                            <div class="flex items-center gap-2 text-xs text-gray-500 mb-4">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ $video->created_at->diffForHumans() }}</span>
                            </div>

                            {{-- Play Button Only (Read-only for Leader) --}}
                            <a href="{{ route('reference-videos.show', $video) }}" target="_blank"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white text-sm font-semibold rounded-lg hover:from-blue-700 hover:to-cyan-700 transition-all shadow-md hover:shadow-lg">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                                Tonton Video
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($videos->hasPages())
                <div class="mt-8 flex justify-center">
                    <div class="inline-flex rounded-lg shadow-sm overflow-hidden ring-1 ring-slate-200 bg-white">
                        {{ $videos->links() }}
                    </div>
                </div>
            @endif
        </section>
    @else
        {{-- Empty State --}}
        <section class="mb-6">
            <div class="bg-white rounded-2xl p-16 text-center shadow-lg ring-1 ring-slate-200">
                <div class="max-w-md mx-auto">
                    <div
                        class="w-24 h-24 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center shadow-2xl">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>

                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Belum Ada Video</h3>
                    <p class="text-gray-600">
                        Belum ada video referensi yang tersedia saat ini. Silakan hubungi admin untuk menambahkan video
                        panduan.
                    </p>
                </div>
            </div>
        </section>
    @endif

    {{-- Custom Styles --}}
    <style>
        /* Custom Pagination Styles */
        nav[role="navigation"] {
            @apply flex items-center gap-1;
        }

        nav[role="navigation"] span,
        nav[role="navigation"] a {
            @apply inline-flex items-center justify-center min-w-[2.5rem] h-10 px-3 text-sm font-medium rounded-lg transition-all;
        }

        nav[role="navigation"] span[aria-current="page"] {
            @apply bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-md;
        }

        nav[role="navigation"] a {
            @apply bg-white text-slate-700 hover:bg-slate-50 hover:text-blue-600;
        }

        nav[role="navigation"] span[aria-disabled="true"] {
            @apply bg-slate-50 text-slate-400 cursor-not-allowed;
        }
    </style>
</x-layouts.app>