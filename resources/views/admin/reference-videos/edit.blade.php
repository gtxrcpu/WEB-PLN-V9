<x-layouts.app :title="'Admin - Edit Video Referensi'">
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

        {{-- Back Button --}}
        <div class="container-fluid py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Header -->
                    <div class="mb-4">
                        <h2>Edit Video Referensi</h2>
                        <p class="text-muted">Update informasi video atau ganti file</p>
                    </div>

                    <!-- Form Card -->
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.reference-videos.update', $referenceVideo->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <!-- Title -->
                                <div class="mb-3">
                                    <label for="title" class="form-label">Judul Video <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                        id="title" name="title" value="{{ old('title', $referenceVideo->title) }}"
                                        required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description"
                                        rows="3">{{ old('description', $referenceVideo->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Unit Selector -->
                                <div class="mb-3">
                                    <label for="unit_id" class="form-label">Unit</label>
                                    <select class="form-select @error('unit_id') is-invalid @enderror" id="unit_id"
                                        name="unit_id">
                                        <option value="">-- Semua Unit --</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ old('unit_id', $referenceVideo->unit_id) == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->code }} - {{ $unit->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Pilih unit tertentu atau kosongkan untuk semua unit</div>
                                    @error('unit_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Current Video Info -->
                                <div class="mb-3">
                                    <label class="form-label">Video Saat Ini</label>
                                    <div class="border rounded p-3 bg-light">
                                        <i class="fas fa-video me-2"></i>{{ basename($referenceVideo->video_path) }}
                                    </div>
                                </div>

                                <!-- Replace Video (Optional) -->
                                <div class="mb-3">
                                    <label for="video" class="form-label">Ganti Video (Opsional)</label>
                                    <input type="file" class="form-control @error('video') is-invalid @enderror"
                                        id="video" name="video" accept="video/mp4,video/mov,video/avi,video/wmv">
                                    <div class="form-text">Kosongkan jika tidak ingin mengganti. Format: MP4, MOV, AVI,
                                        WMV.
                                        Maksimal 100MB</div>
                                    @error('video')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Current Thumbnail -->
                                @if($referenceVideo->thumbnail_path)
                                    <div class="mb-3">
                                        <label class="form-label">Thumbnail Saat Ini</label>
                                        <div>
                                            <img src="{{ $referenceVideo->thumbnail_url }}" alt="Thumbnail"
                                                style="max-height: 150px;" class="rounded">
                                        </div>
                                    </div>
                                @endif

                                <!--Replace Thumbnail (Optional) -->
                                <div class="mb-3">
                                    <label for="thumbnail"
                                        class="form-label">{{ $referenceVideo->thumbnail_path ? 'Ganti' : 'Upload' }}
                                        Thumbnail
                                        (Opsional)</label>
                                    <input type="file" class="form-control @error('thumbnail') is-invalid @enderror"
                                        id="thumbnail" name="thumbnail" accept="image/*">
                                    <div class="form-text">Format: JPG, PNG. Maksimal 2MB</div>
                                    @error('thumbnail')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Buttons -->
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                                    </button>
                                    <a href="{{ route('admin.reference-videos.index') }}" class="btn btn-secondary">
                                        Batal
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>