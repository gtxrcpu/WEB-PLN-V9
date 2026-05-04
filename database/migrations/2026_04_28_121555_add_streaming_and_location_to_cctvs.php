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
        Schema::table('cctvs', function (Blueprint $table) {
            $table->string('stream_url')->nullable()->after('location_code');
            $table->boolean('is_online')->default(false)->after('stream_url');
            $table->timestamp('last_seen_at')->nullable()->after('is_online');
            $table->foreignId('floor_plan_id')->nullable()->after('last_seen_at')->constrained('floor_plans')->nullOnDelete();
            $table->float('floor_plan_x')->nullable()->after('floor_plan_id');
            $table->float('floor_plan_y')->nullable()->after('floor_plan_x');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cctvs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('floor_plan_id');
            $table->dropColumn(['stream_url', 'is_online', 'last_seen_at', 'floor_plan_x', 'floor_plan_y']);
        });
    }
};
