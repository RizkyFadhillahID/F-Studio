<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\EquipmentLoan;
use App\Models\EquipmentLoanItem;
use App\Models\AppNotification;
use Illuminate\Support\Facades\DB;

class EquipmentLoanService
{
    public function create(array $data, int $userId): EquipmentLoan
    {
        return DB::transaction(function () use ($data, $userId) {
            $loanDate = $data['loan_date'] ?? now()->toDateString();
            $dueDate  = $data['due_date'];

            // Validate availability for each item (time-based)
            foreach ($data['items'] as $item) {
                $equipment = Equipment::findOrFail($item['equipment_id']);

                if (!$equipment->is_active) {
                    throw new \Exception("Peralatan '{$equipment->name}' tidak aktif.", 422);
                }

                // Cek konflik jadwal berdasarkan rentang tanggal (time-based)
                // Tidak menggunakan quantity_available (kolom fisik) karena kolom itu
                // mencerminkan stok yang sedang aktif dipinjam sekarang, bukan di tanggal loan_date.
                if (!$equipment->isAvailableBetween($loanDate, $dueDate, $item['quantity'])) {
                    $reserved  = $equipment->countReservedBetween($loanDate, $dueDate);
                    $available = $equipment->quantity_total - $reserved;
                    throw new \Exception(
                        "'{$equipment->name}' tidak tersedia pada tanggal tersebut. " .
                            "Unit tersedia: {$available}, diminta: {$item['quantity']}.",
                        422
                    );
                }
            }

            // Auto-approved layaknya booking ruangan: kurangi stok langsung saat
            // dibuat, tidak menunggu langkah persetujuan admin terpisah.
            foreach ($data['items'] as $item) {
                $equipment = Equipment::lockForUpdate()->find($item['equipment_id']);
                if ($equipment->quantity_available < $item['quantity']) {
                    throw new \Exception(
                        "Stok '{$equipment->name}' sudah tidak mencukupi saat diproses.",
                        422
                    );
                }
                $equipment->decrement('quantity_available', $item['quantity']);
            }

            $loan = EquipmentLoan::create([
                'user_id'        => $userId,
                'booking_id'     => $data['booking_id'] ?? null,
                'loan_code'      => EquipmentLoan::generateCode(),
                'loan_date'      => $loanDate,
                'customer_name'  => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'purpose'        => $data['purpose'],
                'status'         => 'approved',
                'approved_at'    => now(),
                'notes'          => $data['notes'] ?? null,
                'due_date'       => $dueDate,
            ]);

            foreach ($data['items'] as $item) {
                EquipmentLoanItem::create([
                    'equipment_loan_id' => $loan->id,
                    'equipment_id'      => $item['equipment_id'],
                    'quantity'          => $item['quantity'],
                    'notes'             => $item['notes'] ?? null,
                ]);
            }

            AppNotification::create([
                'user_id' => $userId,
                'type'    => 'loan_approved',
                'title'   => 'Peminjaman Disetujui',
                'message' => "Peminjaman peralatan {$loan->loan_code} disetujui secara otomatis. Silakan lakukan pembayaran.",
                'data'    => ['loan_id' => $loan->id],
            ]);

            return $loan->load(['user', 'items.equipment', 'booking']);
        });
    }

    public function approve(EquipmentLoan $loan, int $adminId, ?string $adminNotes): EquipmentLoan
    {
        return DB::transaction(function () use ($loan, $adminId, $adminNotes) {
            if ($loan->status !== 'pending') {
                throw new \Exception('Hanya peminjaman berstatus pending yang dapat disetujui.', 422);
            }

            // Decrement available quantity
            foreach ($loan->items as $item) {
                $equipment = Equipment::lockForUpdate()->find($item->equipment_id);
                if ($equipment->quantity_available < $item->quantity) {
                    throw new \Exception(
                        "Stok '{$equipment->name}' sudah tidak mencukupi saat diproses.",
                        422
                    );
                }
                $equipment->decrement('quantity_available', $item->quantity);
            }

            $loan->update([
                'status'      => 'approved',
                'approved_by' => $adminId,
                'approved_at' => now(),
                'admin_notes' => $adminNotes,
            ]);

            AppNotification::create([
                'user_id' => $loan->user_id,
                'type'    => 'loan_approved',
                'title'   => 'Peminjaman Disetujui',
                'message' => "Peminjaman {$loan->loan_code} telah disetujui.",
                'data'    => ['loan_id' => $loan->id],
            ]);

            return $loan->fresh(['user', 'items.equipment', 'approvedBy']);
        });
    }

    public function reject(EquipmentLoan $loan, int $adminId, string $adminNotes): EquipmentLoan
    {
        return DB::transaction(function () use ($loan, $adminId, $adminNotes) {
            if ($loan->status !== 'pending') {
                throw new \Exception('Hanya peminjaman berstatus pending yang dapat ditolak.', 422);
            }

            $loan->update([
                'status'      => 'rejected',
                'approved_by' => $adminId,
                'approved_at' => now(),
                'admin_notes' => $adminNotes,
            ]);

            AppNotification::create([
                'user_id' => $loan->user_id,
                'type'    => 'loan_rejected',
                'title'   => 'Peminjaman Ditolak',
                'message' => "Peminjaman {$loan->loan_code} ditolak. Alasan: {$adminNotes}",
                'data'    => ['loan_id' => $loan->id],
            ]);

            return $loan->fresh(['user', 'items.equipment', 'approvedBy']);
        });
    }

