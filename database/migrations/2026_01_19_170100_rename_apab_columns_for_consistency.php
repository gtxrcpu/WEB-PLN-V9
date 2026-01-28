<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('apabs', function (Blueprint $table) {
            // Rename columns to match APAT naming scheme
            $table->renameColumn('location_code', 'lokasi');
            $table->renameColumn('isi_apab', 'jenis');
            $table->renameColumn('capacity', 'kapasitas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apabs', function (Blueprint $table) {
            // Revert column names
            $table->renameColumn('lokasi', 'location_code');
            $table->renameColumn('jenis', 'isi_apab');
            $table->renameColumn('kapasitas', 'capacity');
        });
    }
};
