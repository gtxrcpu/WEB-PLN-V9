<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupDuplicateUnits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'units:cleanup-duplicates {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate units and fix user roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
        }
        
        $this->info('=== UNIT CLEANUP AUDIT ===');
        $this->newLine();
        
        // Step 1: Show current state
        $this->info('📊 Current Units:');
        $units = Unit::orderBy('code')->get();
        
        $headers = ['ID', 'Code', 'Name', 'Active', 'Users Count'];
        $rows = [];
        
        foreach ($units as $unit) {
            $rows[] = [
                $unit->id,
                $unit->code,
                $unit->name,
                $unit->is_active ? '✓' : '✗',
                $unit->users()->count()
            ];
        }
        
        $this->table($headers, $rows);
        $this->newLine();
        
        // Step 2: Find duplicates
        $this->info('🔍 Checking for duplicates...');
        $duplicates = Unit::select('code', DB::raw('COUNT(*) as count'))
            ->groupBy('code')
            ->having('count', '>', 1)
            ->get();
        
        if ($duplicates->count() === 0) {
            $this->info('✅ No duplicate units found!');
        } else {
            $this->warn('⚠️  Found ' . $duplicates->count() . ' duplicate unit codes:');
            
            foreach ($duplicates as $dup) {
                $this->warn("  - Code: {$dup->code} (appears {$dup->count} times)");
                
                $unitsWithCode = Unit::where('code', $dup->code)->get();
                foreach ($unitsWithCode as $u) {
                    $this->line("    ID: {$u->id}, Name: {$u->name}, Users: {$u->users()->count()}");
                }
                
                if (!$dryRun) {
                    // Keep the first one, merge others
                    $keepUnit = $unitsWithCode->first();
                    $deleteUnits = $unitsWithCode->slice(1);
                    
                    $this->info("    → Keeping ID: {$keepUnit->id}");
                    
                    foreach ($deleteUnits as $deleteUnit) {
                        $this->info("    → Merging ID: {$deleteUnit->id} into {$keepUnit->id}");
                        
                        // Update users
                        $userCount = User::where('unit_id', $deleteUnit->id)->count();
                        if ($userCount > 0) {
                            User::where('unit_id', $deleteUnit->id)->update(['unit_id' => $keepUnit->id]);
                            $this->line("      ✓ Moved {$userCount} users");
                        }
                        
                        // Update equipment
                        $this->updateEquipmentReferences($deleteUnit->id, $keepUnit->id);
                        
                        // Delete duplicate
                        $deleteUnit->delete();
                        $this->line("      ✓ Deleted duplicate unit");
                        
                        Log::info("Merged unit {$deleteUnit->id} into {$keepUnit->id}");
                    }
                }
            }
        }
        
        $this->newLine();
        
        // Step 3: Check user roles
        $this->info('👥 Checking user roles...');
        
        $usersWithWrongRole = User::whereHas('roles', function($query) {
            $query->where('name', 'leader');
        })->where('position', 'petugas')->get();
        
        if ($usersWithWrongRole->count() > 0) {
            $this->warn("⚠️  Found {$usersWithWrongRole->count()} users with incorrect roles:");
            
            foreach ($usersWithWrongRole as $user) {
                $roles = $user->roles->pluck('name')->implode(', ');
                $this->warn("  - ID: {$user->id}, Email: {$user->email}, Position: {$user->position}, Roles: {$roles}");
                
                if (!$dryRun) {
                    // Remove leader role
                    $user->removeRole('leader');
                    
                    // Add petugas and user roles
                    if (!$user->hasRole('petugas')) {
                        $user->assignRole('petugas');
                    }
                    if (!$user->hasRole('user')) {
                        $user->assignRole('user');
                    }
                    
                    $this->info("    ✓ Fixed roles for {$user->email}");
                    Log::info("Fixed roles for user {$user->id}: {$user->email}");
                }
            }
        } else {
            $this->info('✅ All user roles are correct!');
        }
        
        $this->newLine();
        
        // Step 4: Final summary
        if (!$dryRun) {
            $this->info('=== CLEANUP COMPLETED ===');
            $finalUnits = Unit::orderBy('code')->get();
            $this->info("Total units: {$finalUnits->count()}");
            
            foreach ($finalUnits as $unit) {
                $this->line("  - {$unit->code}: {$unit->name} ({$unit->users()->count()} users)");
            }
        } else {
            $this->warn('=== DRY RUN COMPLETED ===');
            $this->info('Run without --dry-run to apply changes');
        }
        
        return 0;
    }
    
    /**
     * Update equipment references
     */
    private function updateEquipmentReferences($oldUnitId, $newUnitId)
    {
        $tables = ['apars', 'apats', 'apabs', 'fire_alarms', 'box_hydrants', 'rumah_pompas', 'p3ks', 'cctvs'];
        $totalUpdated = 0;
        
        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table) && DB::getSchemaBuilder()->hasColumn($table, 'unit_id')) {
                $count = DB::table($table)->where('unit_id', $oldUnitId)->count();
                if ($count > 0) {
                    DB::table($table)->where('unit_id', $oldUnitId)->update(['unit_id' => $newUnitId]);
                    $totalUpdated += $count;
                    $this->line("      ✓ Updated {$count} records in {$table}");
                }
            }
        }
        
        return $totalUpdated;
    }
}
