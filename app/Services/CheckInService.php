<?php

namespace App\Services;

use App\Models\CheckIn;
use App\Models\Equipment;
use App\Models\EquipmentLoan;
use App\Models\EquipmentLoanItem;
use App\Models\AppNotification;
use Illuminate\Support\Facades\DB;

class CheckInService
{
    public function process(array $data, int $userId): CheckIn
    {
        return DB::transaction(function () use ($data, $userId) {
            $loan = EquipmentLoan::where('loan_code', $data['loan_code'])
                ->with('items.equipment')
                ->lockForUpdate()
                ->firstOrFail();

            if ($data['action'] === 'check_in') {
                return $this->handleCheckIn($loan, $data, $userId);
            }

            return $this->handleCheckOut($loan, $data, $userId);
        });
    }

    private function handleCheckIn(EquipmentLoan $loan, array $data, int $userId): CheckIn
    {
        if ($loan->status !== 'approved') {
            throw new \Exception('Peminjaman harus berstatus approved untuk dapat di-check-in.', 422);
        }

        $alreadyCheckedIn = CheckIn::where('equipment_loan_id', $loan->id)
            ->where('action', 'check_in')
            ->exists();

        if ($alreadyCheckedIn) {
            throw new \Exception('Peminjaman ini sudah di-check-in sebelumnya.', 422);
        }

        // Mark all items as checked in
        $loan->items()->update(['check_in_at' => now()]);

        // Update loan status to active
        $loan->update(['status' => 'active']);

        $checkIn = CheckIn::create([
            'equipment_loan_id' => $loan->id,
            'user_id'           => $userId,
            'device_id'         => $data['device_id'] ?? null,
            'action'            => 'check_in',
            'checked_at'        => now(),
            'latitude'          => $data['latitude'] ?? null,
            'longitude'         => $data['longitude'] ?? null,
            'notes'             => $data['notes'] ?? null,
        ]);

        AppNotification::create([
            'user_id' => $loan->user_id,
            'type'    => 'loan_checkin',
            'title'   => 'Check-in Berhasil',
            'message' => "Peralatan untuk peminjaman {$loan->loan_code} berhasil diambil.",
            'data'    => ['loan_id' => $loan->id],
        ]);

        return $checkIn->load(['loan.items.equipment', 'user']);
    }

    private function handleCheckOut(EquipmentLoan $loan, array $data, int $userId): CheckIn
    {
        if ($loan->status !== 'active') {
            throw new \Exception('Peminjaman harus berstatus active untuk dapat di-check-out.', 422);
        }

        // Update each item on return
        foreach ($loan->items as $item) {
            $item->update(['check_out_at' => now()]);
            // Restore quantity_available
            Equipment::find($item->equipment_id)?->increment('quantity_available', $item->quantity);
        }

        $loan->update([
            'status'      => 'returned',
            'returned_at' => now(),
        ]);

        $checkIn = CheckIn::create([
            'equipment_loan_id' => $loan->id,
            'user_id'           => $userId,
            'device_id'         => $data['device_id'] ?? null,
            'action'            => 'check_out',
            'checked_at'        => now(),
            'latitude'          => $data['latitude'] ?? null,
            'longitude'         => $data['longitude'] ?? null,
            'notes'             => $data['notes'] ?? null,
        ]);

        AppNotification::create([
            'user_id' => $loan->user_id,
            'type'    => 'loan_checkout',
            'title'   => 'Check-out Berhasil',
            'message' => "Peralatan untuk peminjaman {$loan->loan_code} berhasil dikembalikan.",
            'data'    => ['loan_id' => $loan->id],
        ]);

        return $checkIn->load(['loan', 'user']);
    }
}
