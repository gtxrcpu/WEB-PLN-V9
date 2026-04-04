<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Log all changes
        $logFile = storage_path('logs/unit_cleanup_' . date('Y-m-d_His') . '.log');
        
        Log::info('=== STARTING UNIT DATA CLEANUP ===');
        
        // Step 1: Identify and log all units before cleanup
        $allUnits = DB::table('units')->orderBy('code')->get();
        Log::info('Total units before cleanup: ' . $allUnits->count());
        
        foreach ($allUnits as $unit) {
            Log::info("Unit ID: {$unit->id}, Code: {$unit->code}, Name: {$unit->name}");
        }
        
        // Step 2: Find duplicate units by code
        $duplicates = DB::table('units')
            ->select('code', DB::raw('COUNT(*) as count'), DB::raw('GROUP_CONCAT(id) as ids'))
            ->groupBy('code')
            ->having('count', '>', 1)
            ->get();
        
        if ($duplicates->count() > 0) {
            Log::info('Found ' . $duplicates->count() . ' duplicate unit codes');
            
            foreach ($duplicates as $dup) {
                $ids = explode(',', $dup->ids);
                Log::info("Duplicate code: {$dup->code}, IDs: " . implode(', ', $ids));
                
                // Keep the first ID (oldest), mark others for deletion
                $keepId = $ids[0];
                $deleteIds = array_slice($ids, 1);
                
                Log::info("  Keeping ID: {$keepId}");
                Log::info("  Will delete IDs: " . implode(', ', $deleteIds));
                
                // Update references before deleting
                foreach ($deleteIds as $deleteId) {
                    // Update users that reference this unit
                    $affectedUsers = DB::table('users')
                        ->where('unit_id', $deleteId)
                        ->update(['unit_id' => $keepId]);
                    
                    if ($affectedUsers > 0) {
                        Log::info("  Updated {$affectedUsers} users from unit_id {$deleteId} to {$keepId}");
                    }
                    
                    // Update equipment tables
                    $tables = ['apars', 'apats', 'apabs', 'fire_alarms', 'box_hydrants', 'rumah_pompas', 'p3ks', 'cctvs'];
                    
                    foreach ($tables as $table) {
                        if (Schema::hasTable($table) && Schema::hasColumn($table, 'unit_id')) {
                            $affected = DB::table($table)
                                ->where('unit_id', $deleteId)
                                ->update(['unit_id' => $keepId]);
                            
                            if ($affected > 0) {
                                Log::info("  Updated {$affected} records in {$table} from unit_id {$deleteId} to {$keepId}");
                            }
                        }
                    }
                    
                    // Delete the duplicate unit
                    DB::table('units')->where('id', $deleteId)->delete();
                    Log::info("  Deleted duplicate unit ID: {$deleteId}");
                }
            }
        } else {
            Log::info('No duplicate units found');
        }
        
        // Step 3: Ensure unique constraint on code
        if (!Schema::hasColumn('units', 'code')) {
            Log::warning('Units table does not have code column');
        } else {
            // Check if unique index already exists
            $indexes = DB::select("SHOW INDEX FROM units WHERE Column_name = 'code' AND Non_unique = 0");
            
            if (empty($indexes)) {
                Schema::table('units', function (Blueprint $table) {
                    $table->unique('code');
                });
                Log::info('Added unique constraint on units.code');
            } else {
                Log::info('Unique constraint on units.code already exists');
            }
        }
        
        // Step 4: Log final state
        $finalUnits = DB::table('units')->orderBy('code')->get();
        Log::info('Total units after cleanup: ' . $finalUnits->count());
        
        foreach ($finalUnits as $unit) {
            Log::info("Final Unit ID: {$unit->id}, Code: {$unit->code}, Name: {$unit->name}");
        }
        
        Log::info('=== UNIT DATA CLEANUP COMPLETED ===');
        Log::info('Check log file: ' . $logFile);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove unique constraint if it was added
        Schema::table('units', function (Blueprint $table) {
            $table->dropUnique(['code']);
        });
        
        Log::info('Removed unique constraint from units.code');
    }
};
