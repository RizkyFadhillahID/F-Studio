<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kamera', 'description' => 'Kamera foto dan video digital, DSLR, mirrorless.'],
            ['name' => 'Pencahayaan', 'description' => 'Lighting kit, softbox, reflektor, dan lampu studio.'],
            ['name' => 'Audio', 'description' => 'Mikrofon, audio interface, dan aksesori rekaman.'],
            ['name' => 'Tripod & Stabilizer', 'description' => 'Tripod, monopod, gimbal, dan alat stabilisasi.'],
            ['name' => 'Lensa', 'description' => 'Lensa berbagai jenis untuk kamera mirrorless dan DSLR.'],
            ['name' => 'Aksesori Studio', 'description' => 'Backdrop, stand, clamp, dan perlengkapan studio lainnya.'],
            ['name' => 'Drone & Aerial', 'description' => 'Drone, kamera udara, dan aksesori penerbangan.'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
