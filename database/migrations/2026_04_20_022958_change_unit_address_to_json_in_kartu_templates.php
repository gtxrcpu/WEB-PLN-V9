<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clear old text data (akan diisi ulang melalui form edit)
        DB::table('kartu_templates')->update(['unit_address' => null]);
        
        // Change column type to JSON
        Schema::table('kartu_templates', function (Blueprint $table) {
            $table->json('unit_address')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kartu_templates', function (Blueprint $table) {
            $table->text('unit_address')->nullable()->change();
        });
    }
};
