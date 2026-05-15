<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || $booking->user_id === $user->id;
    }

    public function update(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id && $booking->status === 'pending';
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return ($user->isAdmin() || $booking->user_id === $user->id)
            && in_array($booking->status, ['pending', 'approved'])
            && $booking->start_datetime > now();
    }

    public function approve(User $user): bool
    {
        return $user->isAdmin();
    }

    public function reject(User $user): bool
    {
        return $user->isAdmin();
    }
}
