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
        Schema::create('reference_videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('video_path'); // Path to uploaded video
            $table->string('thumbnail_path')->nullable(); // Path to thumbnail
            $table->unsignedBigInteger('unit_id')->nullable(); // null = available to all units
            $table->unsignedBigInteger('created_by'); // Who uploaded
            $table->timestamps();

            // Foreign keys
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index('unit_id');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reference_videos');
    }
};
