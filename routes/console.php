<?php

use App\Models\EquipmentLoan;
use Illuminate\Support\Facades\Schedule;

// Mark loans as overdue every day at midnight
Schedule::call(function () {
    $count = EquipmentLoan::whereIn('status', ['approved', 'active'])
        ->where('due_date', '<', now()->toDateString())
        ->update(['status' => 'overdue']);

    \Illuminate\Support\Facades\Log::info("Marked {$count} loans as overdue.");
})->daily()->name('mark-overdue-loans')->withoutOverlapping();

// Complete approved bookings whose end time has passed
Schedule::call(function () {
    \App\Models\Booking::where('status', 'approved')
        ->where('end_datetime', '<', now())
        ->update(['status' => 'completed']);
})->hourly()->name('complete-expired-bookings')->withoutOverlapping();
