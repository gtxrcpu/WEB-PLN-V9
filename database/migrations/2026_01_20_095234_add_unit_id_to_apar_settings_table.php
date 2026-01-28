<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check and drop unique constraint on 'key' if it exists
        $constraintExists = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'apar_settings' 
            AND CONSTRAINT_NAME = 'apar_settings_key_unique'
        ");

        if (!empty($constraintExists)) {
            DB::statement('ALTER TABLE apar_settings DROP INDEX apar_settings_key_unique');
        }

        Schema::table('apar_settings', function (Blueprint $table) {
            // Add unit_id column
            $table->unsignedBigInteger('unit_id')->nullable()->after('id');

            // Add foreign key to units table
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');

            // Add index for faster queries
            $table->index(['key', 'unit_id']);

            // Add composite unique constraint for (key, unit_id)
            $table->unique(['key', 'unit_id'], 'apar_settings_key_unit_unique');
        });

        // Duplicate existing settings for each unit
        $units = DB::table('units')->get();
        $existingSettings = DB::table('apar_settings')->whereNull('unit_id')->get();

        foreach ($units as $unit) {
            foreach ($existingSettings as $setting) {
                DB::table('apar_settings')->insert([
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'type' => $setting->type,
                    'description' => $setting->description,
                    'unit_id' => $unit->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Delete old global settings (optional - keep them as fallback)
        // DB::table('apar_settings')->whereNull('unit_id')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apar_settings', function (Blueprint $table) {
            // Drop composite unique constraint
            $table->dropUnique('apar_settings_key_unit_unique');

            // Drop foreign key and index
            $table->dropForeign(['unit_id']);
            $table->dropIndex(['key', 'unit_id']);

            // Drop unit_id column
            $table->dropColumn('unit_id');

            // Restore unique constraint on key
            $table->unique('key');
        });
    }
};
