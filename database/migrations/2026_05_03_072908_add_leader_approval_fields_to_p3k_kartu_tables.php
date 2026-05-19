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
        $tables = [
            'kartu_p3k_pemeriksaan',
            'kartu_p3k_pemakaian',
            'kartu_p3k_stock',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'leader_signature_id')) {
                    $table->unsignedBigInteger('leader_signature_id')->nullable()->after('signature_id');
                }
                if (! Schema::hasColumn($tableName, 'leader_approved_by')) {
                    $table->unsignedBigInteger('leader_approved_by')->nullable()->after('leader_signature_id');
                }
                if (! Schema::hasColumn($tableName, 'leader_approved_at')) {
                    $table->timestamp('leader_approved_at')->nullable()->after('leader_approved_by');
                }

                if (! Schema::hasColumn($tableName, 'leader_rejected_by')) {
                    $table->unsignedBigInteger('leader_rejected_by')->nullable()->after('leader_approved_at');
                }
                if (! Schema::hasColumn($tableName, 'leader_rejected_at')) {
                    $table->timestamp('leader_rejected_at')->nullable()->after('leader_rejected_by');
                }
                if (! Schema::hasColumn($tableName, 'leader_rejection_reason')) {
                    $table->text('leader_rejection_reason')->nullable()->after('leader_rejected_at');
                }
                
                if (! Schema::hasColumn($tableName, 'rejected_by')) {
                    $table->unsignedBigInteger('rejected_by')->nullable()->after('leader_rejection_reason');
                }
                if (! Schema::hasColumn($tableName, 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('rejected_by');
                }
                if (! Schema::hasColumn($tableName, 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('rejected_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'kartu_p3k_pemeriksaan',
            'kartu_p3k_pemakaian',
            'kartu_p3k_stock',
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
                if (Schema::hasColumn($tableName, 'rejected_by')) {
                    $table->dropColumn('rejected_by');
                }
                if (Schema::hasColumn($tableName, 'rejected_at')) {
                    $table->dropColumn('rejected_at');
                }
                if (Schema::hasColumn($tableName, 'rejection_reason')) {
                    $table->dropColumn('rejection_reason');
                }
            });
        }
    }
};
