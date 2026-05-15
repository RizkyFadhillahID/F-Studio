<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            [
                'name' => 'Studio Foto Utama',
                'code' => 'STD-01',
                'description' => 'Studio foto profesional dengan cyclorama putih, dilengkapi lighting kit lengkap.',
                'capacity' => 8,
                'facilities' => ['Cyclorama', 'Lighting Kit', 'AC', 'WiFi', 'Backdrop Stand'],
                'is_active' => true,
            ],
            [
                'name' => 'Studio Video A',
                'code' => 'STD-02',
                'description' => 'Studio produksi video dengan akustik treatment dan green screen.',
                'capacity' => 6,
                'facilities' => ['Green Screen', 'Akustik Panel', 'AC', 'WiFi', 'Monitor 4K'],
                'is_active' => true,
            ],
            [
                'name' => 'Ruang Meeting Kreatif',
                'code' => 'MTG-01',
                'description' => 'Ruang diskusi dan brainstorming untuk tim kreatif.',
                'capacity' => 12,
                'facilities' => ['Proyektor', 'Whiteboard', 'AC', 'WiFi', 'TV 65 inch'],
                'is_active' => true,
            ],
            [
                'name' => 'Ruang Edit',
                'code' => 'EDT-01',
                'description' => 'Ruang editing dengan workstation high-spec untuk pasca-produksi.',
                'capacity' => 4,
                'facilities' => ['Workstation Mac', 'Monitor Color Grading', 'AC', 'WiFi'],
                'is_active' => true,
            ],
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(['code' => $room['code']], $room);
        }

        // Pastikan semua ruangan aktif
        Room::query()->update(['is_active' => 1]);
    }
}
