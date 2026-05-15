<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentLoanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_loan_id',
        'equipment_id',
        'quantity',
        'check_in_at',
        'check_out_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'check_in_at'  => 'datetime',
            'check_out_at' => 'datetime',
            'quantity'     => 'integer',
        ];
    }

    public function loan()
    {
        return $this->belongsTo(EquipmentLoan::class, 'equipment_loan_id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
