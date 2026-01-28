<x-layouts.app :title="'Upload Video Referensi — Admin'">
    <div class="max-w-4xl mx-auto px-4 py-6">

        {{-- Header Card --}}
        <div
            class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-purple-600 to-pink-600 p-8 mb-6 shadow-2xl">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full -ml-24 -mb-24"></div>

            <div class="relative flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white">Upload Video Referensi</h1>
                        <p class="text-white/80 text-sm mt-1">Upload video tutorial untuk unit tertentu atau semua unit
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.reference-videos.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/20 backdrop-blur-sm border border-white/30 text-white text-sm font-medium hover:bg-white/30 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="bg-white rounded-3xl shadow-xl border-2 border-slate-100 overflow-hidden">

            {{-- Section Header --}}
            <div class="bg-gradient-to-r from-slate-50 to-slate-100 px-8 py-6 border-b-2 border-slate-100">
                <div class="flex items-center gap-3">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Informasi Video</h2>
                        <p class="text-sm text-slate-600">Lengkapi form di bawah untuk upload video baru</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.reference-videos.store') }}" method="POST" enctype="multipart/form-data"
                class="p-8 space-y-6">
                @csrf

                {{-- Judul Video --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                        </svg>
                        Judul Video
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        placeholder="Contoh: Tutorial Inspeksi APAR"
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                        Deskripsi
                        <span class="text-xs text-slate-500 font-normal">(opsional)</span>
                    </label>
                    <textarea name="description" rows="4" placeholder="Tambahkan deskripsi singkat tentang video ini..."
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all resize-none">{{ old('description') }}</textarea>
                </div>

                {{-- Unit Assignment --}}
                <div class="space-y-3">
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Unit
                        <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-slate-500">Pilih unit tertentu atau kosongkan untuk semua unit</p>
                    <select name="unit_id"
                        class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all @error('unit_id') border-red-500 @enderror">
                        <option value="">-- Semua Unit --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }} ({{ $unit->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('unit_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- File Upload Section --}}
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-6 border-2 border-purple-100">
                    {{-- Video File --}}
                    <div class="space-y-3 mb-6">
                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                            </svg>
                            File Video
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="file" name="video" accept="video/mp4,video/x-m4v,video/*" required
                                class="w-full px-4 py-3 border-2 border-purple-200 rounded-xl bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200 @error('video') border-red-500 @enderror">
                        </div>
                        <p class="flex items-center gap-1.5 text-xs text-slate-500">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Format: MP4, MOV, AVI, WMV. Maksimal 100MB
                        </p>
                        @error('video')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Thumbnail (Opsional) --}}
                    <div class="space-y-3">
                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Thumbnail
                            <span class="text-xs text-slate-500 font-normal">(opsional)</span>
                        </label>
                        <div class="relative">
                            <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/jpg"
                                class="w-full px-4 py-3 border-2 border-purple-200 rounded-xl bg-white focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200">
                        </div>
                        <p class="flex items-center gap-1.5 text-xs text-slate-500">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Format: JPG, PNG. Maksimal 2MB
                        </p>
                        @error('thumbnail')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-6 border-t-2 border-slate-100">
                    <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-4 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-bold hover:from-purple-700 hover:to-pink-700 shadow-lg shadow-purple-500/30 hover:shadow-xl hover:shadow-purple-500/40 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <span>Upload Video</span>
                    </button>
                    <a href="{{ route('admin.reference-videos.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-6 py-4 rounded-xl border-2 border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 hover:border-slate-300 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Batal</span>
                    </a>
                </div>
            </form>
        </div>

    </div>
</x-layouts.app>