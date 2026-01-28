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
        // Add revisi column to all kartu tables
        $tables = [
            'kartu_apars',
            'kartu_apats',
            'kartu_apabs',
            'kartu_fire_alarms',
            'kartu_box_hydrants',
            'kartu_rumah_pompas',
            'kartu_p3ks',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('revisi', 2)->default('00')->after('tgl_periksa');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'kartu_apars',
            'kartu_apats',
            'kartu_apabs',
            'kartu_fire_alarms',
            'kartu_box_hydrants',
            'kartu_rumah_pompas',
            'kartu_p3ks',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('revisi');
            });
        }
    }
};
