<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserManagementExpansionTest extends TestCase
{
    // Gunakan transaksi agar test ini tidak mengubah database secara persisten,
    // atau jika database sudah dimodernisasi, kita verifikasi data real karena
    // requirement meminta user verify sistem sudah terupdate.
    // Jika kita tidak menggunakan RefreshDatabase, test akan menggunakan data asli DB.

    /** @test */
    public function verify_all_expanded_unit_users_can_login()
    {
        $emails = [
            'user.up2w1@pln.com',
            'user.up2w2@pln.com',
            'user.up2w3@pln.com',
            'user.up2w4@pln.com',
            'user.up2w5@pln.com',
            'user.up2w6@pln.com',
        ];

        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();
            
            // 1. Verifikasi User Exists
            $this->assertNotNull($user, "User $email tidak ditemukan di database.");

            // 2. Verifikasi Role adalah leader
            $this->assertTrue($user->hasRole('leader'), "User $email tidak memiliki akses leader.");

            // 3. Testing Integrasi Login & Session
            $response = $this->post(route('login'), [
                'email' => $email,
                'password' => 'pln123'
            ]);

            // Pada sistem Laravel default, login redirect ke "/dashboard" atau "/"
            $response->assertStatus(302);
            $this->assertAuthenticatedAs($user);
            
            // Logout untuk iterasi tes selanjutnya
            $this->post(route('logout'));
        }
    }
}
