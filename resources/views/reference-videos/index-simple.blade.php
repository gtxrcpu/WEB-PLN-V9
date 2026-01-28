@extends('layouts.app')

@section('title', 'Video Referensi')

@section('content')
    {{-- Simple Clean Header --}}
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            {{-- Back Button --}}
            <div class="mb-4">
                <a href="{{ route('petugas.dashboard') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali</span>
                </a>
            </div>

            {{-- Title --}}
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Video Referensi</h1>
                <p class="text-sm text-slate-600 mt-1">
                    Video tutorial dan panduan untuk {{ auth()->user()->unit ? auth()->user()->unit->name : 'semua unit' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="bg-slate-50 min-h-screen py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($videos->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($videos as $video)
                        <a href="{{ route('reference-videos.show', $video->id) }}" class="group">
                            <div class="bg-white rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-200">
                                {{-- Thumbnail --}}
                                <div class="relative aspect-video bg-slate-900">
                                    @if($video->thumbnail_url)
                                        <img src="{{ $video->thumbnail_url }}" class="w-full h-full object-cover"
                                            alt="{{ $video->title }}">
                                    @else
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <svg class="w-16 h-16 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif

                                    {{-- Unit Badge --}}
                                    @if($video->unit_id)
                                        <div class="absolute bottom-2 right-2">
                                            <span class="px-2 py-1 bg-black/80 text-white text-xs font-medium rounded">
                                                {{ $video->unit->code }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="p-3">
                                    <h3 class="font-semibold text-sm text-slate-900 mb-1 line-clamp-2">
                                        {{ $video->title }}
                                    </h3>
                                    @if($video->description)
                                        <p class="text-xs text-slate-600 mb-2 line-clamp-2">
                                            {{ $video->description }}
                                        </p>
                                    @endif
                                    <p class="text-xs text-slate-500">
                                        {{ $video->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $videos->links() }}
                </div>
            @else
                {{-- Empty State --}}
                <div class="max-w-md mx-auto text-center py-12">
                    <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-slate-100 flex items-center justify-center">
                        <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">Belum Ada Video</h3>
                    <p class="text-sm text-slate-600">
                        Saat ini belum ada video referensi yang tersedia untuk unit Anda
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection