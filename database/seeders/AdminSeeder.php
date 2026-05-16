<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@fstudio.id'],
            [
                'name'      => 'Administrator F-Studio',
                'password'  => Hash::make('password123'),
                'role'      => 'admin',
                'is_active' => true,
                'phone'     => '081234567890',
            ]
        );

        // Receptionist
        User::updateOrCreate(
            ['email' => 'rizky@fstudio.id'],
            [
                'name'      => 'Rizky',
                'password'  => Hash::make('password123'),
                'role'      => 'receptionist',
                'is_active' => true,
                'phone'     => '081298765432',
            ]
        );

        // Member
        User::updateOrCreate(
            ['email' => 'member@fstudio.id'],
            [
                'name'      => 'Member Test',
                'password'  => Hash::make('password123'),
                'role'      => 'member',
                'is_active' => true,
                'phone'     => '081200000001',
            ]
        );
    }
}
