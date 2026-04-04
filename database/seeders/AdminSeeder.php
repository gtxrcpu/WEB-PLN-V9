<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua unit yang dibutuhkan
        $induk   = Unit::where('code', 'INDUK')->first();
        $up2wI   = Unit::where('code', 'UP2WI')->first();
        $up2wII  = Unit::where('code', 'UP2WII')->first();
        $up2wIII = Unit::where('code', 'UP2WIII')->first();
        $up2wIV  = Unit::where('code', 'UP2WIV')->first();
        $up2wV   = Unit::where('code', 'UP2WV')->first();
        $up2wVI  = Unit::where('code', 'UP2WVI')->first();

        // ==============================================================
        // 1. SUPERADMIN (tidak terikat unit) - Full access ke semua unit
        // ==============================================================
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@pln.co.id'],
            [
                'name'     => 'Super Administrator',
                'username' => 'superadmin',
                'password' => Hash::make('super123'),
                'unit_id'  => null,
                'position' => null,
            ]
        );
        if (!$superadmin->hasRole('superadmin')) {
            $superadmin->assignRole('superadmin');
        }

        // ==============================================================
        // 2. LEADER & PETUGAS INDUK
        //    Catatan: induk TIDAK termasuk dalam konfigurasi email otomatis
        //    berdasarkan requirement (kecuali unit III, IV, dan induk)
        // ==============================================================
        $leaderInduk = User::firstOrCreate(
            ['email' => 'leader.induk@pln.co.id'],
            [
                'name'     => 'Leader Induk',
                'username' => 'leader_induk',
                'password' => Hash::make('leader123'),
                'unit_id'  => $induk?->id,
                'position' => 'leader',
            ]
        );
        if (!$leaderInduk->hasRole('leader')) {
            $leaderInduk->assignRole('leader');
        }

        $petugasInduk = User::firstOrCreate(
            ['email' => 'petugas.induk@pln.co.id'],
            [
                'name'     => 'Petugas Induk',
                'username' => 'petugas_induk',
                'password' => Hash::make('petugas123'),
                'unit_id'  => $induk?->id,
                'position' => 'petugas',
            ]
        );
        if (!$petugasInduk->hasRole('petugas')) {
            $petugasInduk->assignRole('petugas');
        }

        // ==============================================================
        // 3. LEADER & PETUGAS UP2W I
        //    Email format: UP2W1@pln.com / leader.UPW1@pln.com
        //    Password default: "password" (petugas), "password" (leader)
        // ==============================================================
        $leaderUp2wI = User::firstOrCreate(
            ['email' => 'leader.UPW1@pln.com'],
            [
                'name'     => 'Leader UP2W I',
                'username' => 'leader_up2w1',
                'password' => Hash::make('password'),
                'unit_id'  => $up2wI?->id,
                'position' => 'leader',
            ]
        );
        if (!$leaderUp2wI->hasRole('leader')) {
            $leaderUp2wI->assignRole('leader');
        }

        $petugasUp2wI = User::firstOrCreate(
            ['email' => 'UP2W1@pln.com'],
            [
                'name'     => 'Petugas UP2W I',
                'username' => 'petugas_up2w1',
                'password' => Hash::make('password'),
                'unit_id'  => $up2wI?->id,
                'position' => 'petugas',
            ]
        );
        if (!$petugasUp2wI->hasRole('petugas')) {
            $petugasUp2wI->assignRole('petugas');
        }

        // ==============================================================
        // 4. LEADER & PETUGAS UP2W II
        // ==============================================================
        $leaderUp2wII = User::firstOrCreate(
            ['email' => 'leader.UPW2@pln.com'],
            [
                'name'     => 'Leader UP2W II',
                'username' => 'leader_up2w2',
                'password' => Hash::make('password'),
                'unit_id'  => $up2wII?->id,
                'position' => 'leader',
            ]
        );
        if (!$leaderUp2wII->hasRole('leader')) {
            $leaderUp2wII->assignRole('leader');
        }

        $petugasUp2wII = User::firstOrCreate(
            ['email' => 'UP2W2@pln.com'],
            [
                'name'     => 'Petugas UP2W II',
                'username' => 'petugas_up2w2',
                'password' => Hash::make('password'),
                'unit_id'  => $up2wII?->id,
                'position' => 'petugas',
            ]
        );
        if (!$petugasUp2wII->hasRole('petugas')) {
            $petugasUp2wII->assignRole('petugas');
        }

        // ==============================================================
        // 5. LEADER & PETUGAS UP2W III
        //    Catatan: UP2W III TIDAK termasuk dalam konfigurasi email otomatis
        //    berdasarkan requirement. Tetap dibuat dengan password lama.
        // ==============================================================
        $leaderUp2wIII = User::firstOrCreate(
            ['email' => 'leader.upw3@pln.co.id'],
            [
                'name'     => 'Leader UP2W III',
                'username' => 'leader_upw3',
                'password' => Hash::make('leader123'),
                'unit_id'  => $up2wIII?->id,
                'position' => 'leader',
            ]
        );
        if (!$leaderUp2wIII->hasRole('leader')) {
            $leaderUp2wIII->assignRole('leader');
        }

        $petugasUp2wIII = User::firstOrCreate(
            ['email' => 'petugas.upw3@pln.co.id'],
            [
                'name'     => 'Petugas UP2W III',
                'username' => 'petugas_upw3',
                'password' => Hash::make('petugas123'),
                'unit_id'  => $up2wIII?->id,
                'position' => 'petugas',
            ]
        );
        if (!$petugasUp2wIII->hasRole('petugas')) {
            $petugasUp2wIII->assignRole('petugas');
        }

        // ==============================================================
        // 6. LEADER & PETUGAS UP2W IV
        //    Catatan: UP2W IV TIDAK termasuk dalam konfigurasi email otomatis
        //    berdasarkan requirement. Tetap dibuat dengan password lama.
        // ==============================================================
        $leaderUp2wIV = User::firstOrCreate(
            ['email' => 'leader.upw4@pln.co.id'],
            [
                'name'     => 'Leader UP2W IV',
                'username' => 'leader_upw4',
                'password' => Hash::make('leader123'),
                'unit_id'  => $up2wIV?->id,
                'position' => 'leader',
            ]
        );
        if (!$leaderUp2wIV->hasRole('leader')) {
            $leaderUp2wIV->assignRole('leader');
        }

        $petugasUp2wIV = User::firstOrCreate(
            ['email' => 'petugas.upw4@pln.co.id'],
            [
                'name'     => 'Petugas UP2W IV',
                'username' => 'petugas_upw4',
                'password' => Hash::make('petugas123'),
                'unit_id'  => $up2wIV?->id,
                'position' => 'petugas',
            ]
        );
        if (!$petugasUp2wIV->hasRole('petugas')) {
            $petugasUp2wIV->assignRole('petugas');
        }

        // ==============================================================
        // 7. LEADER & PETUGAS UP2W V
        //    Email format: UP2W5@pln.com / leader.UPW5@pln.com
        // ==============================================================
        $leaderUp2wV = User::firstOrCreate(
            ['email' => 'leader.UPW5@pln.com'],
            [
                'name'     => 'Leader UP2W V',
                'username' => 'leader_up2w5',
                'password' => Hash::make('password'),
                'unit_id'  => $up2wV?->id,
                'position' => 'leader',
            ]
        );
        if (!$leaderUp2wV->hasRole('leader')) {
            $leaderUp2wV->assignRole('leader');
        }

        $petugasUp2wV = User::firstOrCreate(
            ['email' => 'UP2W5@pln.com'],
            [
                'name'     => 'Petugas UP2W V',
                'username' => 'petugas_up2w5',
                'password' => Hash::make('password'),
                'unit_id'  => $up2wV?->id,
                'position' => 'petugas',
            ]
        );
        if (!$petugasUp2wV->hasRole('petugas')) {
            $petugasUp2wV->assignRole('petugas');
        }

        // ==============================================================
        // 8. LEADER & PETUGAS UP2W VI
        //    Email format: UP2W6@pln.com / leader.UPW6@pln.com
        // ==============================================================
        $leaderUp2wVI = User::firstOrCreate(
            ['email' => 'leader.UPW6@pln.com'],
            [
                'name'     => 'Leader UP2W VI',
                'username' => 'leader_up2w6',
                'password' => Hash::make('password'),
                'unit_id'  => $up2wVI?->id,
                'position' => 'leader',
            ]
        );
        if (!$leaderUp2wVI->hasRole('leader')) {
            $leaderUp2wVI->assignRole('leader');
        }

        $petugasUp2wVI = User::firstOrCreate(
            ['email' => 'UP2W6@pln.com'],
            [
                'name'     => 'Petugas UP2W VI',
                'username' => 'petugas_up2w6',
                'password' => Hash::make('password'),
                'unit_id'  => $up2wVI?->id,
                'position' => 'petugas',
            ]
        );
        if (!$petugasUp2wVI->hasRole('petugas')) {
            $petugasUp2wVI->assignRole('petugas');
        }

        // ==============================================================
        // Output ringkasan ke console
        // ==============================================================
        $this->command->info('');
        $this->command->info('✅ SUPERADMIN:');
        $this->command->info('   Email: superadmin@pln.co.id | Password: super123');
        $this->command->info('');
        $this->command->info('✅ INDUK (tidak termasuk email otomatis):');
        $this->command->info('   Leader  - leader.induk@pln.co.id | Password: leader123');
        $this->command->info('   Petugas - petugas.induk@pln.co.id | Password: petugas123');
        $this->command->info('');
        $this->command->info('✅ UP2W I (email format baru):');
        $this->command->info('   Leader  - leader.UPW1@pln.com | Password: password');
        $this->command->info('   Petugas - UP2W1@pln.com       | Password: password');
        $this->command->info('');
        $this->command->info('✅ UP2W II (email format baru):');
        $this->command->info('   Leader  - leader.UPW2@pln.com | Password: password');
        $this->command->info('   Petugas - UP2W2@pln.com       | Password: password');
        $this->command->info('');
        $this->command->info('✅ UP2W III (tidak termasuk email otomatis):');
        $this->command->info('   Leader  - leader.upw3@pln.co.id | Password: leader123');
        $this->command->info('   Petugas - petugas.upw3@pln.co.id | Password: petugas123');
        $this->command->info('');
        $this->command->info('✅ UP2W IV (tidak termasuk email otomatis):');
        $this->command->info('   Leader  - leader.upw4@pln.co.id | Password: leader123');
        $this->command->info('   Petugas - petugas.upw4@pln.co.id | Password: petugas123');
        $this->command->info('');
        $this->command->info('✅ UP2W V (email format baru):');
        $this->command->info('   Leader  - leader.UPW5@pln.com | Password: password');
        $this->command->info('   Petugas - UP2W5@pln.com       | Password: password');
        $this->command->info('');
        $this->command->info('✅ UP2W VI (email format baru):');
        $this->command->info('   Leader  - leader.UPW6@pln.com | Password: password');
        $this->command->info('   Petugas - UP2W6@pln.com       | Password: password');
    }
}
