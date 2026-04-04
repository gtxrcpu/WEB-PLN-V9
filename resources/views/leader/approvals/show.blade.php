<x-layouts.app :title="'Approve Kartu Kendali — Leader'">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Breadcrumb & Header --}}
    <div class="mb-8">
      <nav class="flex mb-4 text-sm text-gray-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
          <li class="inline-flex items-center">
            <a href="{{ route('leader.approvals.index') }}"
              class="inline-flex items-center hover:text-blue-600 transition-colors">
              <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path
                  d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                </path>
              </svg>
              Approvals
            </a>
          </li>
          <li>
            <div class="flex items-center">
              <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                  clip-rule="evenodd"></path>
              </svg>
              <span class="ml-1 text-gray-700 font-medium md:ml-2">Review Kartu</span>
            </div>
          </li>
        </ol>
      </nav>

      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Approve Kartu Kendali</h1>
          <p class="mt-2 text-lg text-gray-600">Review hasil inspeksi dan berikan persetujuan atau revisi.</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="px-3 py-1 text-xs font-semibold tracking-wide uppercase bg-blue-100 text-blue-700 rounded-full">
            {{ strtoupper(str_replace('_', ' ', $module)) }}
          </span>
          <span class="text-sm text-gray-500">ID: #{{ $kartu->id }}</span>
        </div>
      </div>
    </div>

    {{-- Alerts --}}
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

    @if($errors->any())
      <div class="mb-8 rounded-lg bg-red-50 p-4 border-l-4 border-red-500 shadow-sm animate-fade-in-down">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan pada form:</h3>
            <div class="mt-2 text-sm text-red-700">
              <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      {{-- Left Column: Details --}}
      <div class="lg:col-span-2 space-y-8">

        {{-- Card: Detail Equipment --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
              </svg>
              Informasi Inspeksi
            </h2>
            <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($kartu->tgl_periksa)->format('d F Y') }}</span>
          </div>

          <div class="p-6">
            @php $equipment = $kartu->{$equipmentRelation}; @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Equipment</p>
                <p class="text-lg font-bold text-gray-900 font-mono">{{ $equipment->barcode ?? $equipment->serial_no }}
                </p>
                <p class="text-sm text-gray-600 mt-1 flex items-center gap-1">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                  {{ $equipment->location_code ?? 'Lokasi tidak tersedia' }}
                </p>
              </div>

              <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                <div class="flex items-center gap-3 mb-2">
                  <div
                    class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold">
                    {{ substr($kartu->petugas, 0, 2) }}
                  </div>
                  <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Petugas Inspeksi</p>
                    <p class="font-medium text-gray-900">{{ $kartu->petugas }}</p>
                  </div>
                </div>
                <div class="text-sm text-gray-600 pl-13">
                  <p>Created by: <span class="font-medium">{{ get_user_display_name($kartu->user, 'Unknown') }}</span>
                  </p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ $kartu->created_at->diffForHumans() }}</p>
                </div>
              </div>
            </div>

            {{-- Status Kesimpulan --}}
            <div class="mt-6 flex items-center justify-between p-4 rounded-xl border
                    @if($kartu->kesimpulan === 'baik') bg-green-50 border-green-200
                    @elseif($kartu->kesimpulan === 'rusak') bg-red-50 border-red-200
                    @else bg-yellow-50 border-yellow-200 @endif">
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider mb-1
                             @if($kartu->kesimpulan === 'baik') text-green-700
                             @elseif($kartu->kesimpulan === 'rusak') text-red-700
                             @else text-yellow-700 @endif">
                  Kesimpulan Akhir
                </p>
                <p class="text-xl font-bold
                            @if($kartu->kesimpulan === 'baik') text-green-800
                            @elseif($kartu->kesimpulan === 'rusak') text-red-800
                            @else text-yellow-800 @endif">
                  {{ strtoupper($kartu->kesimpulan) }}
                </p>
              </div>
              <div class="h-10 w-10 flex items-center justify-center rounded-full
                         @if($kartu->kesimpulan === 'baik') bg-green-200 text-green-700
                         @elseif($kartu->kesimpulan === 'rusak') bg-red-200 text-red-700
                         @else bg-yellow-200 text-yellow-700 @endif">
                @if($kartu->kesimpulan === 'baik')
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                @else
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                  </svg>
                @endif
              </div>
            </div>
          </div>
        </div>

        {{-- Card: Checklist Item --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h3 class="font-bold text-gray-900">Rincian Pemeriksaan</h3>
          </div>
          <div class="p-6">
            <!-- Checklist Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              @php $moduleKey = str_replace('-', '_', $module); @endphp

              @if($moduleKey === 'apar')
                @php
                    $aparConditions = [
                        'pressure_gauge' => 'Pressure Gauge',
                        'pin_segel'      => 'Pin & Segel',
                        'selang'         => 'Selang',
                        'tabung'         => 'Tabung',
                        'label'          => 'Label',
                        'kondisi_fisik'  => 'Kondisi Fisik',
                    ];
                @endphp
                @foreach($aparConditions as $colName => $colLabel)
                    @php $condVal = $kartu->$colName ?? null; @endphp
                  <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                    <span class="text-sm font-medium text-gray-600">{{ $colLabel }}</span>
                    @if($condVal === 'baik')
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-700 border border-green-200">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Baik
                      </span>
                    @elseif($condVal === 'tidak_baik')
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-700 border border-red-200">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Tidak Baik
                      </span>
                    @elseif($condVal && $condVal !== '-')
                      <span class="text-sm font-bold text-gray-900">{{ ucfirst(str_replace('_', ' ', $condVal)) }}</span>
                    @else
                      <span class="text-xs text-gray-400 italic">Tidak diisi</span>
                    @endif
                  </div>
                @endforeach
              @elseif($moduleKey === 'apat')
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Kondisi Fisik</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->kondisi_fisik ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Drum</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->drum ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Aduk Pasir</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->aduk_pasir ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Sekop</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->sekop ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Fire Blanket</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->fire_blanket ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Ember</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->ember ?? '-' }}</span>
                </div>
              @elseif($moduleKey === 'apab')
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Pressure Gauge</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->pressure_gauge ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Pin & Segel</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->pin_segel ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Selang</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->selang ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Klem Selang</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->klem_selang ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Handle</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->handle ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Kondisi Fisik</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->kondisi_fisik ?? '-' }}</span>
                </div>
              @elseif($moduleKey === 'fire_alarm')
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Panel Kontrol</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->panel_kontrol ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Detector</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->detector ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Manual Call Point</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->manual_call_point ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Alarm Bell</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->alarm_bell ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Battery Backup</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->battery_backup ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Uji Fungsi</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->uji_fungsi ?? '-' }}</span>
                </div>
              @elseif($moduleKey === 'box_hydrant')
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Pilar Hydrant</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->pilar_hydrant ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Box Hydrant</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->box_hydrant ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Nozzle</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->nozzle ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Selang</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->selang ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Uji Fungsi</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->uji_fungsi ?? '-' }}</span>
                </div>
              @elseif($moduleKey === 'rumah_pompa')
                @php
                  $inspectionRows = !empty($kartu->inspection_data) && is_array($kartu->inspection_data)
                    ? $kartu->inspection_data
                    : [
                      ['label' => 'Pompa Utama', 'value' => $kartu->pompa_utama ?? '-'],
                      ['label' => 'Pompa Cadangan', 'value' => $kartu->pompa_cadangan ?? '-'],
                      ['label' => 'Jockey Pump', 'value' => $kartu->jockey_pump ?? '-'],
                      ['label' => 'Panel Kontrol', 'value' => $kartu->panel_kontrol ?? '-'],
                      ['label' => 'Uji Fungsi', 'value' => $kartu->uji_fungsi ?? '-'],
                    ];
                @endphp
                @foreach($inspectionRows as $row)
                  <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                    <span class="text-sm font-medium text-gray-600">{{ $row['label'] ?? ($row['key'] ?? '-') }}</span>
                    <span class="text-sm font-bold text-gray-900">{{ $row['value'] ?? '-' }}</span>
                  </div>
                @endforeach
              @elseif($moduleKey === 'p3k')
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Kotak P3K</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->kotak_p3k ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Plester</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->plester ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Perban</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->perban ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Kasa Steril</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->kasa_steril ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Antiseptik</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->antiseptik ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Gunting</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->gunting ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Sarung Tangan</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->sarung_tangan ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Masker</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->masker ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Obat Luka</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->obat_luka ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100">
                  <span class="text-sm font-medium text-gray-600">Buku Panduan</span>
                  <span class="text-sm font-bold text-gray-900">{{ $kartu->buku_panduan ?? '-' }}</span>
                </div>
              @else
                <div
                  class="col-span-full p-8 text-center text-gray-400 bg-slate-50 rounded-xl border-2 border-dashed border-slate-200">
                  <p>Detail pemeriksaan belum dikonfigurasi untuk modul ini.</p>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>

      {{-- Right Column: Actions --}}
      <div class="space-y-6">

        {{-- Approval Panel --}}
        <div class="bg-white rounded-2xl shadow-lg ring-1 ring-slate-200 overflow-hidden sticky top-6">
          <div class="p-6 bg-gradient-to-br from-white to-slate-50">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Tindakan</h2>
            <p class="text-sm text-gray-500 mb-6">Pilih tanda tangan Anda untuk menyetujui.</p>

            <form action="{{ route('leader.approvals.approve', [$module, $kartu->id]) }}" method="POST">
              @csrf

              <div class="space-y-3 mb-8">
                @forelse($signatures as $signature)
                  <label class="relative block cursor-pointer group">
                    <input type="radio" name="signature_id" value="{{ $signature->id }}" required class="peer sr-only">
                    <div class="p-4 rounded-xl border-2 border-gray-200 bg-white transition-all duration-200 
                                          peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:shadow-md
                                          group-hover:border-green-300">
                      <div class="flex items-center gap-4">
                        <div
                          class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center p-1 border border-gray-200">
                          @if($signature->signature_path)
                            <img src="{{ asset('storage/' . $signature->signature_path) }}"
                              class="max-h-full w-auto object-contain">
                          @else
                            <span class="text-xs text-gray-400">No Img</span>
                          @endif
                        </div>
                        <div class="flex-1 min-w-0">
                          <p class="font-bold text-gray-900 truncate">{{ $signature->name }}</p>
                          <p class="text-xs text-gray-500 truncate">{{ $signature->position }}</p>
                        </div>
                        <div
                          class="w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-green-500 peer-checked:bg-green-500 flex items-center justify-center text-white transition-colors">
                          <svg class="w-3 h-3 opacity-0 peer-checked:opacity-100" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                              clip-rule="evenodd"></path>
                          </svg>
                        </div>
                      </div>
                    </div>
                  </label>
                @empty
                  <div class="text-center py-6 px-4 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                      </path>
                    </svg>
                    <p class="text-sm font-medium text-gray-500">Belum ada tanda tangan.</p>
                    <p class="text-xs text-gray-400">Harap hubungi admin.</p>
                  </div>
                @endforelse
              </div>

              @if($signatures->count() > 0)
                <button type="submit"
                  class="w-full py-3.5 px-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow-lg shadow-green-200 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  Approve Sekarang
                </button>
              @endif
            </form>
          </div>

          <div class="bg-slate-50 p-6 border-t border-slate-200">
            @if($signatures->count() > 0)
              <div x-data="{ openReject: false }">
                <button @click="openReject = !openReject" type="button"
                  class="w-full py-3 px-4 bg-white border border-gray-300 hover:bg-red-50 hover:border-red-200 hover:text-red-700 text-gray-700 font-semibold rounded-xl transition-all flex items-center justify-center gap-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                  </svg>
                  Tolak / Revisi
                </button>

                <div x-show="openReject" x-transition.origin.top class="mt-4 pt-4 border-t border-gray-200">
                  <form action="{{ route('leader.approvals.reject', [$module, $kartu->id]) }}" method="POST"
                    onsubmit="return confirm('Apakah Anda yakin ingin menolak? Revisi akan bertambah.')">
                    @csrf

                    <label class="block text-sm font-bold text-gray-700 mb-2">Alasan Penolakan</label>
                    <textarea name="rejection_reason" rows="3" required
                      class="w-full px-4 py-3 rounded-xl border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                      placeholder="Jelaskan bagian yang perlu diperbaiki..."></textarea>

                    <button type="submit"
                      class="mt-3 w-full py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg shadow transition-colors flex items-center justify-center gap-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                      </svg>
                      Konfirmasi Tolak
                    </button>
                  </form>
                </div>
              </div>
            @endif
          </div>

          <div class="px-6 py-4 bg-gray-100 text-center">
            <a href="{{ route('leader.approvals.index') }}"
              class="text-sm text-gray-500 hover:text-gray-800 font-medium transition-colors">
              Kembali ke Daftar
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-layouts.app>