    public function markOverdue(): int
    {
        return EquipmentLoan::whereIn('status', ['approved', 'active'])
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);
    }

    /**
     * Ubah status loan secara bebas (admin).
     * Mengelola quantity_available secara otomatis.
     */
    public function updateStatus(EquipmentLoan $loan, string $newStatus, int $adminId, ?string $adminNotes): EquipmentLoan
    {
        $allowed = ['pending', 'approved', 'rejected', 'cancelled', 'returned'];
        if (!in_array($newStatus, $allowed)) {
            throw new \Exception('Status tidak valid.', 422);
        }

        return DB::transaction(function () use ($loan, $newStatus, $adminId, $adminNotes) {
            $oldStatus = $loan->status;

            if ($oldStatus === $newStatus) {
                throw new \Exception('Status sudah sama, tidak ada perubahan.', 422);
            }

            $loan->load('items.equipment');

            $stockDeducted = in_array($oldStatus, ['approved', 'active', 'overdue']);
            $needsDeduct   = $newStatus === 'approved';
            // 'returned' juga mengembalikan stok (peminjaman selesai).
            $needsRestore  = $stockDeducted && in_array($newStatus, ['pending', 'rejected', 'cancelled', 'returned']);

            if ($needsRestore) {
                foreach ($loan->items as $item) {
                    Equipment::lockForUpdate()->find($item->equipment_id)
                        ?->increment('quantity_available', $item->quantity);
                }
            }

            if ($needsDeduct && !$stockDeducted) {
                foreach ($loan->items as $item) {
                    $eq = Equipment::lockForUpdate()->find($item->equipment_id);
                    if (! $eq || $eq->quantity_available < $item->quantity) {
                        throw new \Exception(
                            "Stok '{$item->equipment?->name}' tidak mencukupi untuk menyetujui peminjaman ini.",
                            422
                        );
                    }
                    $eq->decrement('quantity_available', $item->quantity);
                }
            }

            $loan->update([
                'status'      => $newStatus,
                'approved_by' => $adminId,
                'approved_at' => $newStatus === 'approved' ? now() : $loan->approved_at,
                'returned_at' => $newStatus === 'returned' ? now() : $loan->returned_at,
                'admin_notes' => $adminNotes,
            ]);

            AppNotification::create([
                'user_id' => $loan->user_id,
                'type'    => 'loan_status_changed',
                'title'   => 'Status Peminjaman Diperbarui',
                'message' => "Status peminjaman {$loan->loan_code} diubah menjadi {$newStatus}.",
                'data'    => ['loan_id' => $loan->id],
            ]);

            return $loan->fresh(['user', 'items.equipment', 'approvedBy']);
        });
    }

    /**
     * Proses pengembalian peralatan: restock tiap item, tandai returned.
     *
     * $returnData = [
     *   'notes' => string|null,
     *   'items' => [
     *     ['id' => int, 'notes' => string|null],
     *     ...
     *   ]
     * ]
     */
    public function processReturn(EquipmentLoan $loan, array $returnData, int $userId): EquipmentLoan
    {
        if (!in_array($loan->status, ['approved', 'active', 'overdue'])) {
            throw new \Exception('Hanya peminjaman yang sudah disetujui/aktif yang dapat dikembalikan.', 422);
        }

        return DB::transaction(function () use ($loan, $returnData, $userId) {
            $itemsById = $loan->items->keyBy('id');

            // Jika tidak ada daftar item eksplisit (tombol "Kembalikan" cepat
            // di admin/resepsionis tidak mengumpulkan kondisi per-item),
            // anggap SEMUA item pada peminjaman ini dikembalikan.
            $itemsToReturn = !empty($returnData['items'])
                ? $returnData['items']
                : $itemsById->map(fn ($item) => ['id' => $item->id])->values()->all();

            foreach ($itemsToReturn as $itemReturn) {
                $item = $itemsById->get($itemReturn['id']);
                if (!$item) {
                    continue;
                }

                if (!empty($itemReturn['notes'])) {
                    $item->update([
                        'notes'        => trim(($item->notes ?? '') . ' | Kembali: ' . $itemReturn['notes']),
                        'check_out_at' => now(),
                    ]);
                } else {
                    $item->update(['check_out_at' => now()]);
                }

                // Restock: kembalikan unit ke stok tersedia
                Equipment::lockForUpdate()->find($item->equipment_id)
                    ?->increment('quantity_available', $item->quantity);
            }

            $returnNotes = trim(($loan->admin_notes ?? '') . (!empty($returnData['notes']) ? ' | Pengembalian: ' . $returnData['notes'] : ''));

            $loan->update([
                'status'      => 'returned',
                'returned_at' => now(),
                'admin_notes' => $returnNotes ?: $loan->admin_notes,
            ]);

            AppNotification::create([
                'user_id' => $loan->user_id,
                'type'    => 'loan_returned',
                'title'   => 'Peralatan Dikembalikan',
                'message' => "Peminjaman {$loan->loan_code} selesai. Peralatan telah dikembalikan dan stok diperbarui.",
                'data'    => ['loan_id' => $loan->id],
            ]);

            return $loan->fresh(['user', 'items.equipment', 'approvedBy']);
        });
    }
}
