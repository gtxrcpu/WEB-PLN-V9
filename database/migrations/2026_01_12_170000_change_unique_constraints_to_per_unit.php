<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Change unique constraints from global to per-unit composite unique
     * This allows each unit to have independent serial/barcode numbering
     */
    public function up(): void
    {
        // Tables to update
        $tables = ['apars', 'fire_alarms', 'box_hydrants', 'rumah_pompas', 'p3ks', 'apabs', 'apats'];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Drop existing unique constraints on barcode and serial_no
                try {
                    // Different naming conventions for unique indexes
                    $possibleBarcodeIndexNames = [
                        "{$tableName}_barcode_unique",
                        "barcode_unique",
                        "{$tableName}s_barcode_unique" // with 's'
                    ];

                    foreach ($possibleBarcodeIndexNames as $indexName) {
                        try {
                            DB::statement("ALTER TABLE {$tableName} DROP INDEX {$indexName}");
                            echo "Dropped index: {$indexName} from {$tableName}\n";
                            break; // Exit loop if successful
                        } catch (\Exception $e) {
                            // Index doesn't exist, continue
                        }
                    }
                } catch (\Exception $e) {
                    // Index doesn't exist, continue
                }

                try {
                    $possibleSerialIndexNames = [
                        "{$tableName}_serial_no_unique",
                        "serial_no_unique",
                        "{$tableName}s_serial_no_unique"
                    ];

                    foreach ($possibleSerialIndexNames as $indexName) {
                        try {
                            DB::statement("ALTER TABLE {$tableName} DROP INDEX {$indexName}");
                            echo "Dropped index: {$indexName} from {$tableName}\n";
                            break;
                        } catch (\Exception $e) {
                            // Index doesn't exist, continue
                        }
                    }
                } catch (\Exception $e) {
                    // Index doesn't exist, continue
                }

                // Add composite unique constraints (barcode + unit_id)
                // This allows same barcode/serial across different units
                if (Schema::hasColumn($tableName, 'barcode') && Schema::hasColumn($tableName, 'unit_id')) {
                    try {
                        $table->unique(['barcode', 'unit_id'], "{$tableName}_barcode_unit_unique");
                        echo "Created composite unique: barcode+unit_id on {$tableName}\n";
                    } catch (\Exception $e) {
                        echo "Could not create barcode+unit_id unique on {$tableName}: " . $e->getMessage() . "\n";
                    }
                }

                if (Schema::hasColumn($tableName, 'serial_no') && Schema::hasColumn($tableName, 'unit_id')) {
                    try {
                        $table->unique(['serial_no', 'unit_id'], "{$tableName}_serial_unit_unique");
                        echo "Created composite unique: serial_no+unit_id on {$tableName}\n";
                    } catch (\Exception $e) {
                        echo "Could not create serial_no+unit_id unique on {$tableName}: " . $e->getMessage() . "\n";
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['apars', 'fire_alarms', 'box_hydrants', 'rumah_pompas', 'p3ks', 'apabs', 'apats'];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Drop composite unique constraints
                try {
                    $table->dropUnique("{$tableName}_barcode_unit_unique");
                } catch (\Exception $e) {
                    // Doesn't exist
                }

                try {
                    $table->dropUnique("{$tableName}_serial_unit_unique");
                } catch (\Exception $e) {
                    // Doesn't exist
                }

                // Restore original unique constraints (global)
                if (Schema::hasColumn($tableName, 'barcode')) {
                    try {
                        $table->unique('barcode');
                    } catch (\Exception $e) {
                        // Already exists
                    }
                }

                if (Schema::hasColumn($tableName, 'serial_no')) {
                    try {
                        $table->unique('serial_no');
                    } catch (\Exception $e) {
                        // Already exists
                    }
                }
            });
        }
    }
};
