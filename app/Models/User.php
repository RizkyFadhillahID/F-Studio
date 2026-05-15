<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'member_id',
        'avatar',
        'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isReceptionist(): bool
    {
        return $this->role === 'receptionist';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function equipmentLoans()
    {
        return $this->hasMany(EquipmentLoan::class);
    }

    public function checkIns()
    {
        return $this->hasMany(CheckIn::class);
    }

    public function appNotifications()
    {
        return $this->hasMany(AppNotification::class);
    }
}
