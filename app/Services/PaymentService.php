<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\EquipmentLoan;
use Carbon\Carbon;

/**
 * Pembayaran SIMULASI (bukan payment gateway sungguhan).
 * Menghitung nominal sederhana lalu menandai transaksi sebagai lunas.
 */
class PaymentService
{
    /** Dipakai hanya sebagai fallback kalau harga per-item tidak tersedia (mis. ruangan/alat sudah dihapus). */
    private const FALLBACK_ROOM_HOURLY_RATE = 50000;
    private const FALLBACK_LOAN_ITEM_DAILY_RATE = 15000;

    private const METHODS = ['cash', 'transfer', 'ewallet', 'qris'];

    /** Hitung nominal booking dari durasi (dibulatkan ke atas per jam) × harga per jam ruangan. */
    public function bookingAmount(Booking $booking): int
    {
        $booking->loadMissing('room');

        $start = Carbon::parse($booking->start_datetime);
        $end   = Carbon::parse($booking->end_datetime);
        $hours = max(1, (int) ceil($start->diffInMinutes($end) / 60));

        $rate = (float) ($booking->room->price_per_hour ?? self::FALLBACK_ROOM_HOURLY_RATE);

        return (int) round($hours * $rate);
    }

    /** Hitung nominal peminjaman dari jumlah hari × total harga harian tiap item (quantity × harga/hari alat). */
    public function loanAmount(EquipmentLoan $loan): int
    {
        $loan->loadMissing('items.equipment');

        $loanDate = $loan->loan_date ? Carbon::parse($loan->loan_date) : Carbon::parse($loan->created_at);
        $dueDate  = Carbon::parse($loan->due_date);
        $days     = max(1, (int) $loanDate->diffInDays($dueDate) + 1);

        $dailySubtotal = $loan->items->sum(
            fn ($item) => $item->quantity * (float) ($item->equipment->price_per_day ?? self::FALLBACK_LOAN_ITEM_DAILY_RATE)
        );
        $dailySubtotal = max($dailySubtotal, self::FALLBACK_LOAN_ITEM_DAILY_RATE);

        return (int) round($dailySubtotal * $days);
    }

    public function payBooking(Booking $booking, string $method): Booking
    {
        $this->assertMethod($method);

        if (! in_array($booking->status, ['approved', 'completed'])) {
            throw new \Exception('Booking harus disetujui dulu sebelum dapat dibayar.', 422);
        }
        if ($booking->payment_status === 'paid') {
            throw new \Exception('Booking ini sudah lunas.', 422);
        }

        $booking->update([
            'payment_status' => 'paid',
            'payment_method' => $method,
            'amount'         => $this->bookingAmount($booking),
            'paid_at'        => now(),
        ]);

        AppNotification::create([
            'user_id' => $booking->user_id,
            'type'    => 'booking_paid',
            'title'   => 'Pembayaran Diterima',
            'message' => "Pembayaran booking {$booking->booking_code} berhasil (simulasi).",
            'data'    => ['booking_id' => $booking->id],
        ]);

        return $booking->fresh(['user', 'room']);
    }

    public function payLoan(EquipmentLoan $loan, string $method): EquipmentLoan
    {
        $this->assertMethod($method);

        if (! in_array($loan->status, ['approved', 'active', 'overdue', 'returned'])) {
            throw new \Exception('Peminjaman harus disetujui dulu sebelum dapat dibayar.', 422);
        }
        if ($loan->payment_status === 'paid') {
            throw new \Exception('Peminjaman ini sudah lunas.', 422);
        }

        $loan->update([
            'payment_status' => 'paid',
            'payment_method' => $method,
            'amount'         => $this->loanAmount($loan),
            'paid_at'        => now(),
        ]);

        AppNotification::create([
            'user_id' => $loan->user_id,
            'type'    => 'loan_paid',
            'title'   => 'Pembayaran Diterima',
            'message' => "Pembayaran peminjaman {$loan->loan_code} berhasil (simulasi).",
            'data'    => ['loan_id' => $loan->id],
        ]);

        return $loan->fresh(['user', 'items.equipment']);
    }

    private function assertMethod(string $method): void
    {
        if (! in_array($method, self::METHODS)) {
            throw new \Exception('Metode pembayaran tidak valid.', 422);
        }
    }
}
