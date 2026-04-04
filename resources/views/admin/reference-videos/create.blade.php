<x-layouts.app :title="'Admin - Upload Video Referensi'">
    {{-- Header Section --}}
    <section class="mb-6 p-6 sm:p-8 shadow-lg rounded-xl bg-white">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.reference-videos.index') }}"
                class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2
                    class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">
                    Upload Video Referensi
                </h2>
                <p class="text-sm text-gray-600 mt-1">Upload video tutorial untuk unit tertentu atau semua unit</p>
            </div>
        </div>
    </section>

    {{-- Main Form --}}
    <section class="mb-6">
        <div class="bg-white rounded-xl shadow-lg ring-1 ring-slate-200 overflow-hidden">
            <form action="{{ route('admin.reference-videos.store') }}" method="POST" enctype="multipart/form-data"
                class="p-6 sm:p-8 space-y-6">
                @csrf

                {{-- Title --}}
                <div>
                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                        Judul Video <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required
                        placeholder="Contoh: Tutorial Inspeksi APAR"
                        class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        Deskripsi <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea id="description" name="description" rows="4"
                        placeholder="Tambahkan deskripsi singkat tentang video ini..."
                        class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Unit Selector --}}
                <div>
                    <label for="unit_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        Unit
                    </label>
                    <select id="unit_id" name="unit_id"
                        class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all @error('unit_id') border-red-500 @enderror">
                        <option value="">-- Semua Unit --</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->code }} - {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Pilih unit tertentu atau kosongkan untuk semua unit</p>
                    @error('unit_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- File Upload Section --}}
                <div class="pt-6 border-t border-slate-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        File Upload
                    </h3>

                    {{-- Video File --}}
                    <div class="mb-6">
                        <label for="video" class="block text-sm font-semibold text-gray-700 mb-2">
                            File Video <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="file" id="video" name="video" accept="video/mp4,video/mov,video/avi,video/wmv"
                                required
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('video') border-red-500 @enderror">
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            <span class="font-medium">Format:</span> MP4, MOV, AVI, WMV •
                            <span class="font-medium">Maksimal:</span> 100MB
                        </p>
                        @error('video')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Thumbnail (Optional) --}}
                    <div>
                        <label for="thumbnail" class="block text-sm font-semibold text-gray-700 mb-2">
                            Thumbnail <span class="text-gray-400 font-normal">(Opsional)</span>
                        </label>
                        <div class="relative">
                            <input type="file" id="thumbnail" name="thumbnail" accept="image/*"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 @error('thumbnail') border-red-500 @enderror">
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            <span class="font-medium">Format:</span> JPG, PNG •
                            <span class="font-medium">Maksimal:</span> 2MB
                        </p>
                        @error('thumbnail')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-slate-200">
                    <button type="submit"
                        class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white text-sm font-semibold rounded-xl hover:from-blue-700 hover:to-cyan-700 transition-all shadow-lg hover:shadow-xl hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <span>Upload Video</span>
                    </button>
                    <a href="{{ route('admin.reference-videos.index') }}"
                        class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-8 py-3 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Batal</span>
                    </a>
                </div>
            </form>
        </div>
    </section>

    {{-- JavaScript --}}
    <script>
        // Preview uploaded files
        document.getElementById('video').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
                console.log('Video dipilih:', file.name, `(${fileSize} MB)`);

                // Show warning if file is too large
                if (file.size > 100 * 1024 * 1024) {
                    alert('⚠️ File video terlalu besar! Maksimal 100MB.\n\nUkuran file: ' + fileSize + ' MB');
                    this.value = '';
                }
            }
        });

        document.getElementById('thumbnail').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
                console.log('Thumbnail dipilih:', file.name, `(${fileSize} MB)`);

                // Show warning if file is too large
                if (file.size > 2 * 1024 * 1024) {
                    alert('⚠️ File thumbnail terlalu besar! Maksimal 2MB.\n\nUkuran file: ' + fileSize + ' MB');
                    this.value = '';
                }
            }
        });
    </script>

    {{-- Custom Styles --}}
    <style>
        /* File input styling enhancement */
        input[type="file"]::-webkit-file-upload-button {
            cursor: pointer;
        }

        /* Smooth transitions */
        * {
            transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }
    </style>
</x-layouts.app>