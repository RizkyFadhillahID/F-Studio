<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\EquipmentLoan;
use App\Models\Equipment;
use App\Models\Room;
use App\Models\AppNotification;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function create(array $data, int $userId): Booking
    {
        return DB::transaction(function () use ($data, $userId) {
            $room = Room::findOrFail($data['room_id']);

            if (!$room->is_active) {
                throw new \Exception('Ruang tidak aktif dan tidak dapat dipesan.', 422);
            }

            if (!$room->isAvailable($data['start_datetime'], $data['end_datetime'])) {
                throw new \Exception('Ruang sudah dipesan pada waktu yang dipilih.', 422);
            }

            $booking = Booking::create([
                'user_id'        => $userId,
                'room_id'        => $data['room_id'],
                'booking_code'   => Booking::generateCode(),
                'customer_name'  => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'title'          => $data['title'],
                'start_datetime' => $data['start_datetime'],
                'end_datetime'   => $data['end_datetime'],
                'notes'          => $data['notes'] ?? null,
                'status'         => 'approved',
                'approved_at'    => now(),
            ]);

            $this->createNotification(
                $userId,
                'booking_approved',
                'Pemesanan Disetujui',
                "Pemesanan ruang {$room->name} dengan kode {$booking->booking_code} disetujui secara otomatis. Silakan lakukan pembayaran.",
                ['booking_id' => $booking->id]
            );

            return $booking->load(['user', 'room']);
        });
    }

    public function approve(Booking $booking, int $adminId, ?string $adminNotes): Booking
    {
        return DB::transaction(function () use ($booking, $adminId, $adminNotes) {
            if ($booking->status !== 'pending') {
                throw new \Exception('Hanya pemesanan berstatus pending yang dapat disetujui.', 422);
            }

            $booking->update([
                'status'      => 'approved',
                'approved_by' => $adminId,
                'approved_at' => now(),
                'admin_notes' => $adminNotes,
            ]);

            // Auto-approve linked equipment loans
            $linkedLoans = EquipmentLoan::where('booking_id', $booking->id)
                ->where('status', 'pending')
                ->with('items.equipment')
                ->get();

            foreach ($linkedLoans as $loan) {
                foreach ($loan->items as $item) {
                    $equipment = Equipment::lockForUpdate()->find($item->equipment_id);
                    if (! $equipment || $equipment->quantity_available < $item->quantity) {
                        throw new \Exception(
                            "Stok '{$item->equipment?->name}' tidak mencukupi untuk peminjaman terkait booking ini.",
                            422
                        );
                    }
                    $equipment->decrement('quantity_available', $item->quantity);
                }
                $loan->update([
                    'status'      => 'approved',
                    'approved_by' => $adminId,
                    'approved_at' => now(),
                    'admin_notes' => 'Disetujui otomatis bersama booking ' . $booking->booking_code,
                ]);

                AppNotification::create([
                    'user_id' => $loan->user_id,
                    'type'    => 'loan_approved',
                    'title'   => 'Peminjaman Peralatan Disetujui',
                    'message' => "Peminjaman {$loan->loan_code} disetujui bersama booking {$booking->booking_code}.",
                    'data'    => ['loan_id' => $loan->id],
                ]);
            }

            $this->createNotification(
                $booking->user_id,
                'booking_approved',
                'Pemesanan Disetujui',
                "Pemesanan {$booking->booking_code} telah disetujui.",
                ['booking_id' => $booking->id]
            );

            return $booking->fresh(['user', 'room', 'approvedBy']);
        });
    }

    public function reject(Booking $booking, int $adminId, string $adminNotes): Booking
    {
        return DB::transaction(function () use ($booking, $adminId, $adminNotes) {
            if ($booking->status !== 'pending') {
                throw new \Exception('Hanya pemesanan berstatus pending yang dapat ditolak.', 422);
            }

            $booking->update([
                'status'      => 'rejected',
                'approved_by' => $adminId,
                'approved_at' => now(),
                'admin_notes' => $adminNotes,
            ]);

            $this->createNotification(
                $booking->user_id,
                'booking_rejected',
                'Pemesanan Ditolak',
                "Pemesanan {$booking->booking_code} ditolak. Alasan: {$adminNotes}",
                ['booking_id' => $booking->id]
            );

            return $booking->fresh(['user', 'room', 'approvedBy']);
        });
    }

    public function cancel(Booking $booking, int $userId): Booking
    {
        return DB::transaction(function () use ($booking, $userId) {
            if (!in_array($booking->status, ['pending', 'approved'])) {
                throw new \Exception('Pemesanan ini tidak dapat dibatalkan.', 422);
            }

            if ($booking->start_datetime <= now()) {
                throw new \Exception('Tidak dapat membatalkan pemesanan yang sudah dimulai.', 422);
            }

            $booking->update(['status' => 'cancelled']);

            return $booking->fresh(['user', 'room']);
        });
    }

    /**
     * Ubah status booking secara bebas (admin).
     * Mengelola stok peralatan terkait secara otomatis.
     */
    public function updateStatus(Booking $booking, string $newStatus, int $adminId, ?string $adminNotes): Booking
    {
        $allowed = ['pending', 'approved', 'rejected', 'cancelled', 'completed'];
        if (!in_array($newStatus, $allowed)) {
            throw new \Exception('Status tidak valid.', 422);
        }

        return DB::transaction(function () use ($booking, $newStatus, $adminId, $adminNotes) {
            $oldStatus = $booking->status;

            if ($oldStatus === $newStatus) {
                throw new \Exception('Status sudah sama, tidak ada perubahan.', 422);
            }

            $linkedLoans = EquipmentLoan::where('booking_id', $booking->id)
                ->with('items.equipment')
                ->get();

            // approved → pending/rejected/cancelled/completed: kembalikan stok peralatan.
            // Untuk 'completed' peminjaman ditandai 'returned'; selain itu mengikuti
            // status booking (pending kembali ke pending, sisanya rejected/cancelled).
            if ($oldStatus === 'approved' && in_array($newStatus, ['pending', 'rejected', 'cancelled', 'completed'])) {
                $loanStatus = match ($newStatus) {
                    'pending'   => 'pending',
                    'completed' => 'returned',
                    default     => $newStatus,
                };

                foreach ($linkedLoans->where('status', 'approved') as $loan) {
                    foreach ($loan->items as $item) {
                        Equipment::lockForUpdate()->find($item->equipment_id)
                            ?->increment('quantity_available', $item->quantity);
                    }
                    $loan->update([
                        'status'      => $loanStatus,
                        'returned_at' => $newStatus === 'completed' ? now() : $loan->returned_at,
                    ]);
                }
            }

            // pending → approved: potong stok peralatan
            if ($oldStatus === 'pending' && $newStatus === 'approved') {
                foreach ($linkedLoans->where('status', 'pending') as $loan) {
                    foreach ($loan->items as $item) {
                        $eq = Equipment::lockForUpdate()->find($item->equipment_id);
                        if (! $eq || $eq->quantity_available < $item->quantity) {
                            throw new \Exception(
                                "Stok '{$item->equipment?->name}' tidak mencukupi untuk peminjaman terkait booking ini.",
                                422
                            );
                        }
                        $eq->decrement('quantity_available', $item->quantity);
                    }
                    $loan->update([
                        'status'      => 'approved',
                        'approved_by' => $adminId,
                        'approved_at' => now(),
                        'admin_notes' => 'Disetujui otomatis bersama booking ' . $booking->booking_code,
                    ]);
                }
            }

            // rejected/cancelled → pending: tidak perlu ubah stok (belum pernah dipotong)
            // rejected/cancelled → approved: potong stok
            if (in_array($oldStatus, ['rejected', 'cancelled']) && $newStatus === 'approved') {
                foreach ($linkedLoans->where('status', $oldStatus) as $loan) {
                    foreach ($loan->items as $item) {
                        $eq = Equipment::lockForUpdate()->find($item->equipment_id);
                        if (! $eq || $eq->quantity_available < $item->quantity) {
                            throw new \Exception(
                                "Stok '{$item->equipment?->name}' tidak mencukupi untuk peminjaman terkait booking ini.",
                                422
                            );
                        }
                        $eq->decrement('quantity_available', $item->quantity);
                    }
                    $loan->update([
                        'status'      => 'approved',
                        'approved_by' => $adminId,
                        'approved_at' => now(),
                        'admin_notes' => 'Disetujui otomatis bersama booking ' . $booking->booking_code,
                    ]);
                }
            }

            $booking->update([
                'status'      => $newStatus,
                'approved_by' => $adminId,
                'approved_at' => in_array($newStatus, ['approved', 'rejected']) ? now() : $booking->approved_at,
                'admin_notes' => $adminNotes,
            ]);

            $this->createNotification(
                $booking->user_id,
                'booking_status_changed',
                'Status Pemesanan Diperbarui',
                "Status pemesanan {$booking->booking_code} diubah menjadi {$newStatus}.",
                ['booking_id' => $booking->id]
            );

            return $booking->fresh(['user', 'room', 'approvedBy']);
        });
    }

    private function createNotification(int $userId, string $type, string $title, string $message, array $data = []): void
    {
        AppNotification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'data'    => $data,
        ]);
    }
}
