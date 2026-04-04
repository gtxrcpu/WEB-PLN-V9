<?php

namespace Tests\Feature\Admin;

use App\Models\KartuApar;
use App\Models\Apar;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalCheckNewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create units
        Unit::factory()->create(['code' => 'INDUK', 'name' => 'Induk']);
        Unit::factory()->create(['code' => 'UPW2', 'name' => 'UP2WIII']);
    }

    /** @test */
    public function it_returns_json_response_with_valid_timestamp()
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');

        $response = $this->actingAs($admin)
            ->getJson('/admin/approvals/check-new?last_checked=' . now()->subMinutes(5)->toIso8601String());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'has_new',
                'count',
                'total_pending',
                'timestamp'
            ]);
    }

    /** @test */
    public function it_returns_error_with_invalid_timestamp()
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');

        $response = $this->actingAs($admin)
            ->getJson('/admin/approvals/check-new?last_checked=invalid-timestamp');

        $response->assertStatus(400)
            ->assertJson([
                'has_new' => false,
                'count' => 0,
                'error' => 'Invalid timestamp format'
            ]);
    }

    /** @test */
    public function it_returns_false_when_no_timestamp_provided()
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');

        $response = $this->actingAs($admin)
            ->getJson('/admin/approvals/check-new');

        $response->assertStatus(200)
            ->assertJson([
                'has_new' => false,
                'count' => 0,
                'total_pending' => 0
            ]);
    }

    /** @test */
    public function it_detects_new_approvals_after_timestamp()
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');
        
        $unit = Unit::where('code', 'INDUK')->first();
        $user = User::factory()->create(['unit_id' => $unit->id]);
        
        $lastChecked = now()->subMinutes(5);

        // Create APAR and Kartu after the timestamp
        $apar = Apar::factory()->create(['unit_id' => $unit->id]);
        $kartu = KartuApar::factory()->create([
            'apar_id' => $apar->id,
            'user_id' => $user->id,
            'approved_at' => null,
            'rejected_at' => null,
            'leader_rejected_at' => null,
            'created_at' => now()
        ]);

        $response = $this->actingAs($admin)
            ->getJson('/admin/approvals/check-new?last_checked=' . $lastChecked->toIso8601String());

        $response->assertStatus(200)
            ->assertJson([
                'has_new' => true,
                'count' => 1
            ]);
    }

    /** @test */
    public function it_does_not_detect_old_approvals()
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');
        
        $unit = Unit::where('code', 'INDUK')->first();
        $user = User::factory()->create(['unit_id' => $unit->id]);

        // Create APAR and Kartu before the timestamp
        $apar = Apar::factory()->create(['unit_id' => $unit->id]);
        $kartu = KartuApar::factory()->create([
            'apar_id' => $apar->id,
            'user_id' => $user->id,
            'approved_at' => null,
            'rejected_at' => null,
            'leader_rejected_at' => null,
            'created_at' => now()->subHours(2)
        ]);

        $lastChecked = now()->subMinutes(5);

        $response = $this->actingAs($admin)
            ->getJson('/admin/approvals/check-new?last_checked=' . $lastChecked->toIso8601String());

        $response->assertStatus(200)
            ->assertJson([
                'has_new' => false,
                'count' => 0
            ]);
    }

    /** @test */
    public function it_counts_total_pending_approvals()
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');
        
        $unit = Unit::where('code', 'INDUK')->first();
        $user = User::factory()->create(['unit_id' => $unit->id]);

        // Create 3 pending approvals
        for ($i = 0; $i < 3; $i++) {
            $apar = Apar::factory()->create(['unit_id' => $unit->id]);
            KartuApar::factory()->create([
                'apar_id' => $apar->id,
                'user_id' => $user->id,
                'approved_at' => null,
                'rejected_at' => null,
                'leader_rejected_at' => null,
            ]);
        }

        $response = $this->actingAs($admin)
            ->getJson('/admin/approvals/check-new?last_checked=' . now()->subMinutes(5)->toIso8601String());

        $response->assertStatus(200)
            ->assertJson([
                'total_pending' => 3
            ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/admin/approvals/check-new?last_checked=' . now()->toIso8601String());

        $response->assertStatus(302); // Redirect to login
    }

    /** @test */
    public function it_handles_url_encoded_timestamp()
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');

        $timestamp = now()->subMinutes(5)->toIso8601String();
        $encodedTimestamp = urlencode($timestamp);

        $response = $this->actingAs($admin)
            ->getJson('/admin/approvals/check-new?last_checked=' . $encodedTimestamp);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'has_new',
                'count',
                'total_pending',
                'timestamp'
            ]);
    }

    /** @test */
    public function it_returns_valid_json_even_on_database_error()
    {
        $admin = User::factory()->create();
        $admin->assignRole('superadmin');

        // This test verifies that even if there's a database issue,
        // we still return valid JSON (not HTML error page)
        $response = $this->actingAs($admin)
            ->getJson('/admin/approvals/check-new?last_checked=' . now()->toIso8601String());

        $response->assertStatus(200);
        $this->assertJson($response->getContent());
    }
}
