<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi: Tambah UNIQUE INDEX pada kolom nomor_kartu di tiga tabel kartu P3K.
 *
 * Tujuan:
 *   - Mencegah duplikasi nomor kartu di level database
 *   - Memastikan pemisahan kode yang bersih antar jenis transaksi:
 *       kartu_p3k_pemeriksaan  → prefix P3K-PMKS-{UNIT}-{NNN}
 *       kartu_p3k_pemakaian    → prefix P3K-PMK-{UNIT}-{NNN}
 *       kartu_p3k_stock        → prefix P3K-STK-{UNIT}-{NNN}
 *
 * Sebelum menjalankan migrasi ini, jalankan perintah Tinker berikut
 * untuk membersihkan data lama yang menggunakan format kode yang salah:
 *
 *   App\Http\Controllers\KartuP3kController::backfillNomorKartu();
 *   App\Http\Controllers\KartuP3kController::fixLegacyNomorKartu();
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Bersihkan nomor_kartu duplikat sebelum membuat unique index ──
        $this->deduplicateNomorKartu('kartu_p3k_pemeriksaan');
        $this->deduplicateNomorKartu('kartu_p3k_pemakaian');
        $this->deduplicateNomorKartu('kartu_p3k_stock');

        // ── 2. Tambah UNIQUE INDEX pada nomor_kartu ──────────────────────────
        Schema::table('kartu_p3k_pemeriksaan', function (Blueprint $table) {
            // Hanya buat index jika belum ada
            if (!$this->indexExists('kartu_p3k_pemeriksaan', 'kartu_p3k_pemeriksaan_nomor_kartu_unique')) {
                $table->string('nomor_kartu', 30)->nullable()->change();
                $table->unique('nomor_kartu', 'kartu_p3k_pemeriksaan_nomor_kartu_unique');
            }
        });

        Schema::table('kartu_p3k_pemakaian', function (Blueprint $table) {
            if (!$this->indexExists('kartu_p3k_pemakaian', 'kartu_p3k_pemakaian_nomor_kartu_unique')) {
                $table->string('nomor_kartu', 30)->nullable()->change();
                $table->unique('nomor_kartu', 'kartu_p3k_pemakaian_nomor_kartu_unique');
            }
        });

        Schema::table('kartu_p3k_stock', function (Blueprint $table) {
            if (!$this->indexExists('kartu_p3k_stock', 'kartu_p3k_stock_nomor_kartu_unique')) {
                $table->string('nomor_kartu', 30)->nullable()->change();
                $table->unique('nomor_kartu', 'kartu_p3k_stock_nomor_kartu_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kartu_p3k_pemeriksaan', function (Blueprint $table) {
            $table->dropUnique('kartu_p3k_pemeriksaan_nomor_kartu_unique');
        });

        Schema::table('kartu_p3k_pemakaian', function (Blueprint $table) {
            $table->dropUnique('kartu_p3k_pemakaian_nomor_kartu_unique');
        });

        Schema::table('kartu_p3k_stock', function (Blueprint $table) {
            $table->dropUnique('kartu_p3k_stock_nomor_kartu_unique');
        });
    }

    /**
     * Hapus nomor_kartu duplikat dengan cara set ke NULL agar unique index bisa dibuat.
     * Record dengan nomor_kartu duplikat yang lebih baru (id lebih besar) akan direset.
     */
    private function deduplicateNomorKartu(string $table): void
    {
        // Temukan nomor_kartu yang muncul lebih dari sekali
        $duplicates = DB::table($table)
            ->select('nomor_kartu', DB::raw('MIN(id) as first_id'), DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('nomor_kartu')
            ->groupBy('nomor_kartu')
            ->having('cnt', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            // Set nomor_kartu = NULL untuk semua record duplikat kecuali yang pertama (id terkecil)
            DB::table($table)
                ->where('nomor_kartu', $dup->nomor_kartu)
                ->where('id', '!=', $dup->first_id)
                ->update(['nomor_kartu' => null]);
        }
    }

    /**
     * Cek apakah index dengan nama tertentu sudah ada di tabel.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
