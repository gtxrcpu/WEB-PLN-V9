<x-layouts.app :title="'Admin - Edit Video Referensi'">
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
                    Edit Video Referensi
                </h2>
                <p class="text-sm text-gray-600 mt-1">Update informasi video atau ganti file</p>
            </div>
        </div>
    </section>

    {{-- Main Form --}}
    <section class="mb-6">
        <div class="bg-white rounded-xl shadow-lg ring-1 ring-slate-200 overflow-hidden">
            <form action="{{ route('admin.reference-videos.update', $referenceVideo->id) }}" method="POST"
                enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
                @csrf
                @method('PUT')

                {{-- Title --}}
                <div>
                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                        Judul Video <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" value="{{ old('title', $referenceVideo->title) }}"
                        required
                        class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        Deskripsi
                    </label>
                    <textarea id="description" name="description" rows="4"
                        class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none @error('description') border-red-500 @enderror">{{ old('description', $referenceVideo->description) }}</textarea>
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
                            <option value="{{ $unit->id }}" {{ old('unit_id', $referenceVideo->unit_id) == $unit->id ? 'selected' : '' }}>
                                {{ $unit->code }} - {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Pilih unit tertentu atau kosongkan untuk semua unit</p>
                    @error('unit_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Current Video Info --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Video Saat Ini
                    </label>
                    <div
                        class="flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-blue-50 to-cyan-50 border border-blue-200 rounded-lg">
                        <div
                            class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-blue-900 truncate">
                                {{ basename($referenceVideo->video_path) }}</p>
                            <p class="text-xs text-blue-600">File video yang sedang digunakan</p>
                        </div>
                    </div>
                </div>

                {{-- Replace Video (Optional) --}}
                <div>
                    <label for="video" class="block text-sm font-semibold text-gray-700 mb-2">
                        Ganti Video <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <div class="relative">
                        <input type="file" id="video" name="video" accept="video/mp4,video/mov,video/avi,video/wmv"
                            class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('video') border-red-500 @enderror">
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        <span class="font-medium">Format:</span> MP4, MOV, AVI, WMV •
                        <span class="font-medium">Maksimal:</span> 100MB •
                        <span class="text-amber-600">Kosongkan jika tidak ingin mengganti</span>
                    </p>
                    @error('video')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Current Thumbnail --}}
                @if ($referenceVideo->thumbnail_path)
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Thumbnail Saat Ini
                        </label>
                        <div class="inline-block rounded-lg overflow-hidden ring-2 ring-slate-200 shadow-md">
                            <img src="{{ $referenceVideo->thumbnail_url }}" alt="Thumbnail" class="max-h-48 w-auto">
                        </div>
                    </div>
                @endif

                {{-- Replace Thumbnail (Optional) --}}
                <div>
                    <label for="thumbnail" class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ $referenceVideo->thumbnail_path ? 'Ganti' : 'Upload' }} Thumbnail
                        <span class="text-gray-400 font-normal">(Opsional)</span>
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

                {{-- Delete Video Option --}}
                <div class="pt-6 border-t border-slate-200">
                    <div class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-red-900 mb-1">Hapus Video</h4>
                            <p class="text-sm text-red-700 mb-3">Jika Anda ingin menghapus video ini secara permanen,
                                gunakan tombol di bawah.</p>
                            <button type="button" onclick="confirmDelete()"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition-all shadow-md hover:shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Hapus Video
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-slate-200">
                    <button type="submit"
                        class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white text-sm font-semibold rounded-xl hover:from-blue-700 hover:to-cyan-700 transition-all shadow-lg hover:shadow-xl hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Perubahan</span>
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

    {{-- Delete Form (Hidden) --}}
    <form id="delete-form" action="{{ route('admin.reference-videos.destroy', $referenceVideo) }}" method="POST"
        class="hidden">
        @csrf
        @method('DELETE')
    </form>

    {{-- JavaScript --}}
    <script>
        function confirmDelete() {
            if (confirm('Apakah Anda yakin ingin menghapus video ini? Tindakan ini tidak dapat dibatalkan.')) {
                document.getElementById('delete-form').submit();
            }
        }

        // Preview uploaded files
        document.getElementById('video').addEventListener('change', function (e) {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                console.log('Video dipilih:', fileName);
            }
        });

        document.getElementById('thumbnail').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    console.log('Thumbnail preview loaded');
                };
                reader.readAsDataURL(file);
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