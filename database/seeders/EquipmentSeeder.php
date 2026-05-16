<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $kamera    = Category::where('name', 'Kamera')->first();
        $cahaya    = Category::where('name', 'Pencahayaan')->first();
        $audio     = Category::where('name', 'Audio')->first();
        $tripod    = Category::where('name', 'Tripod & Stabilizer')->first();
        $lensa     = Category::where('name', 'Lensa')->first();
        $aksesori  = Category::where('name', 'Aksesori Studio')->first();

        $items = [
            ['category_id' => $kamera?->id,   'name' => 'Sony A7 III',        'code' => 'CAM-001', 'quantity_total' => 2, 'location' => 'Rak A-1'],
            ['category_id' => $kamera?->id,   'name' => 'Canon EOS R5',       'code' => 'CAM-002', 'quantity_total' => 1, 'location' => 'Rak A-1'],
            ['category_id' => $cahaya?->id,   'name' => 'Godox SL-200W',      'code' => 'LGT-001', 'quantity_total' => 4, 'location' => 'Rak B-1'],
            ['category_id' => $cahaya?->id,   'name' => 'Softbox 90x90cm',    'code' => 'LGT-002', 'quantity_total' => 4, 'location' => 'Rak B-2'],
            ['category_id' => $audio?->id,    'name' => 'Rode NTG5 Shotgun',  'code' => 'AUD-001', 'quantity_total' => 3, 'location' => 'Rak C-1'],
            ['category_id' => $audio?->id,    'name' => 'Focusrite Scarlett 2i2', 'code' => 'AUD-002', 'quantity_total' => 2, 'location' => 'Rak C-1'],
            ['category_id' => $tripod?->id,   'name' => 'Manfrotto MT055',    'code' => 'TRP-001', 'quantity_total' => 3, 'location' => 'Rak D-1'],
            ['category_id' => $tripod?->id,   'name' => 'DJI Ronin RS3',      'code' => 'GIM-001', 'quantity_total' => 2, 'location' => 'Rak D-2'],
            ['category_id' => $lensa?->id,    'name' => 'Sony FE 24-70mm f/2.8', 'code' => 'LNS-001', 'quantity_total' => 2, 'location' => 'Rak A-2'],
            ['category_id' => $aksesori?->id, 'name' => 'Backdrop Muslin 3x6m', 'code' => 'ACC-001', 'quantity_total' => 3, 'location' => 'Gudang'],
        ];

        foreach ($items as $item) {
            if (!$item['category_id']) continue;
            $item['quantity_available'] = $item['quantity_total'];
            Equipment::updateOrCreate(['code' => $item['code']], $item);
        }
    }
}
