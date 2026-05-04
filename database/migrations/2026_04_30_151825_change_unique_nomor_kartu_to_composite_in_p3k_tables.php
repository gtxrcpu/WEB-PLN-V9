<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kartu_p3k_pemeriksaan', function (Blueprint $table) {
            $table->dropUnique('kartu_p3k_pemeriksaan_nomor_kartu_unique');
            $table->unique(['nomor_kartu', 'unit_id'], 'pemeriksaan_nomor_unit_unique');
        });

        Schema::table('kartu_p3k_pemakaian', function (Blueprint $table) {
            $table->dropUnique('kartu_p3k_pemakaian_nomor_kartu_unique');
            $table->unique(['nomor_kartu', 'unit_id'], 'pemakaian_nomor_unit_unique');
        });

        Schema::table('kartu_p3k_stock', function (Blueprint $table) {
            $table->dropUnique('kartu_p3k_stock_nomor_kartu_unique');
            $table->unique(['nomor_kartu', 'unit_id'], 'stock_nomor_unit_unique');
        });
    }

    public function down(): void
    {
        Schema::table('kartu_p3k_pemeriksaan', function (Blueprint $table) {
            $table->dropUnique('pemeriksaan_nomor_unit_unique');
            $table->unique('nomor_kartu', 'kartu_p3k_pemeriksaan_nomor_kartu_unique');
        });

        Schema::table('kartu_p3k_pemakaian', function (Blueprint $table) {
            $table->dropUnique('pemakaian_nomor_unit_unique');
            $table->unique('nomor_kartu', 'kartu_p3k_pemakaian_nomor_kartu_unique');
        });

        Schema::table('kartu_p3k_stock', function (Blueprint $table) {
            $table->dropUnique('stock_nomor_unit_unique');
            $table->unique('nomor_kartu', 'kartu_p3k_stock_nomor_kartu_unique');
        });
    }
};
