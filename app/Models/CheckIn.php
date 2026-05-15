<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    use HasFactory;

    protected $table = 'check_ins';

    protected $fillable = [
        'equipment_loan_id',
        'user_id',
        'device_id',
        'action',
        'checked_at',
        'latitude',
        'longitude',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function loan()
    {
        return $this->belongsTo(EquipmentLoan::class, 'equipment_loan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
