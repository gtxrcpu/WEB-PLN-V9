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
        Schema::table('apats', function (Blueprint $table) {
            // Drop 'name' column - not needed anymore (using serial_no as name)
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apats', function (Blueprint $table) {
            // Restore 'name' column if rollback
            $table->string('name')->nullable()->after('user_id');
        });
    }
};
