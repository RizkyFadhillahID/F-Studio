<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CategorySeeder::class,
            EquipmentSeeder::class,
            RoomSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
