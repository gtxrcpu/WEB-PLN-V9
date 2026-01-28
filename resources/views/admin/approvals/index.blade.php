<x-layouts.app :title="'Pending Approvals — Admin'">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
      <div>
        <nav class="flex mb-2 text-sm text-gray-500" aria-label="Breadcrumb">
          <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
              <a href="{{ route('admin.dashboard') }}"
                class="inline-flex items-center hover:text-purple-600 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                  </path>
                </svg>
                Dashboard
              </a>
            </li>
            <li>
              <div class="flex items-center">
                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                    clip-rule="evenodd"></path>
                </svg>
                <span class="ml-1 text-gray-700 font-medium md:ml-2">Approvals</span>
              </div>
            </li>
          </ol>
        </nav>
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Pending Approvals</h1>
        <p class="mt-2 text-lg text-gray-600">Daftar kartu kendali yang menunggu persetujuan Anda.</p>
      </div>

      {{-- Unit Filter --}}
      <div class="flex flex-col sm:flex-row gap-3">
        @if(isset($units) && $units->count())
          <form method="POST" action="{{ route('unit.switch') }}" class="relative">
            @csrf
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400 group-hover:text-purple-500 transition-colors" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <select id="unit_id" name="unit_id"
                class="block w-full pl-10 pr-10 py-2.5 sm:text-sm border-gray-300 rounded-xl focus:ring-purple-500 focus:border-purple-500 shadow-sm transition-shadow cursor-pointer bg-white"
                onchange="this.form.submit()">
                <option value="">Semua Unit</option>
                @foreach($units as $unit)
                  <option value="{{ $unit->id }}" @selected((string) ($unitId ?? '') === (string) $unit->id)>{{ $unit->code }}
                    -
                    {{ $unit->name }}
                  </option>
                @endforeach
              </select>
            </div>
          </form>
        @endif
      </div>
    </div>

    @if(session('success'))
      <div class="mb-8 rounded-lg bg-green-50 p-4 border-l-4 border-green-500 shadow-sm animate-fade-in-down">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
          </div>
        </div>
      </div>
    @endif

    {{-- Content --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap">
          <thead>
            <tr
              class="bg-gray-50/50 border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
              <th class="px-6 py-4">Informasi Modul</th>
              <th class="px-6 py-4">Equipment</th>
              <th class="px-6 py-4">Lokasi & Unit</th>
              <th class="px-6 py-4">Inspeksi</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            @forelse($pendingApprovals as $kartu)
              <tr class="hover:bg-slate-50/50 transition-colors group">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="h-10 w-10 flex-shrink-0 flex items-center justify-center rounded-lg 
                                  @if($kartu->module === 'APAR') bg-red-100 text-red-600
                                  @elseif($kartu->module === 'APAT') bg-cyan-100 text-cyan-600
                                  @elseif($kartu->module === 'APAB') bg-orange-100 text-orange-600
                                  @elseif($kartu->module === 'Fire Alarm') bg-rose-100 text-rose-600
                                  @elseif($kartu->module === 'Box Hydrant') bg-blue-100 text-blue-600
                                  @elseif($kartu->module === 'Rumah Pompa') bg-purple-100 text-purple-600
                                  @elseif($kartu->module === 'P3K') bg-emerald-100 text-emerald-600
                                  @else bg-gray-100 text-gray-600 @endif">
                      <span class="text-xs font-bold">{{ substr($kartu->module, 0, 3) }}</span>
                    </div>
                    <div>
                      <div class="text-sm font-bold text-gray-900">{{ $kartu->module }}</div>
                      <div class="text-xs text-gray-500">ID: #{{ $kartu->id }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-semibold text-gray-900">{{ $kartu->equipment_name }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ $kartu->unit_label ?? '-' }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <span
                      class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($kartu->tgl_periksa)->format('d M Y') }}</span>
                    <span class="text-xs text-gray-500">Oleh: {{ $kartu->petugas }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border
                            @if($kartu->kesimpulan === 'baik') bg-green-50 text-green-700 border-green-200
                            @elseif($kartu->kesimpulan === 'rusak') bg-red-50 text-red-700 border-red-200
                            @else bg-yellow-50 text-yellow-700 border-yellow-200 @endif">
                      <span class="w-1.5 h-1.5 mr-1.5 rounded-full
                              @if($kartu->kesimpulan === 'baik') bg-green-500
                              @elseif($kartu->kesimpulan === 'rusak') bg-red-500
                              @else bg-yellow-500 @endif"></span>
                      {{ strtoupper($kartu->kesimpulan) }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium">
                  <a href="{{ $kartu->route_show }}"
                    class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all transform group-hover:scale-105">
                    Review
                    <svg class="ml-2 -mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6">
                  <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="bg-gray-50 rounded-full p-4 mb-4">
                      <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5 13l4 4L19 7m-3.5 9h-9"></path>
                      </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Tidak ada approval pending</h3>
                    <p class="text-sm text-gray-500 mt-1">Saat ini tidak ada kartu kendali yang menunggu persetujuan Anda.
                    </p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-500">Menampilkan {{ $pendingApprovals->count() }} item</p>
      </div>
    </div>
  </div>

  {{-- New Data Notification Toast --}}
  <div id="new-data-toast"
    class="fixed bottom-4 right-4 z-50 transform translate-y-20 opacity-0 transition-all duration-300">
    <div class="bg-indigo-600 rounded-lg shadow-lg p-4 flex items-center justify-between gap-4 min-w-[300px]">
      <div class="flex items-center gap-3">
        <span class="flex h-2 w-2 relative">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-200 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
        </span>
        <p class="text-white text-sm font-medium">
          <span id="new-data-count">0</span> Data Baru Masuk
        </p>
      </div>
      <button onclick="window.location.reload()"
        class="bg-white text-indigo-600 px-3 py-1.5 rounded-md text-xs font-bold hover:bg-gray-100 transition-colors">
        Refresh
      </button>
    </div>
  </div>

  @push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const toast = document.getElementById('new-data-toast');
        const countSpan = document.getElementById('new-data-count');
        let lastChecked = "{{ now()->toIso8601String() }}";

        // Poll every 15 seconds
        const pollInterval = setInterval(checkNewData, 15000);

        async function checkNewData() {
          try {
            const response = await fetch(`{{ route('admin.approvals.check-new') }}?last_checked=${lastChecked}`);
            const data = await response.json();

            if (data.has_new) {
              showToast(data.count);
              // Update timestamp to avoid repeated notifications for same data
              // Only update if you want to 'acknowledge' them without refresh, 
              // but here we want to keep showing until refresh, so maybe don't update?
              // Actually, if more come in, count goes up. 
              // Let's keep lastChecked as page load time to catch EVERYTHING new since page load.
            }
          } catch (error) {
            console.error('Error checking for new approvals:', error);
          }
        }

        function showToast(count) {
          countSpan.textContent = count;
          toast.classList.remove('translate-y-20', 'opacity-0');

          // Optional: Play sound
          // const audio = new Audio('/notification.mp3'); // If we had one
          // audio.play().catch(e => console.log('Audio autoplay prevented'));
        }
      });
    </script>
  @endpush
</x-layouts.app>