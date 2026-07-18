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
                'price_per_hour' => 150000,
                'facilities' => ['Cyclorama', 'Lighting Kit', 'AC', 'WiFi', 'Backdrop Stand'],
                'image' => 'images/rooms/studio-foto.jpg',
                'images' => ['images/rooms/studio-foto.jpg', 'images/rooms/studio-foto-2.jpg', 'images/rooms/studio-foto-3.jpg'],
                'is_active' => true,
            ],
            [
                'name' => 'Studio Video A',
                'code' => 'STD-02',
                'description' => 'Studio produksi video dengan akustik treatment dan green screen.',
                'capacity' => 6,
                'price_per_hour' => 175000,
                'facilities' => ['Green Screen', 'Akustik Panel', 'AC', 'WiFi', 'Monitor 4K'],
                'image' => 'images/rooms/studio-video.jpg',
                'images' => ['images/rooms/studio-video.jpg', 'images/rooms/studio-video-2.jpg', 'images/rooms/studio-video-3.jpg'],
                'is_active' => true,
            ],
            [
                'name' => 'Ruang Meeting Kreatif',
                'code' => 'MTG-01',
                'description' => 'Ruang diskusi dan brainstorming untuk tim kreatif.',
                'capacity' => 12,
                'price_per_hour' => 100000,
                'facilities' => ['Proyektor', 'Whiteboard', 'AC', 'WiFi', 'TV 65 inch'],
                'image' => 'images/rooms/meeting-room.jpg',
                'images' => ['images/rooms/meeting-room.jpg', 'images/rooms/meeting-room-2.jpg', 'images/rooms/meeting-room-3.jpg'],
                'is_active' => true,
            ],
            [
                'name' => 'Ruang Edit',
                'code' => 'EDT-01',
                'description' => 'Ruang editing dengan workstation high-spec untuk pasca-produksi.',
                'capacity' => 4,
                'price_per_hour' => 80000,
                'facilities' => ['Workstation Mac', 'Monitor Color Grading', 'AC', 'WiFi'],
                'image' => 'images/rooms/editing-room.jpg',
                'images' => ['images/rooms/editing-room.jpg', 'images/rooms/editing-room-2.jpg', 'images/rooms/editing-room-3.jpg'],
                'is_active' => true,
            ],
            [
                'name' => 'Studio Podcast',
                'code' => 'POD-01',
                'description' => 'Ruang rekaman podcast kedap suara dengan meja bundar untuk 4 pembicara.',
                'capacity' => 4,
                'price_per_hour' => 90000,
                'facilities' => ['Mikrofon Kondensor x4', 'Mixer', 'Akustik Panel', 'AC', 'WiFi'],
                'image' => 'images/rooms/podcast-room.jpg',
                'images' => ['images/rooms/podcast-room.jpg', 'images/rooms/podcast-room-2.jpg', 'images/rooms/podcast-room-3.jpg'],
                'is_active' => true,
            ],
            [
                'name' => 'Coworking Space',
                'code' => 'CWK-01',
                'description' => 'Area kerja bersama dengan meja fleksibel untuk komunitas kreatif.',
                'capacity' => 20,
                'price_per_hour' => 40000,
                'facilities' => ['Meja Fleksibel', 'AC', 'WiFi Cepat', 'Coffee Corner', 'Loker'],
                'image' => 'images/rooms/coworking.jpg',
                'images' => ['images/rooms/coworking.jpg', 'images/rooms/coworking-2.jpg', 'images/rooms/coworking-3.jpg'],
                'is_active' => true,
            ],
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(['code' => $room['code']], $room);
        }
    }
}
