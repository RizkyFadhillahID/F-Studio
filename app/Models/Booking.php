<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'booking_code',
        'customer_name',
        'customer_phone',
        'title',
        'start_datetime',
        'end_datetime',
        'status',
        'notes',
        'admin_notes',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function equipmentLoans()
    {
        return $this->hasMany(EquipmentLoan::class);
    }

    public static function generateCode(): string
    {
        $date = now()->format('Ymd');
        $last = static::where('booking_code', 'like', "BK-{$date}-%")->count();
        return 'BK-' . $date . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }
}
