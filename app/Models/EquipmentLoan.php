<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'booking_id',
        'loan_code',
        'loan_date',
        'customer_name',
        'customer_phone',
        'purpose',
        'status',
        'payment_status',
        'payment_method',
        'amount',
        'paid_at',
        'notes',
        'admin_notes',
        'approved_by',
        'approved_at',
        'due_date',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'returned_at' => 'datetime',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(EquipmentLoanItem::class);
    }

    public function checkIns()
    {
        return $this->hasMany(CheckIn::class);
    }

    public static function generateCode(): string
    {
        $date = now()->format('Ymd');
        $last = static::where('loan_code', 'like', "LN-{$date}-%")->count();
        return 'LN-' . $date . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }
}
