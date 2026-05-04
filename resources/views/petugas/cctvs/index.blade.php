<x-layouts.app :title="'Manajemen CCTV'">
  <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

    {{-- Back Button --}}
    <div class="mb-4">
      <a href="{{ route('user.dashboard') }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border-2 border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition-all duration-200 shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        <span>Kembali ke Dashboard</span>
      </a>
    </div>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <div class="inline-flex items-center px-2 py-1 mb-2 text-xs font-semibold tracking-wide text-indigo-700 bg-indigo-100 rounded-full">
          Manajemen CCTV Unit
        </div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Manajemen CCTV Terpadu</h1>
        <p class="text-sm text-slate-600 mt-1">Pantau dan kelola indikator kualitas CCTV di unit Anda</p>
      </div>
      <div class="flex items-center gap-3">
        <a href="{{ route('petugas.cctvs.create') }}"
          class="group inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-all duration-300">
          <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Tambah CCTV</span>
        </a>
      </div>
    </div>

    @if (session('success'))
    <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200" role="alert">
      {{ session('success') }}
    </div>
    @endif

    {{-- Toolbar --}}
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
      <form action="{{ route('petugas.cctvs.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1 relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input type="text" name="search" value="{{ request('search') }}"
            class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors duration-200"
            placeholder="Cari CCTV, lokasi, catatan...">
        </div>

        <select name="status" class="w-full sm:w-48 block pl-3 pr-10 py-2 text-base border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm bg-slate-50 transition-colors duration-200">
          <option value="">Semua Status</option>
          <option value="Baik" {{ request('status') == 'Baik' ? 'selected' : '' }}>Baik</option>
          <option value="Jelek" {{ request('status') == 'Jelek' ? 'selected' : '' }}>Jelek</option>
        </select>

        <button type="submit" class="inline-flex justify-center items-center px-4 py-2 border-2 border-indigo-600 rounded-xl text-sm font-semibold text-indigo-600 bg-white hover:bg-indigo-50 transition-all duration-200">
          Filter
        </button>
        <a href="{{ route('petugas.cctvs.index') }}" class="inline-flex justify-center items-center px-4 py-2 border-2 border-slate-200 rounded-xl text-sm font-semibold text-slate-600 bg-white hover:bg-slate-50 transition-all duration-200">
          Reset
        </a>
      </form>
    </div>

    {{-- Stats Summary --}}
    @php
      $total = $cctvs->count();
      $baik = $cctvs->where('status', 'Baik')->count();
      $jelek = $cctvs->where('status', 'Jelek')->count();
    @endphp
    <div class="grid grid-cols-3 gap-4">
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 text-center">
        <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Total CCTV</p>
        <p class="text-3xl font-black text-slate-900">{{ $total }}</p>
      </div>
      <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-5 text-center">
        <p class="text-xs text-green-600 font-semibold uppercase tracking-wider mb-1">Online (Baik)</p>
        <p class="text-3xl font-black text-green-600">{{ $baik }}</p>
      </div>
      <div class="bg-white rounded-2xl border border-rose-100 shadow-sm p-5 text-center">
        <p class="text-xs text-rose-500 font-semibold uppercase tracking-wider mb-1">Offline (Jelek)</p>
        <p class="text-3xl font-black text-rose-500">{{ $jelek }}</p>
      </div>
    </div>

    {{-- Grid View of CCTVs --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      @forelse($cctvs as $cctv)
      <div class="flex flex-col bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-lg hover:shadow-indigo-500/20 hover:border-indigo-500/40 transition-all duration-300 group">

        {{-- Camera Feed Area --}}
        <div class="relative h-48 w-full bg-slate-800 shrink-0">
          @php
            $placeholders = [
              'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80',
              'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=800&q=80',
              'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=800&q=80',
              'https://images.unsplash.com/photo-1541888031388-75c12808c1d3?auto=format&fit=crop&w=800&q=80'
            ];
            $imageUrl = $placeholders[$cctv->id % count($placeholders)];
          @endphp

          <img src="{{ $imageUrl }}" class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700" alt="CCTV Feed" loading="lazy" />
          <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-transparent to-black/70"></div>

          {{-- Offline Overlay --}}
          @if($cctv->status !== 'Baik')
          <div class="absolute inset-0 bg-slate-900/80 flex flex-col items-center justify-center z-20">
            <svg class="w-10 h-10 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
            <span class="text-xs font-bold text-slate-300 tracking-widest uppercase">Koneksi Terputus</span>
          </div>
          @endif

          {{-- Top Left: REC + Cam ID --}}
          <div class="absolute top-3 left-3 z-30 flex flex-wrap gap-2">
            @if($cctv->status === 'Baik')
            <div class="flex items-center gap-1.5 px-2 py-1 bg-black bg-opacity-60 border border-slate-600 rounded">
              <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
              <span class="text-xs font-bold text-red-500 tracking-widest uppercase">REC</span>
            </div>
            @endif
            <div class="flex items-center px-2 py-1 bg-black bg-opacity-60 border border-slate-600 rounded">
              <span class="text-[11px] font-mono font-medium text-white tracking-widest uppercase">
                CAM_{{ str_pad($cctv->id, 3, '0', STR_PAD_LEFT) }}_{{ $cctv->location_code }}
              </span>
            </div>
          </div>

          {{-- Top Right: Status Toggle --}}
          <div class="absolute top-3 right-3 z-30 cursor-pointer hover:scale-105 transition-transform"
            onclick="toggleStatus({{ $cctv->id }}, '{{ $cctv->status }}')" title="Ubah Status">
            @if($cctv->status === 'Baik')
            <div class="flex items-center gap-1.5 px-2 py-1 bg-green-900 bg-opacity-80 border border-green-500 rounded">
              <div class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></div>
              <span class="text-[10px] font-bold text-white tracking-wider uppercase">ONLINE</span>
            </div>
            @else
            <div class="flex items-center gap-1.5 px-2 py-1 bg-red-900 bg-opacity-80 border border-red-500 rounded">
              <div class="w-1.5 h-1.5 rounded-full bg-red-500"></div>
              <span class="text-[10px] font-bold text-white tracking-wider uppercase">OFFLINE</span>
            </div>
            @endif
          </div>

          {{-- Timestamp --}}
          <div class="absolute bottom-3 right-3 z-30">
            <span class="text-xs font-mono font-bold text-white tracking-widest drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)] cctv-timestamp">
              {{ now()->format('Y-m-d H:i:s') }}
            </span>
          </div>
        </div>

        {{-- Info Section --}}
        <div class="p-5 flex flex-col flex-grow bg-slate-900 relative z-10 border-t border-slate-800">
          <h3 class="text-lg font-bold text-white mb-2 line-clamp-1 group-hover:text-indigo-400 transition-colors">{{ $cctv->name }}</h3>

          <p class="text-slate-400 text-sm mb-5 line-clamp-2 leading-relaxed flex-grow">
            {{ $cctv->notes ?? 'Sistem pemantauan aktif. Kamera beroperasi dengan parameter normal.' }}
          </p>

          <div class="flex items-center gap-2 pt-4 border-t border-slate-800 mt-auto">
            <a href="{{ route('petugas.cctvs.edit', $cctv) }}"
              class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold rounded-lg transition-colors border border-slate-700">
              <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Edit
            </a>
            {{-- Petugas tidak bisa hapus, hanya lihat dan edit --}}
            <a href="{{ route('cctv.dashboard') }}"
              class="flex-none inline-flex justify-center items-center px-3 py-2 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 hover:text-indigo-300 text-sm font-semibold rounded-lg transition-colors border border-indigo-500/20" title="Live View">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
            </a>
          </div>
        </div>
      </div>
      @empty
      <div class="col-span-full bg-slate-50 rounded-2xl p-8 text-center border-2 border-dashed border-slate-200">
        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-slate-900">Belum ada CCTV di unit Anda</h3>
        <p class="mt-1 text-sm text-slate-500">Mulai daftarkan kamera CCTV pertama untuk unit ini.</p>
        <div class="mt-6">
          <a href="{{ route('petugas.cctvs.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-xl text-white bg-indigo-600 hover:bg-indigo-700">
            Tambah Perangkat CCTV
          </a>
        </div>
      </div>
      @endforelse
    </div>
  </div>

  <script>
    // Live Timestamp Updater
    setInterval(() => {
      const now = new Date();
      const formatted = now.getFullYear() + '-' +
        String(now.getMonth() + 1).padStart(2, '0') + '-' +
        String(now.getDate()).padStart(2, '0') + ' ' +
        String(now.getHours()).padStart(2, '0') + ':' +
        String(now.getMinutes()).padStart(2, '0') + ':' +
        String(now.getSeconds()).padStart(2, '0');
      document.querySelectorAll('.cctv-timestamp').forEach(el => {
        el.textContent = formatted;
      });
    }, 1000);

    async function toggleStatus(id, currentStatus) {
      if (!confirm('Apakah Ingin mengubah status dari perangkat ini?')) return;
      let newStatus = currentStatus === 'Baik' ? 'Jelek' : 'Baik';
      try {
        const response = await fetch(`/petugas/cctvs/${id}/toggle-status`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({ status: newStatus })
        });
        if (response.ok) {
          window.location.reload();
        } else {
          alert('Gagal mengubah status perangkat.');
        }
      } catch (e) {
        alert('Terjadi kesalahan jaringan.');
      }
    }
  </script>
</x-layouts.app>
