<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kartu_p3k_pemakaian', function (Blueprint $table) {
            // Add new columns for the updated pemakaian format
            $table->string('bulan')->nullable()->after('user_id');
            $table->string('nomor')->nullable()->after('bulan');
            $table->string('lokasi')->nullable()->after('nomor');
            $table->json('usage_entries')->nullable()->after('lokasi');

            // Make old fields nullable for backward compatibility
            $table->foreignId('p3k_id')->nullable()->change();
            $table->string('item_digunakan')->nullable()->change();
            $table->integer('jumlah')->nullable()->change();
            $table->string('kesimpulan')->nullable()->change();
            $table->date('tgl_pemakaian')->nullable()->change();
            $table->string('petugas')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('kartu_p3k_pemakaian', function (Blueprint $table) {
            $table->dropColumn(['bulan', 'nomor', 'lokasi', 'usage_entries']);
        });
    }
};
