<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Status Peralatan {{ strtoupper($module) }}: {{ $equipment->serial_no ?? $equipment->name ?? '-' }}">
    <title>Status {{ strtoupper($module) }} | {{ $equipment->serial_no ?? 'Detail' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --pln-blue: #00A3E0;
            --pln-yellow: #FFD100;
            --bg-color: #f4f7fa;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;

            --status-baik: #22c55e;
            --status-rusak: #ef4444;
            --status-warning: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
        }

        .container {
            width: 100%;
            max-width: 450px;
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            padding: 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(to bottom, #ffffff, #fcfcfc);
        }

        .module-logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-bottom: 1.25rem;
            filter: drop-shadow(0 4px 3px rgba(0,0,0,0.07));
        }

        .module-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--pln-blue);
            margin-bottom: 0.5rem;
        }

        .serial-number {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 1rem;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        <?php 
            $statusText = strtoupper($equipment->status ?? 'UNKNOWN'); 
            $statusColor = 'var(--text-muted)';
            $statusBg = '#f1f5f9';
            if (in_array($statusText, ['BAIK', 'AKTIF', 'NORMAL'])) {
                $statusColor = 'var(--status-baik)';
                $statusBg = '#f0fdf4';
            } elseif (in_array($statusText, ['RUSAK', 'MATI', 'ERROR'])) {
                $statusColor = 'var(--status-rusak)';
                $statusBg = '#fef2f2';
            } elseif (in_array($statusText, ['ISI ULANG', 'REPAIR', 'MAINTENANCE'])) {
                $statusColor = 'var(--status-warning)';
                $statusBg = '#fffbeb';
            }
        ?>

        .status-pill {
            color: <?= $statusColor ?>;
            background-color: <?= $statusBg ?>;
            border: 1px solid <?= $statusColor ?>40;
        }

        .content {
            padding: 1.5rem;
        }

        .info-grid {
            display: grid;
            gap: 1rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px dashed var(--border-color);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 0.875rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .info-value {
            font-size: 0.875rem;
            color: var(--text-main);
            font-weight: 600;
        }

        .actions {
            padding: 1.5rem;
            background-color: #f8fafc;
            border-top: 1px solid var(--border-color);
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.875rem;
            border-radius: 10px;
            font-size: 0.9375rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            margin-bottom: 0.75rem;
        }

        .btn-primary {
            background-color: var(--pln-blue);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 163, 224, 0.2);
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .btn-outline {
            background-color: white;
            color: var(--text-main);
            border: 1px solid var(--border-color);
            margin-bottom: 0;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary:active {
            transform: scale(0.98);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
        }

        .btn-success:active {
            transform: scale(0.98);
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-icon svg {
            width: 18px;
            height: 18px;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border-color);
        }

        .divider span {
            padding: 0 0.75rem;
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .pln-logo-small {
            height: 24px;
            margin-bottom: 0.5rem;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            body {
                padding: 1rem 0.5rem;
            }

            .container {
                border-radius: 12px;
            }

            .header {
                padding: 1.5rem 1rem;
            }

            .module-logo {
                width: 80px;
                height: 80px;
            }

            .serial-number {
                font-size: 1.25rem;
            }

            .btn {
                padding: 0.75rem;
                font-size: 0.875rem;
            }

            .btn-icon svg {
                width: 16px;
                height: 16px;
            }

            .actions {
                padding: 1rem;
            }

            .divider span {
                font-size: 0.7rem;
            }
        }

        /* Animation untuk tombol */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.8;
            }
        }

        .btn:hover {
            animation: pulse 1.5s ease-in-out infinite;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <img src="{{ asset('images/' . $module . '.png') }}" alt="{{ $module }}" class="module-logo" onerror="this.src='{{ asset('images/logo1.png') }}'">
            <div class="module-label">{{ $title ?? str_replace('-', ' ', $module) }}</div>
            <h1 class="serial-number">{{ $equipment->serial_no ?? $equipment->name ?? 'Unknown' }}</h1>
            <div class="status-pill">
                {{ $equipment->status ?? 'TANPA STATUS' }}
            </div>
        </div>

        <div class="content">
            <div class="info-grid">
                @if(isset($equipment->location_code) || isset($equipment->lokasi))
                    <div class="info-row">
                        <span class="info-label">Lokasi</span>
                        <span class="info-value">{{ $equipment->location_code ?? $equipment->lokasi ?? '-' }}</span>
                    </div>
                @endif

                @if(isset($equipment->type) || isset($equipment->jenis))
                    <div class="info-row">
                        <span class="info-label">Tipe/Jenis</span>
                        <span class="info-value">{{ $equipment->type ?? $equipment->jenis ?? '-' }}</span>
                    </div>
                @endif

                @if(isset($equipment->capacity) || isset($equipment->kapasitas))
                    <div class="info-row">
                        <span class="info-label">Kapasitas</span>
                        <span class="info-value">{{ $equipment->capacity ?? $equipment->kapasitas ?? '-' }}</span>
                    </div>
                @endif

                @if(isset($equipment->zone))
                    <div class="info-row">
                        <span class="info-label">Zona</span>
                        <span class="info-value">{{ $equipment->zone }}</span>
                    </div>
                @endif
                
                <div class="info-row">
                    <span class="info-label">Terakhir Diperbarui</span>
                    <span class="info-value">{{ $equipment->updated_at ? $equipment->updated_at->diffForHumans() : '-' }}</span>
                </div>
            </div>
        </div>

        <div class="actions">
            <a href="#" onclick="navigateToKartuKendali(event)" class="btn btn-success btn-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Kartu Kendali
            </a>
            <a href="#" onclick="navigateToEdit(event)" class="btn btn-secondary btn-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Data
            </a>
            
            <div class="divider">
                <span>Atau</span>
            </div>
            
            <a href="{{ route('guest.' . $module) }}" class="btn btn-primary">
                Lihat Daftar {{ strtoupper(str_replace('-', ' ', $module)) }}
            </a>
            <a href="{{ route('guest.dashboard') }}" class="btn btn-outline">
                Dashboard Guest
            </a>
        </div>
    </div>

    <div class="footer">
        <img src="{{ asset('images/logo1.png') }}" alt="PLN Logo" class="pln-logo-small">
        <p>Sistem Manajemen Peralatan Terintegrasi</p>
        <p>&copy; {{ date('Y') }} PT PLN (Persero)</p>
    </div>

    <script>
        // Data equipment dari backend
        const equipmentData = {
            id: {{ $equipment->id }},
            module: '{{ $module }}',
            serialNo: '{{ $equipment->serial_no ?? $equipment->name ?? '' }}',
            status: '{{ $equipment->status ?? '' }}'
        };

        // Route mapping untuk create kartu kendali
        const kartuRoutes = {
            'apar': '{{ route('kartu.create') }}?apar_id={{ $equipment->id }}',
            'apat': '{{ route('apat.kartu.create') }}?apat_id={{ $equipment->id }}',
            'apab': '{{ route('apab.kartu.create') }}?apab_id={{ $equipment->id }}',
            'fire-alarm': '{{ route('fire-alarm.kartu.create') }}?fire_alarm_id={{ $equipment->id }}',
            'box-hydrant': '{{ route('box-hydrant.kartu.create') }}?box_hydrant_id={{ $equipment->id }}',
            'rumah-pompa': '{{ route('rumah-pompa.kartu.create') }}?rumah_pompa_id={{ $equipment->id }}',
            'p3k': '{{ route('p3k.kartu.create') }}?p3k_id={{ $equipment->id }}'
        };

        // Route mapping untuk edit
        const editRoutes = {
            'apar': '{{ route('apar.edit', ['apar' => $equipment->id]) }}',
            'apat': '{{ route('apat.edit', ['apat' => $equipment->id]) }}',
            'apab': '{{ route('apab.edit', ['apab' => $equipment->id]) }}',
            'fire-alarm': '{{ route('fire-alarm.edit', ['fireAlarm' => $equipment->id]) }}',
            'box-hydrant': '{{ route('box-hydrant.edit', ['boxHydrant' => $equipment->id]) }}',
            'rumah-pompa': '{{ route('rumah-pompa.edit', ['rumahPompa' => $equipment->id]) }}',
            'p3k': '{{ route('p3k.edit', ['p3k' => $equipment->id]) }}'
        };

        // Flag untuk tracking perubahan data (jika ada form di masa depan)
        let hasUnsavedChanges = false;

        /**
         * Navigasi ke halaman Buat Kartu Kendali
         */
        function navigateToKartuKendali(event) {
            event.preventDefault();
            
            if (hasUnsavedChanges) {
                if (!confirm('⚠️ Anda memiliki perubahan yang belum disimpan. Yakin ingin melanjutkan?')) {
                    return;
                }
            }

            // Konfirmasi navigasi
            const confirmed = confirm(
                `📋 Anda akan membuat Kartu Kendali baru untuk:\n\n` +
                `${equipmentData.serialNo}\n` +
                `Status: ${equipmentData.status}\n\n` +
                `Lanjutkan?`
            );

            if (confirmed) {
                const kartuRoute = kartuRoutes[equipmentData.module];
                if (kartuRoute) {
                    window.location.href = kartuRoute;
                } else {
                    alert('❌ Fitur Kartu Kendali belum tersedia untuk modul ini.');
                }
            }
        }

        /**
         * Navigasi ke halaman Edit
         */
        function navigateToEdit(event) {
            event.preventDefault();
            
            if (hasUnsavedChanges) {
                if (!confirm('⚠️ Anda memiliki perubahan yang belum disimpan. Yakin ingin melanjutkan?')) {
                    return;
                }
            }

            // Konfirmasi navigasi
            const confirmed = confirm(
                `✏️ Anda akan mengedit data:\n\n` +
                `${equipmentData.serialNo}\n` +
                `Status: ${equipmentData.status}\n\n` +
                `Lanjutkan?`
            );

            if (confirmed) {
                const editRoute = editRoutes[equipmentData.module];
                if (editRoute) {
                    window.location.href = editRoute;
                } else {
                    alert('❌ Fitur Edit belum tersedia untuk modul ini.');
                }
            }
        }

        /**
         * Deteksi perubahan pada form (jika ada)
         */
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('change', function() {
                    hasUnsavedChanges = true;
                });
            });

            // Warning sebelum meninggalkan halaman jika ada perubahan
            window.addEventListener('beforeunload', function(e) {
                if (hasUnsavedChanges) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        });
    </script>

</body>
</html>
