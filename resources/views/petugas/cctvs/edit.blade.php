<x-layouts.app :title="'Edit CCTV'">
<div class="max-w-4xl mx-auto px-4 py-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Edit Perangkat CCTV</h1>
            <p class="text-sm text-slate-600 mt-1">Perbarui informasi kamera <span class="font-semibold text-slate-800">{{ $cctv->name }}</span>.</p>
        </div>
        <div>
            <a href="{{ route('petugas.cctvs.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50 transition-colors">
                Kembali
            </a>
        </div>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <form action="{{ route('petugas.cctvs.update', $cctv) }}" method="POST" class="p-6 sm:p-8 space-y-8">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 border-b border-slate-100 pb-2">Informasi Utama</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Nama CCTV --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                            Nama/Label Perangkat <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $cctv->name) }}"
                            class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-colors sm:text-sm p-3 border"
                            placeholder="Contoh: CCTV Gerbang Utama" required>
                        @error('name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Kode Lokasi --}}
                    <div>
                        <label for="location_code" class="block text-sm font-medium text-slate-700 mb-1">
                            Kode/Detail Lokasi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="location_code" id="location_code" value="{{ old('location_code', $cctv->location_code) }}"
                            class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-colors sm:text-sm p-3 border"
                            placeholder="Contoh: LOK-01" required>
                        @error('location_code') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Unit locked --}}
                <input type="hidden" name="unit_id" value="{{ $unitId }}">

                {{-- Status --}}
                <div class="pt-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Status Saat Ini <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-6">
                        <label class="relative flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="status" value="Baik" {{ old('status', $cctv->status) == 'Baik' ? 'checked' : '' }}
                                class="w-5 h-5 text-indigo-600 border-slate-300 focus:ring-indigo-500 cursor-pointer">
                            <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Baik</span>
                        </label>
                        <label class="relative flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="status" value="Jelek" {{ old('status', $cctv->status) == 'Jelek' ? 'checked' : '' }}
                                class="w-5 h-5 text-red-600 border-slate-300 focus:ring-red-500 cursor-pointer">
                            <span class="text-sm font-medium text-slate-700 group-hover:text-slate-900">Jelek / Bermasalah</span>
                        </label>
                    </div>
                    @error('status') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Catatan --}}
                <div>
                    <label for="notes" class="block text-sm font-medium text-slate-700 mb-1">
                        Catatan Teknis <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea name="notes" id="notes" rows="3"
                        class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-colors sm:text-sm p-3 border"
                        placeholder="Contoh: Lensa perlu dibersihkan">{{ old('notes', $cctv->notes) }}</textarea>
                    @error('notes') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('petugas.cctvs.index') }}"
                    class="inline-flex justify-center items-center px-6 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold hover:bg-slate-200 transition-all duration-300">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex justify-center items-center px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-all duration-300">
                    Perbarui CCTV
                </button>
            </div>
        </form>
    </div>
</div>
</x-layouts.app>
