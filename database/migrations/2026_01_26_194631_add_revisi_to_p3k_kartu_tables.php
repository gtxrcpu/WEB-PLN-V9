<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add revisi to pemeriksaan table
        Schema::table('kartu_p3k_pemeriksaan', function (Blueprint $table) {
            $table->string('revisi', 10)->default('00')->after('bulan_tahun');
        });

        // Add revisi to pemakaian table
        Schema::table('kartu_p3k_pemakaian', function (Blueprint $table) {
            $table->string('revisi', 10)->default('00')->after('lokasi');
        });
    }

    public function down(): void
    {
        Schema::table('kartu_p3k_pemeriksaan', function (Blueprint $table) {
            $table->dropColumn('revisi');
        });

        Schema::table('kartu_p3k_pemakaian', function (Blueprint $table) {
            $table->dropColumn('revisi');
        });
    }
};
