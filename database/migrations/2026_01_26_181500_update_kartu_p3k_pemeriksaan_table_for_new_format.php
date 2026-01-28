<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kartu_p3k_pemeriksaan', function (Blueprint $table) {
            // Add new columns for the updated pemeriksaan format
            $table->string('unit_kerja')->nullable()->after('user_id');
            $table->string('bulan_tahun')->nullable()->after('tgl_periksa');
            $table->json('inspection_items')->nullable()->after('checklist_items');

            // Make p3k_id nullable since pemeriksaan might not be linked to specific P3K box
            $table->foreignId('p3k_id')->nullable()->change();

            // Make old fields nullable for backward compatibility
            $table->json('checklist_items')->nullable()->change();
            $table->string('kesimpulan')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('kartu_p3k_pemeriksaan', function (Blueprint $table) {
            $table->dropColumn(['unit_kerja', 'bulan_tahun', 'inspection_items']);
        });
    }
};
