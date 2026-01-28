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
        // Safely add rejection fields - check if column exists first

        // kartu_apabs
        Schema::table('kartu_apabs', function (Blueprint $table) {
            if (!Schema::hasColumn('kartu_apabs', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('kartu_apabs', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('kartu_apabs', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
        });

        // kartu_fire_alarms
        Schema::table('kartu_fire_alarms', function (Blueprint $table) {
            if (!Schema::hasColumn('kartu_fire_alarms', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('kartu_fire_alarms', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('kartu_fire_alarms', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
        });

        // kartu_box_hydrants
        Schema::table('kartu_box_hydrants', function (Blueprint $table) {
            if (!Schema::hasColumn('kartu_box_hydrants', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('kartu_box_hydrants', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('kartu_box_hydrants', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
        });

        // kartu_rumah_pompas
        Schema::table('kartu_rumah_pompas', function (Blueprint $table) {
            if (!Schema::hasColumn('kartu_rumah_pompas', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('kartu_rumah_pompas', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('kartu_rumah_pompas', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
        });

        // kartu_p3ks
        Schema::table('kartu_p3ks', function (Blueprint $table) {
            if (!Schema::hasColumn('kartu_p3ks', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('kartu_p3ks', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('kartu_p3ks', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kartu_apabs', function (Blueprint $table) {
            if (Schema::hasColumn('kartu_apabs', 'rejected_by')) {
                $table->dropColumn('rejected_by');
            }
            if (Schema::hasColumn('kartu_apabs', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
            if (Schema::hasColumn('kartu_apabs', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });

        Schema::table('kartu_fire_alarms', function (Blueprint $table) {
            if (Schema::hasColumn('kartu_fire_alarms', 'rejected_by')) {
                $table->dropColumn('rejected_by');
            }
            if (Schema::hasColumn('kartu_fire_alarms', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
            if (Schema::hasColumn('kartu_fire_alarms', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });

        Schema::table('kartu_box_hydrants', function (Blueprint $table) {
            if (Schema::hasColumn('kartu_box_hydrants', 'rejected_by')) {
                $table->dropColumn('rejected_by');
            }
            if (Schema::hasColumn('kartu_box_hydrants', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
            if (Schema::hasColumn('kartu_box_hydrants', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });

        Schema::table('kartu_rumah_pompas', function (Blueprint $table) {
            if (Schema::hasColumn('kartu_rumah_pompas', 'rejected_by')) {
                $table->dropColumn('rejected_by');
            }
            if (Schema::hasColumn('kartu_rumah_pompas', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
            if (Schema::hasColumn('kartu_rumah_pompas', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });

        Schema::table('kartu_p3ks', function (Blueprint $table) {
            if (Schema::hasColumn('kartu_p3ks', 'rejected_by')) {
                $table->dropColumn('rejected_by');
            }
            if (Schema::hasColumn('kartu_p3ks', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
            if (Schema::hasColumn('kartu_p3ks', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};
