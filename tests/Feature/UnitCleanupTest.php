<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class UnitCleanupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles first
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        
        // Seed units
        $this->artisan('db:seed', ['--class' => 'UnitSeeder']);
    }

    public function test_no_duplicate_units_exist()
    {
        $duplicates = Unit::select('code', DB::raw('COUNT(*) as count'))
            ->groupBy('code')
            ->having('count', '>', 1)
            ->get();
        
        $this->assertEquals(0, $duplicates->count(), 'No duplicate units should exist');
    }
    
    public function test_exactly_seven_units_exist()
    {
        $count = Unit::count();
        $this->assertEquals(7, $count, 'Should have exactly 7 units');
    }
    
    public function test_all_expected_units_exist()
    {
        $expectedCodes = ['INDUK', 'UP2WI', 'UP2WII', 'UP2WIII', 'UP2WIV', 'UP2WV', 'UP2WVI'];
        
        foreach ($expectedCodes as $code) {
            $this->assertTrue(
                Unit::where('code', $code)->exists(),
                "Unit {$code} should exist"
            );
        }
    }
    
    public function test_unit_codes_are_unique()
    {
        $units = Unit::all();
        $codes = $units->pluck('code')->toArray();
        $uniqueCodes = array_unique($codes);
        
        $this->assertEquals(
            count($codes),
            count($uniqueCodes),
            'All unit codes should be unique'
        );
    }
    
    public function test_no_users_with_wrong_roles()
    {
        // Create a test user with wrong role
        $unit = Unit::first();
        $user = User::factory()->create([
            'unit_id' => $unit->id,
            'position' => 'petugas',
        ]);
        
        // Assign correct roles
        $user->assignRole('petugas');
        $user->assignRole('user');
        
        // Verify no users have leader role with petugas position
        $wrongRoles = User::whereHas('roles', function($query) {
            $query->where('name', 'leader');
        })->where('position', 'petugas')->count();
        
        $this->assertEquals(0, $wrongRoles, 'No users should have leader role with petugas position');
    }
    
    public function test_cannot_create_duplicate_unit()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('sudah ada');
        
        // Try to create duplicate of existing unit
        $existingUnit = Unit::first();
        
        Unit::create([
            'code' => $existingUnit->code,
            'name' => 'Duplicate Unit',
            'is_active' => true
        ]);
    }
    
    public function test_cannot_update_to_duplicate_code()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('sudah ada');
        
        $unit1 = Unit::where('code', 'INDUK')->first();
        $unit2 = Unit::where('code', 'UP2WI')->first();
        
        // Try to update unit2 to have same code as unit1
        $unit2->code = $unit1->code;
        $unit2->save();
    }
    
    public function test_unit_has_correct_structure()
    {
        $unit = Unit::first();
        
        $this->assertNotNull($unit->code);
        $this->assertNotNull($unit->name);
        $this->assertIsBool($unit->is_active);
    }
    
    public function test_unit_relationships_work()
    {
        $unit = Unit::first();
        
        // Create test user
        $user = User::factory()->create([
            'unit_id' => $unit->id,
            'position' => 'petugas',
        ]);
        
        // Test users relationship
        $this->assertTrue($unit->users()->exists());
        $this->assertEquals($user->id, $unit->users()->first()->id);
    }
    
    public function test_cleanup_command_runs_successfully()
    {
        // Run cleanup command in dry-run mode
        $this->artisan('units:cleanup-duplicates --dry-run')
            ->assertExitCode(0);
    }
    
    public function test_all_units_are_active()
    {
        $inactiveUnits = Unit::where('is_active', false)->count();
        $this->assertEquals(0, $inactiveUnits, 'All units should be active');
    }
}
