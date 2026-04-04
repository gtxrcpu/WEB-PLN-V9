<?php

use App\Models\User;
use App\Models\Unit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

echo "=== Memulai Update Sistem User Management ===\n";

// 1. Eksekusi Unit Expansion
echo "1. Menyiapkan Data Unit (Ekspansi 6 Unit)...\n";

// Update Unit yang sudah ada code-nya agar konsisten
$upw2 = Unit::where('code', 'UPW2')->first();
if ($upw2) {
    $upw2->update(['code' => 'UP2W3', 'name' => 'UP2WIII']);
}

$upw3 = Unit::where('code', 'UPW3')->first();
if ($upw3) {
    $upw3->update(['code' => 'UP2W4', 'name' => 'UP2WIV']);
}

// Ensure 6 Units exist
$unitsData = [
    ['code' => 'UP2W1', 'name' => 'UP2WI', 'description' => 'Unit Pelayanan dan Pengelolaan Wilayah I'],
    ['code' => 'UP2W2', 'name' => 'UP2WII', 'description' => 'Unit Pelayanan dan Pengelolaan Wilayah II'],
    ['code' => 'UP2W3', 'name' => 'UP2WIII', 'description' => 'Unit Pelayanan dan Pengelolaan Wilayah III'],
    ['code' => 'UP2W4', 'name' => 'UP2WIV', 'description' => 'Unit Pelayanan dan Pengelolaan Wilayah IV'],
    ['code' => 'UP2W5', 'name' => 'UP2WV', 'description' => 'Unit Pelayanan dan Pengelolaan Wilayah V'],
    ['code' => 'UP2W6', 'name' => 'UP2WVI', 'description' => 'Unit Pelayanan dan Pengelolaan Wilayah VI'],
];

foreach ($unitsData as $data) {
    Unit::updateOrCreate(
        ['code' => $data['code']],
        ['name' => $data['name'], 'description' => $data['description']]
    );
}

// 2. Konfigurasi Email & Update User Existing
echo "2. Konfigurasi Email Existing...\n";

// Update leader UP2WIII
$leader3 = User::where('email', 'leader.upw2@pln.co.id')->first();
if ($leader3) {
    $leader3->update([
        'email' => 'user.up2w3@pln.com',
        'username' => 'user_up2w3',
        'name' => 'User UP2WIII'
    ]);
} else {
    // Kalau email sebelumnya sudah terganti
    $u3 = User::where('email', 'user.up2w3@pln.com')->first();
    if(!$u3) {
        $leader3 = User::create([
            'email' => 'user.up2w3@pln.com',
            'username' => 'user_up2w3',
            'name' => 'User UP2WIII',
            'password' => Hash::make('pln123'),
            'position' => 'leader',
            'unit_id' => Unit::where('code', 'UP2W3')->first()->id
        ]);
        $leader3->assignRole('leader');
    }
}

// Update leader UP2WIV
$leader4 = User::where('email', 'leader.upw3@pln.co.id')->first();
if ($leader4) {
    $leader4->update([
        'email' => 'user.up2w4@pln.com',
        'username' => 'user_up2w4',
        'name' => 'User UP2WIV'
    ]);
} else {
    $u4 = User::where('email', 'user.up2w4@pln.com')->first();
    if(!$u4) {
        $leader4 = User::create([
            'email' => 'user.up2w4@pln.com',
            'username' => 'user_up2w4',
            'name' => 'User UP2WIV',
            'password' => Hash::make('pln123'),
            'position' => 'leader',
            'unit_id' => Unit::where('code', 'UP2W4')->first()->id
        ]);
        $leader4->assignRole('leader');
    }
}

// 3. Buat User untuk Unit Baru
echo "3. Insert Record Baru untuk Unit Baru...\n";

$newUsers = [
    ['email' => 'user.up2w1@pln.com', 'username' => 'user_up2w1', 'name' => 'User UP2WI', 'code' => 'UP2W1'],
    ['email' => 'user.up2w2@pln.com', 'username' => 'user_up2w2', 'name' => 'User UP2WII', 'code' => 'UP2W2'],
    ['email' => 'user.up2w5@pln.com', 'username' => 'user_up2w5', 'name' => 'User UP2WV', 'code' => 'UP2W5'],
    ['email' => 'user.up2w6@pln.com', 'username' => 'user_up2w6', 'name' => 'User UP2WVI', 'code' => 'UP2W6'],
];

foreach ($newUsers as $nu) {
    if (!User::where('email', $nu['email'])->exists()) {
        $unitId = Unit::where('code', $nu['code'])->first()->id;
        $user = User::create([
            'email' => $nu['email'],
            'username' => $nu['username'],
            'name' => $nu['name'],
            'password' => Hash::make('pln123'), // Default password
            'position' => 'leader',
            'unit_id' => $unitId
        ]);
        $user->assignRole('leader');
    }
}

echo "=== Selesai ===\n";
