<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kartu_templates', function (Blueprint $table) {
            // Tambah kolom unit_id untuk relasi ke tabel units
            $table->foreignId('unit_id')->nullable()->after('module')->constrained('units')->onDelete('cascade');
            
            // Tambah kolom unit_address untuk menyimpan alamat spesifik per unit
            $table->text('unit_address')->nullable()->after('company_address')->comment('Alamat perusahaan spesifik untuk unit ini');
            
            // Tambah index untuk performa query
            $table->index(['module', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kartu_templates', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropIndex(['module', 'unit_id']);
            $table->dropColumn(['unit_id', 'unit_address']);
        });
    }
};
