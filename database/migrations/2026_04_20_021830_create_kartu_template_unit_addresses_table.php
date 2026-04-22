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
        Schema::create('kartu_template_unit_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kartu_template_id')->constrained('kartu_templates')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->text('address');
            $table->timestamps();
            
            // Unique constraint: satu template hanya bisa punya satu alamat per unit
            $table->unique(['kartu_template_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kartu_template_unit_addresses');
    }
};
