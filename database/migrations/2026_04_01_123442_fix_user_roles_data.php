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
        Log::info('=== STARTING USER ROLES CLEANUP ===');
        
        // Step 1: Log all users and their roles before cleanup
        $users = DB::table('users')->get();
        Log::info('Total users: ' . $users->count());
        
        foreach ($users as $user) {
            $roles = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->where('model_has_roles.model_id', $user->id)
                ->pluck('roles.name')
                ->toArray();
            
            Log::info("User ID: {$user->id}, Email: {$user->email}, Name: {$user->name}, Roles: " . implode(', ', $roles));
        }
        
        // Step 2: Find users with 'leader' role that should be 'petugas'
        // Based on the requirement: "user dengan email yang bersangkutan memiliki role 'petugas' bukan 'leader'"
        // We need to identify which users need role correction
        
        // Get role IDs
        $leaderRoleId = DB::table('roles')->where('name', 'leader')->value('id');
        $petugasRoleId = DB::table('roles')->where('name', 'petugas')->value('id');
        $userRoleId = DB::table('roles')->where('name', 'user')->value('id');
        
        if (!$leaderRoleId || !$petugasRoleId) {
            Log::warning('Leader or Petugas role not found in database');
            return;
        }
        
        Log::info("Leader Role ID: {$leaderRoleId}");
        Log::info("Petugas Role ID: {$petugasRoleId}");
        if ($userRoleId) {
            Log::info("User Role ID: {$userRoleId}");
        }
        
        // Step 3: Find users with incorrect roles
        // Users with 'leader' role but should be 'petugas'
        $usersWithLeaderRole = DB::table('model_has_roles')
            ->join('users', function($join) {
                $join->on('model_has_roles.model_id', '=', 'users.id')
                     ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->where('model_has_roles.role_id', $leaderRoleId)
            ->select('users.*')
            ->get();
        
        Log::info('Found ' . $usersWithLeaderRole->count() . ' users with leader role');
        
        // Step 4: Correct roles based on position field or other criteria
        foreach ($usersWithLeaderRole as $user) {
            // Check if user's position is 'petugas' but has 'leader' role
            if (isset($user->position) && $user->position === 'petugas') {
                Log::info("Correcting role for user ID: {$user->id}, Email: {$user->email} from 'leader' to 'petugas'");
                
                // Remove leader role
                DB::table('model_has_roles')
                    ->where('model_type', 'App\\Models\\User')
                    ->where('model_id', $user->id)
                    ->where('role_id', $leaderRoleId)
                    ->delete();
                
                // Add petugas role if not exists
                $hasPetugasRole = DB::table('model_has_roles')
                    ->where('model_type', 'App\\Models\\User')
                    ->where('model_id', $user->id)
                    ->where('role_id', $petugasRoleId)
                    ->exists();
                
                if (!$hasPetugasRole) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $petugasRoleId,
                        'model_type' => 'App\\Models\\User',
                        'model_id' => $user->id,
                    ]);
                }
                
                // Also add 'user' role if exists and not already assigned
                if ($userRoleId) {
                    $hasUserRole = DB::table('model_has_roles')
                        ->where('model_type', 'App\\Models\\User')
                        ->where('model_id', $user->id)
                        ->where('role_id', $userRoleId)
                        ->exists();
                    
                    if (!$hasUserRole) {
                        DB::table('model_has_roles')->insert([
                            'role_id' => $userRoleId,
                            'model_type' => 'App\\Models\\User',
                            'model_id' => $user->id,
                        ]);
                    }
                }
                
                Log::info("  Role corrected successfully");
            }
        }
        
        // Step 5: Log final state
        Log::info('=== Final User Roles State ===');
        $finalUsers = DB::table('users')->get();
        
        foreach ($finalUsers as $user) {
            $roles = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_type', 'App\\Models\\User')
                ->where('model_has_roles.model_id', $user->id)
                ->pluck('roles.name')
                ->toArray();
            
            Log::info("User ID: {$user->id}, Email: {$user->email}, Roles: " . implode(', ', $roles));
        }
        
        Log::info('=== USER ROLES CLEANUP COMPLETED ===');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Log::info('Rollback of user roles cleanup - manual intervention may be required');
        // Rollback is complex as we don't know original state
        // This should be done manually if needed
    }
};
