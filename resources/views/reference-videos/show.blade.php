@extends('layouts.app')

@section('title', $referenceVideo->title)

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 py-8">
        <div class="container mx-auto px-4 max-w-7xl">

            {{-- Back Button --}}
            <div class="mb-6">
                <a href="{{ route('reference-videos.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border-2 border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition-all duration-200 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali</span>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Video Area --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Video Player Card --}}
                    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border-2 border-slate-100">
                        {{-- Video Player --}}
                        <div class="relative bg-black aspect-video">
                            <video controls class="w-full h-full" controlsList="nodownload">
                                <source src="{{ $referenceVideo->video_url }}" type="video/mp4">
                                Browser Anda tidak mendukung video player.
                            </video>
                        </div>

                        {{-- Video Info --}}
                        <div class="p-6 sm:p-8">
                            {{-- Title --}}
                            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 mb-4">
                                {{ $referenceVideo->title }}
                            </h1>

                            {{-- Meta Info --}}
                            <div class="flex flex-wrap items-center gap-3 mb-6">
                                {{-- Unit Badge --}}
                                @if($referenceVideo->unit_id)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-100 text-purple-700 rounded-full text-sm font-semibold">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ $referenceVideo->unit->code }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-cyan-100 to-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                        </svg>
                                        Semua Unit
                                    </span>
                                @endif

                                {{-- Upload Info --}}
                                <span class="inline-flex items-center gap-1.5 text-sm text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ $referenceVideo->creator->name }}
                                </span>

                                <span class="inline-flex items-center gap-1.5 text-sm text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $referenceVideo->created_at->format('d M Y') }}
                                </span>
                            </div>

                            {{-- Description --}}
                            @if($referenceVideo->description)
                                <div
                                    class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl p-6 border-2 border-slate-100">
                                    <h3 class="flex items-center gap-2 text-sm font-bold text-slate-700 mb-3">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Deskripsi
                                    </h3>
                                    <p class="text-slate-700 leading-relaxed whitespace-pre-line">
                                        {{ $referenceVideo->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="lg:col-span-1">
                    {{-- Info Card --}}
                    <div class="bg-white rounded-3xl shadow-xl border-2 border-slate-100 overflow-hidden sticky top-6">
                        {{-- Header --}}
                        <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4">
                            <h3 class="flex items-center gap-2 text-white font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Informasi Video
                            </h3>
                        </div>

                        {{-- Info List --}}
                        <div class="p-6 space-y-4">
                            {{-- Unit Info --}}
                            <div class="pb-4 border-b border-slate-100">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Unit</p>
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-slate-900 truncate">
                                            @if($referenceVideo->unit_id)
                                                {{ $referenceVideo->unit->code }}
                                            @else
                                                Semua Unit
                                            @endif
                                        </p>
                                        <p class="text-sm text-slate-600 truncate">
                                            @if($referenceVideo->unit_id)
                                                {{ $referenceVideo->unit->name }}
                                            @else
                                                Tersedia untuk semua unit
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Uploader Info --}}
                            <div class="pb-4 border-b border-slate-100">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Diupload Oleh
                                </p>
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white font-bold flex-shrink-0">
                                        {{ strtoupper(substr($referenceVideo->creator->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-slate-900 truncate">{{ $referenceVideo->creator->name }}
                                        </p>
                                        <p class="text-sm text-slate-600">Administrator</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Upload Date --}}
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Tanggal Upload
                                </p>
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-slate-900">
                                            {{ $referenceVideo->created_at->format('d F Y') }}</p>
                                        <p class="text-sm text-slate-600">{{ $referenceVideo->created_at->format('H:i') }}
                                            WIB</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer Stats --}}
                        <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-6 py-4 border-t-2 border-slate-100">
                            <div class="flex items-center justify-center gap-2 text-sm text-slate-600">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-medium">{{ $referenceVideo->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection