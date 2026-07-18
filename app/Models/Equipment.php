<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'category_id',
        'name',
        'code',
        'description',
        'quantity_total',
        'quantity_available',
        'price_per_day',
        'location',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'quantity_total' => 'integer',
            'quantity_available' => 'integer',
            'price_per_day' => 'decimal:2',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function loanItems()
    {
        return $this->hasMany(EquipmentLoanItem::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where('quantity_available', '>', 0);
    }

    /**
     * Hitung jumlah unit yang sudah direservasi (pending/approved/active)
     * pada rentang tanggal tertentu.
     */
    public function countReservedBetween(string $loanDate, string $dueDate): int
    {
        return EquipmentLoanItem::where('equipment_id', $this->id)
            ->whereHas('loan', function ($q) use ($loanDate, $dueDate) {
                $q->whereIn('status', ['pending', 'approved', 'active'])
                    ->where('due_date', '>=', $loanDate)
                    ->where(function ($q2) use ($loanDate, $dueDate) {
                        $q2->whereNull('loan_date')
                            ->orWhere('loan_date', '<=', $dueDate);
                    });
            })
            ->sum('quantity');
    }

    /**
     * Cek apakah sejumlah unit tersedia untuk rentang tanggal tertentu.
     */
    public function isAvailableBetween(string $loanDate, string $dueDate, int $qty): bool
    {
        $reserved = $this->countReservedBetween($loanDate, $dueDate);
        return ($this->quantity_total - $reserved) >= $qty;
    }
}
