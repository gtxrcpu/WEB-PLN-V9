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

        {{-- Filter Jenis Kartu --}}
        <form method="GET" action="{{ route('admin.approvals.index') }}" id="filterForm">
          <div class="flex gap-2">
            <div class="relative">
              <select name="jenis_kartu"
                class="block pl-3 pr-8 py-2.5 sm:text-sm border-gray-300 rounded-xl focus:ring-purple-500 focus:border-purple-500 shadow-sm cursor-pointer bg-white"
                onchange="document.getElementById('filterForm').submit()">
                <option value="">Semua Jenis</option>
                <option value="kendali"     {{ request('jenis_kartu') === 'kendali'     ? 'selected' : '' }}>Kartu Kendali</option>
                <option value="pemeriksaan" {{ request('jenis_kartu') === 'pemeriksaan' ? 'selected' : '' }}>Kartu Pemeriksaan</option>
              </select>
            </div>
            <div class="relative">
              <select name="filter_modul"
                class="block pl-3 pr-8 py-2.5 sm:text-sm border-gray-300 rounded-xl focus:ring-purple-500 focus:border-purple-500 shadow-sm cursor-pointer bg-white"
                onchange="document.getElementById('filterForm').submit()">
                <option value="">Semua Modul</option>
                <option value="APAR"        {{ request('filter_modul') === 'APAR'        ? 'selected' : '' }}>APAR</option>
                <option value="APAT"        {{ request('filter_modul') === 'APAT'        ? 'selected' : '' }}>APAT</option>
                <option value="APAB"        {{ request('filter_modul') === 'APAB'        ? 'selected' : '' }}>APAB</option>
                <option value="Fire Alarm"  {{ request('filter_modul') === 'Fire Alarm'  ? 'selected' : '' }}>Fire Alarm</option>
                <option value="Box Hydrant" {{ request('filter_modul') === 'Box Hydrant' ? 'selected' : '' }}>Box Hydrant</option>
                <option value="Rumah Pompa" {{ request('filter_modul') === 'Rumah Pompa' ? 'selected' : '' }}>Rumah Pompa</option>
                <option value="P3K"         {{ request('filter_modul') === 'P3K'         ? 'selected' : '' }}>P3K</option>
              </select>
            </div>
            @if(request()->hasAny(['jenis_kartu', 'filter_modul']))
              <a href="{{ route('admin.approvals.index') }}"
                 class="inline-flex items-center px-3 py-2 text-xs font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                ✕ Reset
              </a>
            @endif
          </div>
        </form>
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
              <th class="px-6 py-4 w-12">
                <input type="checkbox" id="selectAllCheckbox" onclick="window.toggleAllCheckboxes(this)" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 cursor-pointer transition-all">
              </th>
              <th class="px-6 py-4">Informasi Modul</th>
              <th class="px-6 py-4">Jenis</th>
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
                  <input type="checkbox" class="approval-checkbox item-checkbox rounded border-gray-300 text-purple-600 focus:ring-purple-500 cursor-pointer transition-all" value="{{ $kartu->id }}" data-type="{{ strtolower(str_replace(' ', '-', $kartu->module)) }}" onclick="if(window.updateBatchUI) window.updateBatchUI()">
                </td>
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
                {{-- JENIS KARTU --}}
                <td class="px-6 py-4">
                  @if(($kartu->jenis_kartu ?? 'kendali') === 'pemeriksaan')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                      </svg>
                      Pemeriksaan
                    </span>
                  @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-700 border border-sky-200">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                      </svg>
                      Kendali
                    </span>
                  @endif
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
                  @php $kLow = strtolower($kartu->kesimpulan ?? ''); @endphp
                  <div class="flex items-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border
                            @if($kLow === 'baik') bg-green-50 text-green-700 border-green-200
                            @elseif(in_array($kLow, ['rusak','tidak baik','tidak_baik'])) bg-red-50 text-red-700 border-red-200
                            @elseif($kLow === 'isi ulang') bg-amber-50 text-amber-700 border-amber-200
                            @else bg-yellow-50 text-yellow-700 border-yellow-200 @endif">
                      <span class="w-1.5 h-1.5 mr-1.5 rounded-full
                              @if($kLow === 'baik') bg-green-500
                              @elseif(in_array($kLow, ['rusak','tidak baik','tidak_baik'])) bg-red-500
                              @elseif($kLow === 'isi ulang') bg-amber-500
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
                <td colspan="8">
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

  {{-- Batch Approval Action Bar (Bottom) --}}
  <div id="batch-approve-bar"
    class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t-2 border-purple-500 shadow-2xl transform translate-y-full transition-transform duration-300 ease-in-out">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        {{-- Selection Info --}}
        <div class="flex items-center gap-3">
          <div class="flex items-center justify-center w-10 h-10 bg-purple-100 rounded-full">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900">
              <span id="selected-count">0</span> Item Dipilih
            </p>
            <p class="text-xs text-gray-500">Pilih tanda tangan untuk approve</p>
          </div>
        </div>

        {{-- Signature Selection & Actions --}}
        <div class="flex items-center gap-3 w-full sm:w-auto">
          {{-- Signature Dropdown with Preview --}}
          <div class="flex-1 sm:flex-initial sm:min-w-[300px]">
            <label for="batch-signature-id" class="sr-only">Pilih Tanda Tangan</label>
            <div class="relative">
              <select id="batch-signature-id"
                class="block w-full px-4 py-2.5 text-sm border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white shadow-sm appearance-none pr-10">
                <option value="">-- Pilih Tanda Tangan --</option>
                @foreach($signatures as $signature)
                  <option value="{{ $signature->id }}" data-image="{{ $signature->signature_url }}" data-name="{{ $signature->name }}" data-position="{{ $signature->position }}">
                    {{ $signature->name }} - {{ $signature->position }}
                  </option>
                @endforeach
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                  <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                </svg>
              </div>
            </div>
            
            {{-- Signature Preview --}}
            <div id="signature-preview" class="hidden mt-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
              <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                  <img id="signature-preview-image" src="" alt="Signature Preview" class="h-16 w-auto object-contain bg-white rounded border border-gray-300 p-1">
                </div>
                <div class="flex-1 min-w-0">
                  <p id="signature-preview-name" class="text-sm font-semibold text-gray-900 truncate"></p>
                  <p id="signature-preview-position" class="text-xs text-gray-600 truncate"></p>
                </div>
              </div>
            </div>
          </div>

          {{-- Approve Button --}}
          <button type="button" id="btn-batch-approve" disabled
            class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
            <svg id="batch-approve-spinner" class="hidden animate-spin -ml-1 mr-2 h-4 w-4 text-white"
              fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
              </path>
            </svg>
            <span id="batch-approve-text">Approve Batch</span>
          </button>

          {{-- Cancel Button --}}
          <button type="button" onclick="window.clearAllSelections()"
            class="inline-flex items-center justify-center px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Batal
          </button>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      (function() {
        'use strict';
        
        // Define global functions immediately for inline onclick handlers
        // These must be available before DOM loads since they're called from inline onclick
        window.toggleAllCheckboxes = function(source) {
          try {
            const isChecked = source.checked;
            const checkboxes = document.querySelectorAll('.item-checkbox');
            
            checkboxes.forEach(function(cb) {
              cb.checked = isChecked;
            });
            
            // Call updateBatchUI if it exists
            if (typeof window.updateBatchUI === 'function') {
              window.updateBatchUI();
            }
          } catch (error) {
            console.error('Error in toggleAllCheckboxes:', error);
          }
        };

        window.updateBatchUI = function() {
          try {
            const batchActionBar = document.getElementById('batch-approve-bar');
            const selectedCountEl = document.getElementById('selected-count');
            const batchSignatureSelect = document.getElementById('batch-signature-id');
            const btnBatchApprove = document.getElementById('btn-batch-approve');
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');

            const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
            const allItemsCount = document.querySelectorAll('.item-checkbox').length;
            
            if (selectedCountEl) {
              selectedCountEl.textContent = checkedCount;
            }
            
            if (checkedCount > 0) {
              if (batchActionBar) {
                batchActionBar.classList.remove('translate-y-full');
                batchActionBar.classList.add('translate-y-0');
              }
            } else {
              if (batchActionBar) {
                batchActionBar.classList.add('translate-y-full');
                batchActionBar.classList.remove('translate-y-0');
              }
            }

            if (btnBatchApprove && batchSignatureSelect) {
              if (checkedCount > 0 && batchSignatureSelect.value !== '') {
                btnBatchApprove.removeAttribute('disabled');
              } else {
                btnBatchApprove.setAttribute('disabled', 'true');
              }
            }

            // Update select all checkbox state
            if (selectAllCheckbox && allItemsCount > 0) {
              selectAllCheckbox.checked = (checkedCount === allItemsCount);
              // Set indeterminate state if some but not all are checked
              selectAllCheckbox.indeterminate = (checkedCount > 0 && checkedCount < allItemsCount);
            }
          } catch (error) {
            console.error('Error in updateBatchUI:', error);
          }
        };

        window.clearAllSelections = function() {
          try {
            // Uncheck all checkboxes
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(function(cb) {
              cb.checked = false;
            });

            // Uncheck select all
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            if (selectAllCheckbox) {
              selectAllCheckbox.checked = false;
              selectAllCheckbox.indeterminate = false;
            }

            // Reset signature selection
            const batchSignatureSelect = document.getElementById('batch-signature-id');
            if (batchSignatureSelect) {
              batchSignatureSelect.value = '';
            }

            // Hide signature preview
            const signaturePreview = document.getElementById('signature-preview');
            if (signaturePreview) {
              signaturePreview.classList.add('hidden');
            }

            // Update UI
            if (typeof window.updateBatchUI === 'function') {
              window.updateBatchUI();
            }
          } catch (error) {
            console.error('Error in clearAllSelections:', error);
          }
        };

        // DOM Ready handler
        document.addEventListener('DOMContentLoaded', function () {
          try {
            const toast = document.getElementById('new-data-toast');
            const countSpan = document.getElementById('new-data-count');
            let lastChecked = "{{ now()->toIso8601String() }}";

            // Poll every 15 seconds for new data
            const pollInterval = setInterval(checkNewData, 15000);

            async function checkNewData() {
              try {
                // Replace + with %2B manually to prevent it being decoded as space
                const encodedTimestamp = encodeURIComponent(lastChecked).replace(/\+/g, '%2B');
                const response = await fetch(`{{ route('admin.approvals.check-new') }}?last_checked=${encodedTimestamp}`);
                
                // Check if response is OK
                if (!response.ok) {
                  console.error('Server returned error:', response.status, response.statusText);
                  return;
                }
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                  console.error('Response is not JSON:', contentType);
                  return;
                }
                
                const data = await response.json();

                if (data.has_new) {
                  showToast(data.count);
                  // Update lastChecked to prevent showing same notification again
                  lastChecked = data.timestamp || new Date().toISOString();
                }
              } catch (error) {
                console.error('Error checking for new approvals:', error);
                // Don't show error to user, just log it
              }
            }

            function showToast(count) {
              if (countSpan) countSpan.textContent = count;
              if (toast) {
                toast.classList.remove('translate-y-20', 'opacity-0');
              }
            }

            // Batch Approval Logic
            const batchSignatureSelect = document.getElementById('batch-signature-id');
            const btnBatchApprove = document.getElementById('btn-batch-approve');
            const batchApproveText = document.getElementById('batch-approve-text');
            const batchApproveSpinner = document.getElementById('batch-approve-spinner');

            // Signature preview elements
            const signaturePreview = document.getElementById('signature-preview');
            const signaturePreviewImage = document.getElementById('signature-preview-image');
            const signaturePreviewName = document.getElementById('signature-preview-name');
            const signaturePreviewPosition = document.getElementById('signature-preview-position');

            if (batchSignatureSelect) {
              // Handle signature selection change
              batchSignatureSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const imageUrl = selectedOption.getAttribute('data-image');
                const name = selectedOption.getAttribute('data-name');
                const position = selectedOption.getAttribute('data-position');

                if (this.value && imageUrl) {
                  // Show preview
                  signaturePreviewImage.src = imageUrl;
                  signaturePreviewName.textContent = name;
                  signaturePreviewPosition.textContent = position;
                  signaturePreview.classList.remove('hidden');
                } else {
                  // Hide preview
                  signaturePreview.classList.add('hidden');
                }

                // Update batch UI
                window.updateBatchUI();
              });
            }

            if (btnBatchApprove) {
              btnBatchApprove.addEventListener('click', async function () {
                const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
                
                // Validation: Check if any items are selected
                if (checkedBoxes.length === 0) {
                  alert('⚠️ Silakan pilih minimal satu kartu kendali untuk di-approve.');
                  return;
                }
                
                // Validation: Check if signature is selected
                if (batchSignatureSelect && !batchSignatureSelect.value) {
                  alert('⚠️ Silakan pilih tanda tangan terlebih dahulu.');
                  batchSignatureSelect.focus();
                  return;
                }

                // Confirmation dialog
                const itemCount = checkedBoxes.length;
                const confirmMessage = `Anda akan meng-approve ${itemCount} kartu kendali.\n\nApakah Anda yakin ingin melanjutkan?`;
                
                if (!confirm(confirmMessage)) {
                  return;
                }

                const items = Array.from(checkedBoxes).map(cb => ({
                  id: cb.value,
                  type: cb.dataset.type
                }));

                // Disable all checkboxes during processing
                document.querySelectorAll('.item-checkbox, #selectAllCheckbox').forEach(cb => {
                  cb.disabled = true;
                });

                // Loading state
                btnBatchApprove.setAttribute('disabled', 'true');
                if (batchSignatureSelect) batchSignatureSelect.disabled = true;
                if (batchApproveText) batchApproveText.textContent = `Memproses ${itemCount} item...`;
                if (batchApproveSpinner) batchApproveSpinner.classList.remove('hidden');

                try {
                  const signatureId = batchSignatureSelect ? batchSignatureSelect.value : null;
                  const response = await fetch(`{{ route('admin.approvals.batch-approve') }}`, {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                      items: items,
                      signature_id: signatureId
                    })
                  });

                  const result = await response.json();
                  
                  if (response.ok && result.success) {
                    // Success state
                    if (batchApproveText) batchApproveText.textContent = '✓ Berhasil!';
                    if (batchApproveSpinner) batchApproveSpinner.classList.add('hidden');
                    
                    // Extract details from response
                    const details = result.details || {};
                    const approved = details.approved || 0;
                    const skipped = details.skipped || 0;
                    const failed = details.failed || 0;
                    const total = details.total || itemCount;
                    
                    // Build detailed message
                    let detailsHtml = '';
                    if (approved > 0) {
                      detailsHtml += `<div class="flex items-center gap-2 text-sm"><span class="font-semibold">${approved}</span> berhasil di-approve</div>`;
                    }
                    if (skipped > 0) {
                      detailsHtml += `<div class="flex items-center gap-2 text-sm text-yellow-100"><span class="font-semibold">${skipped}</span> item di-skip</div>`;
                    }
                    if (failed > 0) {
                      detailsHtml += `<div class="flex items-center gap-2 text-sm text-red-200"><span class="font-semibold">${failed}</span> item gagal</div>`;
                    }
                    
                    // Build reasons section if there are skipped or failed items
                    let reasonsHtml = '';
                    if ((skipped > 0 || failed > 0) && (details.skipped_reasons || details.failed_items)) {
                      reasonsHtml = '<div class="mt-3 pt-3 border-t border-green-400">';
                      reasonsHtml += '<button onclick="this.nextElementSibling.classList.toggle(\'hidden\')" class="text-sm underline hover:no-underline">Lihat Detail</button>';
                      reasonsHtml += '<div class="hidden mt-2 max-h-40 overflow-y-auto bg-green-600 rounded p-2 text-xs">';
                      
                      if (details.skipped_reasons && details.skipped_reasons.length > 0) {
                        reasonsHtml += '<div class="font-semibold mb-1">Item yang di-skip:</div>';
                        details.skipped_reasons.forEach(reason => {
                          reasonsHtml += `<div class="mb-1">• ${reason}</div>`;
                        });
                      }
                      
                      if (details.failed_items && details.failed_items.length > 0) {
                        reasonsHtml += '<div class="font-semibold mb-1 mt-2">Item yang gagal:</div>';
                        details.failed_items.forEach(item => {
                          reasonsHtml += `<div class="mb-1">• ${item}</div>`;
                        });
                      }
                      
                      reasonsHtml += '</div></div>';
                    }
                    
                    // Create success notification with details
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg animate-fade-in-down max-w-md';
                    notification.innerHTML = `
                      <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                          <p class="font-semibold mb-2">Batch Approval Selesai</p>
                          ${detailsHtml}
                          ${reasonsHtml}
                        </div>
                      </div>
                    `;
                    document.body.appendChild(notification);
                    
                    // Reload page after delay (longer if there are details to read)
                    const reloadDelay = (skipped > 0 || failed > 0) ? 3000 : 1500;
                    setTimeout(() => {
                      window.location.reload();
                    }, reloadDelay);
                  } else {
                    throw new Error(result.message || 'Terjadi kesalahan saat memproses batch approval.');
                  }
                } catch (err) {
                  console.error('Batch approval error:', err);
                  
                  // Show error notification
                  const errorNotification = document.createElement('div');
                  errorNotification.className = 'fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg';
                  errorNotification.innerHTML = `
                    <div class="flex items-center gap-3">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <div>
                        <p class="font-semibold">Approval Gagal</p>
                        <p class="text-sm">${err.message || 'Gagal melakukan permintaan. Silakan coba lagi.'}</p>
                      </div>
                    </div>
                  `;
                  document.body.appendChild(errorNotification);
                  
                  setTimeout(() => {
                    errorNotification.remove();
                  }, 5000);
                  
                  // Reset loading state
                  if (batchApproveText) batchApproveText.textContent = 'Approve Batch';
                  if (batchApproveSpinner) batchApproveSpinner.classList.add('hidden');
                  if (batchSignatureSelect) batchSignatureSelect.disabled = false;
                  
                  // Re-enable checkboxes
                  document.querySelectorAll('.item-checkbox, #selectAllCheckbox').forEach(cb => {
                    cb.disabled = false;
                  });
                  
                  if (typeof window.updateBatchUI === 'function') {
                    window.updateBatchUI();
                  }
                }
              });
            }

            // Initialize UI state
            if (typeof window.updateBatchUI === 'function') {
              window.updateBatchUI();
            }
          } catch (error) {
            console.error('Error in DOMContentLoaded handler:', error);
          }
        });
      })();
    </script>
  @endpush
</x-layouts.app>