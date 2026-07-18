<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Admin
            ['email' => 'admin@gmail.com', 'name' => 'Administrator F-Studio', 'role' => 'admin', 'phone' => '081234567890', 'member_id' => null],

            // Resepsionis
            ['email' => 'resepsionis01@gmail.com', 'name' => 'Resepsionis Satu', 'role' => 'receptionist', 'phone' => '081298765401', 'member_id' => null],
            ['email' => 'resepsionis02@gmail.com', 'name' => 'Resepsionis Dua',  'role' => 'receptionist', 'phone' => '081298765402', 'member_id' => null],

            // Member
            ['email' => 'rizky@gmail.com', 'name' => 'Rizky Fadhillah', 'role' => 'member', 'phone' => '081200000001', 'member_id' => 'FS-20260001'],
            ['email' => 'dimas@gmail.com', 'name' => 'Dimas Prakoso',   'role' => 'member', 'phone' => '081200000002', 'member_id' => 'FS-20260002'],
            ['email' => 'putri@gmail.com', 'name' => 'Putri Ramadhani', 'role' => 'member', 'phone' => '081200000003', 'member_id' => 'FS-20260003'],
            ['email' => 'fajar@gmail.com', 'name' => 'Fajar Nugroho',   'role' => 'member', 'phone' => '081200000004', 'member_id' => 'FS-20260004'],
            ['email' => 'ayu@gmail.com',   'name' => 'Ayu Lestari',     'role' => 'member', 'phone' => '081200000005', 'member_id' => 'FS-20260005'],
            ['email' => 'bagas@gmail.com', 'name' => 'Bagas Setiawan',  'role' => 'member', 'phone' => '081200000006', 'member_id' => 'FS-20260006'],
            ['email' => 'indah@gmail.com', 'name' => 'Indah Permata',   'role' => 'member', 'phone' => '081200000007', 'member_id' => 'FS-20260007'],
            ['email' => 'yusuf@gmail.com', 'name' => 'Yusuf Hakim',     'role' => 'member', 'phone' => '081200000008', 'member_id' => 'FS-20260008'],
            ['email' => 'citra@gmail.com', 'name' => 'Citra Ningsih',   'role' => 'member', 'phone' => '081200000009', 'member_id' => 'FS-20260009'],
            ['email' => 'wahyu@gmail.com', 'name' => 'Wahyu Ramadhan',  'role' => 'member', 'phone' => '081200000010', 'member_id' => 'FS-20260010'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                array_merge($user, [
                    'password'  => Hash::make('password123'),
                    'is_active' => true,
                ])
            );
        }
    }
}
