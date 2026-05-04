<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pemeriksaan
        Schema::table('kartu_p3k_pemeriksaan', function (Blueprint $table) {
            $table->string('nomor_kartu', 30)->nullable()->after('id');
            $table->unsignedBigInteger('unit_id')->nullable()->after('nomor_kartu');
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });

        // Pemakaian
        Schema::table('kartu_p3k_pemakaian', function (Blueprint $table) {
            $table->string('nomor_kartu', 30)->nullable()->after('id');
            $table->unsignedBigInteger('unit_id')->nullable()->after('nomor_kartu');
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });

        // Stock
        Schema::table('kartu_p3k_stock', function (Blueprint $table) {
            $table->string('nomor_kartu', 30)->nullable()->after('id');
            $table->unsignedBigInteger('unit_id')->nullable()->after('nomor_kartu');
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kartu_p3k_pemeriksaan', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['nomor_kartu', 'unit_id']);
        });

        Schema::table('kartu_p3k_pemakaian', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['nomor_kartu', 'unit_id']);
        });

        Schema::table('kartu_p3k_stock', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['nomor_kartu', 'unit_id']);
        });
    }
};
