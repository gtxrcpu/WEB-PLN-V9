<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'leader_signature_id')) {
                    $table->unsignedBigInteger('leader_signature_id')->nullable();
                }
                if (! Schema::hasColumn($tableName, 'leader_approved_by')) {
                    $table->unsignedBigInteger('leader_approved_by')->nullable();
                }
                if (! Schema::hasColumn($tableName, 'leader_approved_at')) {
                    $table->timestamp('leader_approved_at')->nullable();
                }

                if (! Schema::hasColumn($tableName, 'leader_rejected_by')) {
                    $table->unsignedBigInteger('leader_rejected_by')->nullable();
                }
                if (! Schema::hasColumn($tableName, 'leader_rejected_at')) {
                    $table->timestamp('leader_rejected_at')->nullable();
                }
                if (! Schema::hasColumn($tableName, 'leader_rejection_reason')) {
                    $table->text('leader_rejection_reason')->nullable();
                }
            });
        }
    }

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
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'leader_signature_id')) {
                    $table->dropColumn('leader_signature_id');
                }
                if (Schema::hasColumn($tableName, 'leader_approved_by')) {
                    $table->dropColumn('leader_approved_by');
                }
                if (Schema::hasColumn($tableName, 'leader_approved_at')) {
                    $table->dropColumn('leader_approved_at');
                }
                if (Schema::hasColumn($tableName, 'leader_rejected_by')) {
                    $table->dropColumn('leader_rejected_by');
                }
                if (Schema::hasColumn($tableName, 'leader_rejected_at')) {
                    $table->dropColumn('leader_rejected_at');
                }
                if (Schema::hasColumn($tableName, 'leader_rejection_reason')) {
                    $table->dropColumn('leader_rejection_reason');
                }
            });
        }
    }
};
