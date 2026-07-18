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
        $drone     = Category::where('name', 'Drone & Aerial')->first();

        $items = [
            // Kamera
            ['category_id' => $kamera?->id,   'name' => 'Sony A7 III',            'code' => 'CAM-001', 'quantity_total' => 2, 'price_per_day' => 150000, 'location' => 'Rak A-1', 'image' => 'images/equipment/sony-a7iii.jpg',   'description' => 'Kamera mirrorless full-frame 24MP, cocok untuk foto & video.'],
            ['category_id' => $kamera?->id,   'name' => 'Canon EOS R5',           'code' => 'CAM-002', 'quantity_total' => 1, 'price_per_day' => 200000, 'location' => 'Rak A-1', 'image' => 'images/equipment/canon-r5.jpg',     'description' => 'Kamera mirrorless 45MP dengan video 8K.'],
            ['category_id' => $kamera?->id,   'name' => 'Nikon Z6 II',            'code' => 'CAM-003', 'quantity_total' => 2, 'price_per_day' => 140000, 'location' => 'Rak A-1', 'image' => 'images/equipment/nikon-z6.jpg',     'description' => 'Kamera mirrorless serbaguna untuk foto & video hybrid.'],
            ['category_id' => $kamera?->id,   'name' => 'GoPro Hero 12',          'code' => 'CAM-004', 'quantity_total' => 3, 'price_per_day' => 50000,  'location' => 'Rak A-3', 'image' => 'images/equipment/gopro.jpg',        'description' => 'Action camera tahan air untuk pengambilan gambar dinamis.'],
            // Pencahayaan
            ['category_id' => $cahaya?->id,   'name' => 'Godox SL-200W',          'code' => 'LGT-001', 'quantity_total' => 4, 'price_per_day' => 40000,  'location' => 'Rak B-1', 'image' => 'images/equipment/godox-sl200.jpg',  'description' => 'LED video light 200W dengan bowens mount.'],
            ['category_id' => $cahaya?->id,   'name' => 'Softbox 90x90cm',        'code' => 'LGT-002', 'quantity_total' => 4, 'price_per_day' => 20000,  'location' => 'Rak B-2', 'image' => 'images/equipment/softbox.jpg',      'description' => 'Softbox untuk cahaya lembut dan merata.'],
            // Audio
            ['category_id' => $audio?->id,    'name' => 'Rode NTG5 Shotgun',      'code' => 'AUD-001', 'quantity_total' => 3, 'price_per_day' => 60000,  'location' => 'Rak C-1', 'image' => 'images/equipment/rode-ntg.jpg',     'description' => 'Mikrofon shotgun broadcast-grade untuk produksi video.'],
            ['category_id' => $audio?->id,    'name' => 'Focusrite Scarlett 2i2', 'code' => 'AUD-002', 'quantity_total' => 2, 'price_per_day' => 30000,  'location' => 'Rak C-1', 'image' => 'images/equipment/scarlett-2i2.jpg', 'description' => 'Audio interface USB 2-input untuk rekaman.'],
            ['category_id' => $audio?->id,    'name' => 'Shure SM7B',             'code' => 'AUD-003', 'quantity_total' => 4, 'price_per_day' => 45000,  'location' => 'Rak C-2', 'image' => 'images/equipment/shure-sm7b.jpg',   'description' => 'Mikrofon dinamis legendaris untuk podcast & vokal.'],
            ['category_id' => $audio?->id,    'name' => 'Zoom H6 Recorder',       'code' => 'AUD-004', 'quantity_total' => 2, 'price_per_day' => 35000,  'location' => 'Rak C-2', 'image' => 'images/equipment/zoom-h6.jpg',      'description' => 'Portable recorder 6-track untuk audio lapangan.'],
            // Tripod & Stabilizer
            ['category_id' => $tripod?->id,   'name' => 'Manfrotto MT055',        'code' => 'TRP-001', 'quantity_total' => 3, 'price_per_day' => 25000,  'location' => 'Rak D-1', 'image' => 'images/equipment/manfrotto.jpg',    'description' => 'Tripod aluminium profesional dengan ball head.'],
            ['category_id' => $tripod?->id,   'name' => 'DJI Ronin RS3',          'code' => 'GIM-001', 'quantity_total' => 2, 'price_per_day' => 70000,  'location' => 'Rak D-2', 'image' => 'images/equipment/ronin-rs3.jpg',    'description' => 'Gimbal 3-axis untuk kamera mirrorless.'],
            // Lensa
            ['category_id' => $lensa?->id,    'name' => 'Sony FE 24-70mm f/2.8',  'code' => 'LNS-001', 'quantity_total' => 2, 'price_per_day' => 80000,  'location' => 'Rak A-2', 'image' => 'images/equipment/sony-2470.jpg',    'description' => 'Lensa zoom standar profesional GM series.'],
            ['category_id' => $lensa?->id,    'name' => 'Sigma 35mm f/1.4 Art',   'code' => 'LNS-002', 'quantity_total' => 2, 'price_per_day' => 60000,  'location' => 'Rak A-2', 'image' => 'images/equipment/sigma-35.jpg',     'description' => 'Lensa prime cepat untuk potret dan low-light.'],
            // Aksesori
            ['category_id' => $aksesori?->id, 'name' => 'Backdrop Muslin 3x6m',   'code' => 'ACC-001', 'quantity_total' => 3, 'price_per_day' => 15000,  'location' => 'Gudang',  'image' => 'images/equipment/backdrop.jpg',     'description' => 'Backdrop kain muslin untuk foto studio.'],
            // Drone
            ['category_id' => $drone?->id,    'name' => 'DJI Mavic 3 Pro',        'code' => 'DRN-001', 'quantity_total' => 1, 'price_per_day' => 250000, 'location' => 'Rak E-1', 'image' => 'images/equipment/dji-mavic.jpg',    'description' => 'Drone dengan kamera Hasselblad untuk aerial footage.'],
        ];

        foreach ($items as $item) {
            if (!$item['category_id']) continue;

            $existing = Equipment::where('code', $item['code'])->first();
            if ($existing) {
                // Jangan reset quantity_available yang sedang dipakai transaksi;
                // hanya sinkronkan selisih total.
                $diff = $item['quantity_total'] - $existing->quantity_total;
                $item['quantity_available'] = max(0, $existing->quantity_available + $diff);
                $existing->update($item);
            } else {
                $item['quantity_available'] = $item['quantity_total'];
                Equipment::create($item);
            }
        }
    }
}
