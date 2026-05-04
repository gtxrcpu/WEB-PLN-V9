<x-layouts.app :title="'Monitoring CCTV'">
    <div class="px-4 py-6 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Real-time CCTV Monitoring</h1>
                <p class="mt-1 text-sm text-slate-500">Pantau keamanan dan operasional unit secara langsung.</p>
            </div>

            @if(auth()->user()->hasRole('superadmin'))
            <div class="flex items-center gap-3">
                <form action="{{ route('cctv.dashboard') }}" method="GET" class="flex items-center gap-2">
                    <select name="unit_id" onchange="this.form.submit()"
                        class="rounded-xl border-slate-200 bg-white text-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                        <option value="">Semua Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            @endif
        </div>

        @if($cctvs->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900">Belum Ada CCTV Terdaftar</h3>
                <p class="text-slate-500 mt-1 max-w-xs text-center">Hubungi admin untuk mendaftarkan kamera CCTV di unit Anda.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($cctvs as $cctv)
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden flex flex-col group hover:shadow-xl transition-all duration-300 h-full" 
                         x-data="{ zoom: 1, rotate: 0, isOnline: {{ $cctv->is_online ? 'true' : 'false' }} }">
                        
                        {{-- Video Container --}}
                        <div class="relative aspect-video bg-black overflow-hidden group">
                            {{-- Offline Overlay --}}
                            <div x-show="!isOnline" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-slate-900/90 text-white">
                                <svg class="w-12 h-12 text-rose-500 mb-3 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                <span class="text-sm font-semibold tracking-wider">OFFLINE</span>
                                <span class="text-xs text-slate-400 mt-1">Terakhir terlihat: {{ $cctv->last_seen_at ? $cctv->last_seen_at->diffForHumans() : 'Tidak diketahui' }}</span>
                            </div>

                            {{-- Online Status Badge --}}
                            <div class="absolute top-4 left-4 z-20 flex items-center gap-2">
                                <span class="flex h-2 w-2 rounded-full {{ $cctv->is_online ? 'bg-emerald-500 animate-ping' : 'bg-rose-500' }}"></span>
                                <span class="text-[10px] font-bold text-white bg-black/50 backdrop-blur-md px-2 py-0.5 rounded-full uppercase tracking-widest">
                                    {{ $cctv->is_online ? 'Live' : 'Offline' }}
                                </span>
                            </div>

                            {{-- Video Element --}}
                            <div class="w-full h-full flex items-center justify-center transition-all duration-500 ease-out"
                                 :style="`transform: scale(${zoom}) rotate(${rotate}deg)`">
                                @if($cctv->stream_url)
                                    <video id="video-{{ $cctv->id }}" 
                                           class="video-js vjs-default-skin vjs-big-play-centered w-full h-full object-cover"
                                           controls preload="auto" width="640" height="264"
                                           data-setup='{}'>
                                        <source src="{{ $cctv->stream_url }}" type="application/x-mpegURL">
                                        <p class="vjs-no-js">
                                            To view this video please enable JavaScript, and consider upgrading to a
                                            web browser that supports HTML5 video.
                                        </p>
                                    </video>
                                @endif
                            </div>

                            {{-- Controls Overlay --}}
                            <div class="absolute bottom-4 right-4 z-20 flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <button @click="zoom = Math.min(zoom + 0.2, 3)" 
                                        class="p-2 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white rounded-lg border border-white/20 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                    </svg>
                                </button>
                                <button @click="zoom = Math.max(zoom - 0.2, 1)" 
                                        class="p-2 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white rounded-lg border border-white/20 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                                    </svg>
                                </button>
                                <button @click="rotate = (rotate + 90) % 360" 
                                        class="p-2 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white rounded-lg border border-white/20 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="p-5 flex flex-col flex-grow">
                            <div class="flex items-start justify-between gap-4 mb-3">
                                <div>
                                    <h3 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $cctv->name }}</h3>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="text-xs text-slate-500 font-medium">{{ $cctv->location_code }} • {{ $cctv->unit->name }}</span>
                                    </div>
                                </div>
                                <div class="px-2.5 py-1 bg-slate-50 text-slate-600 text-[10px] font-bold rounded-lg uppercase tracking-tight border border-slate-100">
                                    {{ $cctv->status }}
                                </div>
                            </div>
                            
                            @if($cctv->notes)
                                <p class="text-xs text-slate-500 line-clamp-2 italic mb-4">"{{ $cctv->notes }}"</p>
                            @endif

                            <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[10px] text-slate-400 uppercase tracking-widest font-semibold">IP: {{ $cctv->ip_address ?? 'DNS' }}</span>
                                <div class="flex items-center gap-2">
                                    <button class="text-xs font-bold text-indigo-600 hover:text-indigo-700 transition-colors">Details</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Video.js Assets --}}
    @push('styles')
    <link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet" />
    <style>
        .video-js .vjs-tech { object-fit: cover; }
        .vjs-big-play-centered .vjs-big-play-button { 
            background-color: rgba(79, 70, 229, 0.9);
            border-radius: 1.5rem;
            width: 3.5rem;
            height: 3.5rem;
            line-height: 3.5rem;
            margin-top: -1.75rem;
            margin-left: -1.75rem;
            border: none;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lazy load video players if needed or other initialization
        });
    </script>
    @endpush
</x-layouts.app>
