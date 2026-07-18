<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\CheckIn;
use App\Models\Equipment;
use App\Models\EquipmentLoan;
use App\Models\EquipmentLoanItem;
use App\Models\Room;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data transaksi contoh: 3 dari rizky@gmail.com, 5 dari member lain (masing-masing
 * 1), dan 10 dari resepsionis (atas nama pelanggan walk-in). Menghapus transaksi
 * lama dulu (idempotent) lalu membuat ulang dengan variasi status & pembayaran.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::where('email', 'admin@gmail.com')->first();
        $resep1 = User::where('email', 'resepsionis01@gmail.com')->first();
        $resep2 = User::where('email', 'resepsionis02@gmail.com')->first();

        $rizky = User::where('email', 'rizky@gmail.com')->first();
        $dimas = User::where('email', 'dimas@gmail.com')->first();
        $putri = User::where('email', 'putri@gmail.com')->first();
        $fajar = User::where('email', 'fajar@gmail.com')->first();
        $ayu   = User::where('email', 'ayu@gmail.com')->first();
        $bagas = User::where('email', 'bagas@gmail.com')->first();

        $rooms = Room::pluck('id', 'code');
        $eq    = Equipment::pluck('id', 'code');

        if (! $admin || ! $resep1 || ! $rizky || $rooms->isEmpty() || $eq->isEmpty()) {
            $this->command?->warn('DemoDataSeeder dilewati: master data belum lengkap.');

            return;
        }

        $payment = app(PaymentService::class);

        // Bersihkan transaksi lama supaya seeder aman dijalankan ulang.
        EquipmentLoanItem::query()->delete();
        CheckIn::query()->delete();
        EquipmentLoan::query()->delete();
        Booking::query()->delete();

        $bookingSeq = 1;
        $loanSeq    = 1;

        $makeBooking = function (array $attrs) use (&$bookingSeq, $admin) {
            $defaults = [
                'booking_code'    => sprintf('BK-DEMO-%04d', $bookingSeq++),
                'status'          => 'approved',
                'approved_by'     => $admin->id,
                'approved_at'     => now(),
                'payment_status'  => 'unpaid',
            ];

            return Booking::create(array_merge($defaults, $attrs));
        };

        $makeLoan = function (array $attrs, array $items) use (&$loanSeq, $admin, $eq) {
            $defaults = [
                'loan_code'      => sprintf('LN-DEMO-%04d', $loanSeq++),
                'status'         => 'approved',
                'approved_by'    => $admin->id,
                'approved_at'    => now(),
                'payment_status' => 'unpaid',
            ];

            $loan = EquipmentLoan::create(array_merge($defaults, $attrs));

            foreach ($items as $item) {
                if (! isset($eq[$item['code']])) {
                    continue;
                }
                EquipmentLoanItem::create([
                    'equipment_loan_id' => $loan->id,
                    'equipment_id'      => $eq[$item['code']],
                    'quantity'          => $item['qty'],
                    'check_in_at'       => $item['check_in_at'] ?? null,
                    'check_out_at'      => $item['check_out_at'] ?? null,
                ]);
            }

            return $loan->fresh('items');
        };

        $markPaid = function ($model, string $method) use ($payment) {
            $amount = $model instanceof Booking ? $payment->bookingAmount($model) : $payment->loanAmount($model);
            $model->update([
                'payment_status' => 'paid',
                'payment_method' => $method,
                'amount'         => $amount,
                'paid_at'        => now(),
            ]);
        };

        // ── 1) Rizky — 3 transaksi ───────────────────────────────────────────
        $markPaid($makeBooking([
            'user_id' => $rizky->id, 'room_id' => $rooms['STD-01'],
            'title' => 'Pemotretan Produk Katalog', 'status' => 'approved',
            'start_datetime' => now()->addDay()->setTime(10, 0), 'end_datetime' => now()->addDay()->setTime(12, 0),
        ]), 'qris');

        $markPaid($makeBooking([
            'user_id' => $rizky->id, 'room_id' => $rooms['EDT-01'],
            'title' => 'Color Grading Video Promosi', 'status' => 'completed',
            'start_datetime' => now()->subDays(3)->setTime(9, 0), 'end_datetime' => now()->subDays(3)->setTime(13, 0),
        ]), 'transfer');

        $makeLoan([
            'user_id' => $rizky->id, 'purpose' => 'Sesi foto produk mandiri',
            'loan_date' => now()->toDateString(), 'due_date' => now()->addDays(2)->toDateString(),
        ], [['code' => 'CAM-001', 'qty' => 1], ['code' => 'TRP-001', 'qty' => 1]]);

        // ── 2) 5 member lain — 1 transaksi masing-masing ─────────────────────
        $markPaid($makeBooking([
            'user_id' => $dimas->id, 'room_id' => $rooms['STD-02'],
            'title' => 'Syuting Video Company Profile', 'status' => 'approved',
            'start_datetime' => now()->addDays(2)->setTime(13, 0), 'end_datetime' => now()->addDays(2)->setTime(16, 0),
        ]), 'ewallet');

        $makeLoan([
            'user_id' => $putri->id, 'purpose' => 'Rekaman podcast mingguan',
            'loan_date' => now()->toDateString(), 'due_date' => now()->addDay()->toDateString(),
        ], [['code' => 'AUD-003', 'qty' => 2]]);

        $makeBooking([
            'user_id' => $fajar->id, 'room_id' => $rooms['POD-01'],
            'title' => 'Rekaman Podcast Bisnis', 'status' => 'cancelled',
            'start_datetime' => now()->addDays(5)->setTime(14, 0), 'end_datetime' => now()->addDays(5)->setTime(16, 0),
        ]);

        $loanAyu = $makeLoan([
            'user_id' => $ayu->id, 'purpose' => 'Pemotretan luar ruangan',
            'loan_date' => now()->subDays(5)->toDateString(), 'due_date' => now()->subDays(3)->toDateString(),
            'status' => 'returned', 'returned_at' => now()->subDays(3),
        ], [
            ['code' => 'LGT-001', 'qty' => 2, 'check_in_at' => now()->subDays(5), 'check_out_at' => now()->subDays(3)],
            ['code' => 'LGT-002', 'qty' => 2, 'check_in_at' => now()->subDays(5), 'check_out_at' => now()->subDays(3)],
        ]);
        $markPaid($loanAyu, 'cash');

        $makeBooking([
            'user_id' => $bagas->id, 'room_id' => $rooms['CWK-01'],
            'title' => 'Workshop Komunitas', 'status' => 'rejected',
            'start_datetime' => now()->addDays(6)->setTime(9, 0), 'end_datetime' => now()->addDays(6)->setTime(17, 0),
            'admin_notes' => 'Kapasitas melebihi batas untuk tanggal tersebut.',
        ]);

        // ── 3) 10 transaksi dari resepsionis (atas nama pelanggan walk-in) ───
        $markPaid($makeBooking([
            'user_id' => $resep1->id, 'room_id' => $rooms['MTG-01'],
            'customer_name' => 'Joko Susilo', 'customer_phone' => '081311110001',
            'title' => 'Pitching Klien Baru', 'status' => 'approved',
            'start_datetime' => now()->addDay()->setTime(13, 0), 'end_datetime' => now()->addDay()->setTime(15, 0),
        ]), 'cash');

        $makeLoan([
            'user_id' => $resep1->id, 'customer_name' => 'Tania Putri', 'customer_phone' => '081311110002',
            'purpose' => 'Foto pre-wedding klien',
            'loan_date' => now()->addDay()->toDateString(), 'due_date' => now()->addDays(3)->toDateString(),
        ], [['code' => 'CAM-003', 'qty' => 1], ['code' => 'LNS-002', 'qty' => 1]]);

        $markPaid($makeBooking([
            'user_id' => $resep1->id, 'room_id' => $rooms['STD-01'],
            'customer_name' => 'Rendi Saputra', 'customer_phone' => '081311110003',
            'title' => 'Sesi Foto Keluarga', 'status' => 'completed',
            'start_datetime' => now()->subDays(2)->setTime(9, 0), 'end_datetime' => now()->subDays(2)->setTime(11, 0),
        ]), 'qris');

        $loanNina = $makeLoan([
            'user_id' => $resep1->id, 'customer_name' => 'Nina Kartika', 'customer_phone' => '081311110004',
            'purpose' => 'Footage udara promosi kampus', 'status' => 'active',
            'loan_date' => now()->toDateString(), 'due_date' => now()->addDays(2)->toDateString(),
        ], [['code' => 'DRN-001', 'qty' => 1, 'check_in_at' => now()]]);

        $makeBooking([
            'user_id' => $resep1->id, 'room_id' => $rooms['EDT-01'],
            'customer_name' => 'Sinta Marlina', 'customer_phone' => '081311110005',
            'title' => 'Editing Wedding Video', 'status' => 'approved',
            'start_datetime' => now()->addDays(3)->setTime(10, 0), 'end_datetime' => now()->addDays(3)->setTime(15, 0),
        ]);

        $loanAgus = $makeLoan([
            'user_id' => $resep2->id, 'customer_name' => 'Agus Salim', 'customer_phone' => '081311110006',
            'purpose' => 'Live streaming turnamen komunitas', 'status' => 'returned',
            'loan_date' => now()->subDays(7)->toDateString(), 'due_date' => now()->subDays(5)->toDateString(),
            'returned_at' => now()->subDays(5),
        ], [
            ['code' => 'CAM-001', 'qty' => 1, 'check_in_at' => now()->subDays(7), 'check_out_at' => now()->subDays(5)],
            ['code' => 'AUD-002', 'qty' => 1, 'check_in_at' => now()->subDays(7), 'check_out_at' => now()->subDays(5)],
        ]);
        $markPaid($loanAgus, 'transfer');

        $markPaid($makeBooking([
            'user_id' => $resep2->id, 'room_id' => $rooms['STD-02'],
            'customer_name' => 'Rina Wijaya', 'customer_phone' => '081311110007',
            'title' => 'Produksi Video Musik Indie', 'status' => 'approved',
            'start_datetime' => now()->addDays(4)->setTime(10, 0), 'end_datetime' => now()->addDays(4)->setTime(18, 0),
        ]), 'ewallet');

        $loanDoni = $makeLoan([
            'user_id' => $resep2->id, 'customer_name' => 'Doni Pratama', 'customer_phone' => '081311110008',
            'purpose' => 'Syuting dokumenter singkat', 'status' => 'overdue',
            'loan_date' => now()->subDays(4)->toDateString(), 'due_date' => now()->subDay()->toDateString(),
        ], [
            ['code' => 'TRP-001', 'qty' => 1, 'check_in_at' => now()->subDays(4)],
            ['code' => 'GIM-001', 'qty' => 1, 'check_in_at' => now()->subDays(4)],
        ]);

        $markPaid($makeBooking([
            'user_id' => $resep2->id, 'room_id' => $rooms['CWK-01'],
            'customer_name' => 'Komunitas Film Pendek', 'customer_phone' => '081311110009',
            'title' => 'Meetup Bulanan Kreator', 'status' => 'approved',
            'start_datetime' => now()->addDays(2)->setTime(15, 0), 'end_datetime' => now()->addDays(2)->setTime(18, 0),
        ]), 'cash');

        $makeLoan([
            'user_id' => $resep2->id, 'customer_name' => 'Lestari Putri', 'customer_phone' => '081311110010',
            'purpose' => 'Konten review produk kecantikan',
            'loan_date' => now()->addDay()->toDateString(), 'due_date' => now()->addDays(2)->toDateString(),
        ], [['code' => 'CAM-002', 'qty' => 1], ['code' => 'LNS-001', 'qty' => 1]]);

        // ── Log check-in (jejak serah-terima via API tablet) ─────────────────
        $checkInLogs = [
            [$loanNina, [['action' => 'check_in', 'at' => now()]]],
            [$loanAgus, [['action' => 'check_in', 'at' => now()->subDays(7)], ['action' => 'check_out', 'at' => now()->subDays(5)]]],
            [$loanDoni, [['action' => 'check_in', 'at' => now()->subDays(4)]]],
            [$loanAyu,  [['action' => 'check_in', 'at' => now()->subDays(5)], ['action' => 'check_out', 'at' => now()->subDays(3)]]],
        ];

        foreach ($checkInLogs as [$loan, $logs]) {
            foreach ($logs as $log) {
                CheckIn::create([
                    'equipment_loan_id' => $loan->id,
                    'user_id'           => $loan->user_id,
                    'device_id'         => 'TABLET-01',
                    'action'            => $log['action'],
                    'checked_at'        => $log['at'],
                    'notes'             => $log['action'] === 'check_in' ? 'Alat diambil di front desk.' : 'Alat dikembalikan, kondisi baik.',
                ]);
            }
        }

        // ── Rekonsiliasi stok ─────────────────────────────────────────────────
        // quantity_available = quantity_total - unit pada loan approved/active/overdue.
        $reserved = EquipmentLoanItem::whereHas(
            'loan',
            fn ($q) => $q->whereIn('status', ['approved', 'active', 'overdue'])
        )
            ->select('equipment_id', DB::raw('SUM(quantity) as qty'))
            ->groupBy('equipment_id')
            ->pluck('qty', 'equipment_id');

        foreach (Equipment::all() as $equipment) {
            $equipment->update([
                'quantity_available' => max(0, $equipment->quantity_total - (int) ($reserved[$equipment->id] ?? 0)),
            ]);
        }
    }
}
