<?php

namespace Tests\Feature;

use App\Models\Cctv;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CctvModuleTest extends TestCase
{
    use RefreshDatabase;

    protected $superadmin;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->superadmin = User::factory()->create([
            'role' => 'superadmin'
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin'
        ]);
    }

    /** @test */
    public function superadmin_can_view_cctv_index()
    {
        Cctv::create([
            'name' => 'CCTV Gerbang',
            'location_code' => 'GB-01',
            'status' => 'Baik',
        ]);

        $response = $this->actingAs($this->superadmin)
            ->get(route('admin.cctvs.index'));

        $response->assertStatus(200);
        $response->assertSee('CCTV Gerbang');
        $response->assertSee('GB-01');
    }

    /** @test */
    public function non_superadmin_cannot_view_cctv_index()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.cctvs.index'));

        // Since it's protected by 'role:superadmin' middleware
        $response->assertStatus(403);
    }

    /** @test */
    public function superadmin_can_create_new_cctv()
    {
        $response = $this->actingAs($this->superadmin)
            ->post(route('admin.cctvs.store'), [
                'name' => 'CCTV Parkir',
                'location_code' => 'PK-02',
                'status' => 'Baik',
                'notes' => 'Catatan test'
            ]);

        $response->assertRedirect(route('admin.cctvs.index'));
        
        $this->assertDatabaseHas('cctvs', [
            'name' => 'CCTV Parkir',
            'location_code' => 'PK-02',
            'status' => 'Baik',
        ]);
    }

    /** @test */
    public function superadmin_can_update_cctv_status()
    {
        $cctv = Cctv::create([
            'name' => 'CCTV Lobby',
            'location_code' => 'LB-01',
            'status' => 'Baik',
        ]);

        $response = $this->actingAs($this->superadmin)
            ->put(route('admin.cctvs.update', $cctv->id), [
                'name' => 'CCTV Lobby Baru',
                'location_code' => 'LB-01',
                'status' => 'Jelek',
                'notes' => 'Kamera mati'
            ]);

        $response->assertRedirect(route('admin.cctvs.index'));
        
        $this->assertDatabaseHas('cctvs', [
            'id' => $cctv->id,
            'status' => 'Jelek',
            'name' => 'CCTV Lobby Baru'
        ]);
    }

    /** @test */
    public function superadmin_can_toggle_cctv_status_via_api()
    {
        $cctv = Cctv::create([
            'name' => 'CCTV Gudang',
            'location_code' => 'GD-01',
            'status' => 'Baik',
        ]);

        $response = $this->actingAs($this->superadmin)
            ->postJson(route('admin.cctvs.toggle-status', $cctv->id), [
                'status' => 'Jelek'
            ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('cctvs', [
            'id' => $cctv->id,
            'status' => 'Jelek',
        ]);
    }
}
