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
        // Add rejection tracking columns to all kartu tables
        $tables = [
            'kartu_apars',
            'kartu_apats',
            'kartu_apabs',
            'kartu_fire_alarms',
            'kartu_box_hydrants',
            'kartu_rumah_pompas',
            'kartu_p3ks',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Rejection tracking fields
                $table->foreignId('rejected_by')->nullable()->after('approved_at')->constrained('users')->onDelete('set null');
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
                $table->text('rejection_reason')->nullable()->after('rejected_at');
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

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['rejected_by']);
                $table->dropColumn(['rejected_by', 'rejected_at', 'rejection_reason']);
            });
        }
    }
};
