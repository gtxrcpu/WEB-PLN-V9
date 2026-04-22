<x-layouts.app :title="'Pending Approvals â€” Leader'">
  <div class="mb-4 sm:mb-6">
    <a href="{{ route('leader.dashboard') }}"
      class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white hover:bg-slate-50 text-slate-700 transition-colors shadow-sm border border-slate-200 mb-4">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      <span class="text-sm font-medium">Kembali ke Dashboard</span>
    </a>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Pending Approvals</h1>
        <p class="text-sm text-gray-600 mt-1">Kartu kendali yang menunggu approval dari leader unit</p>
      </div>
      <a href="{{ route('leader.signatures.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-md text-sm font-semibold">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        Buat / Kelola Tanda Tangan
      </a>
    </div>
  </div>

  @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
      <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <span class="text-green-800 font-medium">{{ session('success') }}</span>
    </div>
  @endif

  {{-- Module Filter --}}
  <div class="bg-white rounded-xl shadow-lg ring-1 ring-slate-200 p-6 mb-6">
    <form method="GET" action="{{ route('leader.approvals.index') }}" class="flex items-end gap-4">
      <div class="flex-1">
        <label for="module" class="block text-sm font-semibold text-gray-700 mb-2">Filter by Module</label>
        <select name="module" id="module"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="">Semua Modul</option>
          @foreach($modules as $key => $config)
            <option value="{{ $key }}" {{ $moduleFilter === $key ? 'selected' : '' }}>
              {{ $config['label'] }}
            </option>
          @endforeach
        </select>
      </div>
      <button type="submit"
        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold">
        Filter
      </button>
      @if($moduleFilter)
        <a href="{{ route('leader.approvals.index') }}"
          class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-semibold">
          Clear
        </a>
      @endif
    </form>
  </div>

  <div class="bg-white rounded-xl shadow-lg ring-1 ring-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-slate-50 border-b border-slate-200">
          <tr>
            <th class="px-6 py-4 w-12">
              <input type="checkbox" id="selectAllCheckbox" onclick="window.toggleAllCheckboxes(this)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer transition-all">
            </th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Modul</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Equipment</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Tanggal
              Periksa</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Petugas</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Kesimpulan
            </th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Dibuat Oleh
            </th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Waktu Dibuat
            </th>
            <th class="px-6 py-4 text-right text-xs font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          @forelse($pendingApprovals as $kartu)
            <tr class="hover:bg-slate-50 transition-colors">
              <td class="px-6 py-4">
                <input type="checkbox" class="approval-checkbox item-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer transition-all" value="{{ $kartu->id }}" data-type="{{ strtolower($kartu->module_type) }}" onclick="if(window.updateBatchUI) window.updateBatchUI()">
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                  {{ $kartu->module_label }}
                </span>
              </td>
              <td class="px-6 py-4">
                @php
                  $equipment = $kartu->{$modules[$kartu->module_type]['equipment']} ?? null;
                @endphp
                <div>
                  <p class="font-semibold text-gray-900">{{ $equipment->barcode ?? $equipment->serial_no ?? '-' }}</p>
                  <p class="text-sm text-gray-500">{{ $equipment->location_code ?? '-' }}</p>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-700">
                {{ \Carbon\Carbon::parse($kartu->tgl_periksa)->format('d M Y') }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-700">{{ $kartu->petugas }}</td>
              <td class="px-6 py-4">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                      @if($kartu->kesimpulan === 'baik') bg-green-100 text-green-700
                      @elseif($kartu->kesimpulan === 'rusak') bg-red-100 text-red-700
                      @else bg-yellow-100 text-yellow-700 @endif">
                  {{ ucfirst($kartu->kesimpulan) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm">
                  <p class="font-medium text-gray-900">{{ get_user_display_name($kartu->user, 'Unknown User') }}</p>
                  @if($kartu->user)
                    <p class="text-xs text-gray-500">{{ $kartu->user->username ?? '-' }}</p>
                    <p class="text-xs text-gray-500">{{ get_user_role_display($kartu->user) }}</p>
                  @endif
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">
                {{ $kartu->created_at->format('d M Y H:i') }}
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <!-- View Details Button -->
                  <a href="{{ route('leader.approvals.show', [$kartu->module_type, $kartu->id]) }}"
                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Lihat Detail
                  </a>
</div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="font-medium">Tidak ada kartu kendali yang menunggu approval</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($pendingApprovals->hasPages())
      <div class="px-6 py-4 border-t border-slate-200">
        {{ $pendingApprovals->links() }}
      </div>
    @endif
  </div>

  {{-- Batch Approve Action Bar --}}
  <div id="batch-approve-bar"
    class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200 shadow-2xl transform translate-y-full transition-transform duration-300 flex items-center justify-between px-6 py-4 sm:px-8">
    <div class="flex items-center gap-4">
      <div class="bg-blue-100 text-blue-700 h-10 w-10 flex items-center justify-center rounded-full font-bold">
        <span id="selected-count">0</span>
      </div>
      <div>
        <p class="text-sm font-bold text-gray-900">Item Terpilih</p>
        <p class="text-xs text-gray-500">Pilih tanda tangan dan approve sekaligus</p>
      </div>
    </div>
    <div class="flex items-center gap-4">
      <select id="batch-signature-id" class="rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500 py-2 pl-3 pr-10">
        <option value="">-- Pilih Tanda Tangan --</option>
        @if(isset($signatures))
          @foreach($signatures as $sig)
            <option value="{{ $sig->id }}">{{ $sig->name }} ({{ $sig->position }})</option>
          @endforeach
        @endif
      </select>
      <button id="btn-batch-approve" disabled
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
        <span id="batch-approve-text">Approve Batch</span>
        <svg id="batch-approve-spinner" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </button>
    </div>
  </div>

  @push('scripts')
    <script>
      // Global functions for inline onclick handlers
      window.updateBatchUI = function() {
        const batchActionBar = document.getElementById('batch-approve-bar');
        const selectedCountEl = document.getElementById('selected-count');
        const batchSignatureSelect = document.getElementById('batch-signature-id');
        const btnBatchApprove = document.getElementById('btn-batch-approve');
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');

        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        const allItemsCount = document.querySelectorAll('.item-checkbox').length;
        
        if (selectedCountEl) selectedCountEl.textContent = checkedCount;
        
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

        if (selectAllCheckbox && allItemsCount > 0) {
          selectAllCheckbox.checked = (checkedCount === allItemsCount);
        }
      };

      window.toggleAllCheckboxes = function(source) {
        const isChecked = source.checked;
        document.querySelectorAll('.item-checkbox').forEach(cb => {
          cb.checked = isChecked;
        });
        window.updateBatchUI();
      };

      document.addEventListener('DOMContentLoaded', function () {
        const batchSignatureSelect = document.getElementById('batch-signature-id');
        const btnBatchApprove = document.getElementById('btn-batch-approve');
        const batchApproveText = document.getElementById('batch-approve-text');
        const batchApproveSpinner = document.getElementById('batch-approve-spinner');

        if (batchSignatureSelect) {
          batchSignatureSelect.addEventListener('change', window.updateBatchUI);
        }

        if (btnBatchApprove) {
          btnBatchApprove.addEventListener('click', async function () {
            const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
            if (checkedBoxes.length === 0) return;
            if (batchSignatureSelect && !batchSignatureSelect.value) {
              alert('Silakan pilih tanda tangan terlebih dahulu.');
              return;
            }

            const items = Array.from(checkedBoxes).map(cb => ({
              id: cb.value,
              module: cb.dataset.type // mapping dataset 'type' ke key 'module' yang diterima backend
            }));

            // Loading state
            btnBatchApprove.setAttribute('disabled', 'true');
            if (batchApproveText) batchApproveText.textContent = 'Memproses...';
            if (batchApproveSpinner) batchApproveSpinner.classList.remove('hidden');

            try {
              const signatureId = batchSignatureSelect ? batchSignatureSelect.value : null;
              const response = await fetch(`{{ route('leader.approvals.batch-approve') }}`, {
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
                if (batchApproveText) batchApproveText.textContent = 'Berhasil';
                if (batchApproveSpinner) batchApproveSpinner.classList.add('hidden');
                
                window.location.reload();
              } else {
                alert(result.message || 'Terjadi kesalahan saat memproses batch approval.');
                throw new Error(result.message);
              }
            } catch (err) {
              console.error(err);
              alert('Gagal melakukan permintaan. Silakan coba lagi.');
              if (batchApproveText) batchApproveText.textContent = 'Approve Batch';
              if (batchApproveSpinner) batchApproveSpinner.classList.add('hidden');
              window.updateBatchUI();
            }
          });
        }
      });
    </script>
  @endpush
</x-layouts.app>
