<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds pengawas column to kartu_apars table for consistency with other kartu tables.
     */
    public function up(): void
    {
        Schema::table('kartu_apars', function (Blueprint $table) {
            $table->string('pengawas')->nullable()->after('petugas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kartu_apars', function (Blueprint $table) {
            $table->dropColumn('pengawas');
        });
    }
};
