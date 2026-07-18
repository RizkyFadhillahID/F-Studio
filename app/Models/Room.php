<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'capacity',
        'price_per_hour',
        'facilities',
        'image',
        'images',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
            'images' => 'array',
            'is_active' => 'boolean',
            'capacity' => 'integer',
            'price_per_hour' => 'decimal:2',
        ];
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function isAvailable(string $start, string $end, ?int $excludeBookingId = null): bool
    {
        // Normalize to Y-m-d H:i:s so SQLite string comparison works correctly
        // (HTML datetime-local sends '2026-05-16T10:00' but DB stores '2026-05-16 10:00:00')
        $start = \Carbon\Carbon::parse($start)->format('Y-m-d H:i:s');
        $end   = \Carbon\Carbon::parse($end)->format('Y-m-d H:i:s');

        // Standard interval overlap: existing.start < new.end AND existing.end > new.start
        $query = $this->bookings()
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start);

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->doesntExist();
    }
}
