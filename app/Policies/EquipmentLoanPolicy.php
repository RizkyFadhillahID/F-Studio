<?php

namespace App\Policies;

use App\Models\EquipmentLoan;
use App\Models\User;

class EquipmentLoanPolicy
{
    public function view(User $user, EquipmentLoan $loan): bool
    {
        return $user->isAdmin() || $loan->user_id === $user->id;
    }

    public function update(User $user, EquipmentLoan $loan): bool
    {
        return $loan->user_id === $user->id && $loan->status === 'pending';
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
