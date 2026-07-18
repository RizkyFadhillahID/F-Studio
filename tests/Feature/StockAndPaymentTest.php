<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\EquipmentLoan;
use App\Models\EquipmentLoanItem;
use App\Models\Room;
use App\Models\User;
use App\Services\BookingService;
use App\Services\EquipmentLoanService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StockAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function member(): User
    {
        return User::create([
            'name' => 'Member', 'email' => 'm' . uniqid() . '@t.id',
            'password' => Hash::make('password123'), 'role' => 'member', 'is_active' => true,
        ]);
    }

    private function equipment(int $total = 3): Equipment
    {
        $cat = Category::create(['name' => 'Cat' . uniqid(), 'description' => null]);

        return Equipment::create([
            'category_id' => $cat->id, 'name' => 'Cam', 'code' => 'C' . uniqid(),
            'quantity_total' => $total, 'quantity_available' => $total, 'is_active' => true,
        ]);
    }

    private function approvedLoan(User $member, Equipment $eq, int $qty = 2): EquipmentLoan
    {
        $loan = EquipmentLoan::create([
            'user_id' => $member->id, 'loan_code' => EquipmentLoan::generateCode(),
            'loan_date' => now()->toDateString(), 'purpose' => 'x', 'status' => 'approved',
            'due_date' => now()->addDay()->toDateString(),
        ]);
        EquipmentLoanItem::create([
            'equipment_loan_id' => $loan->id, 'equipment_id' => $eq->id, 'quantity' => $qty,
        ]);

        return $loan;
    }

    public function test_process_return_restocks_all_items_without_explicit_payload(): void
    {
        $eq = $this->equipment(3);
        $loan = $this->approvedLoan($this->member(), $eq, 2);

        // Simulasikan stok sudah terpotong saat approve.
        $eq->update(['quantity_available' => 1]);

        // Tombol "Kembalikan" cepat mengirim payload kosong.
        app(EquipmentLoanService::class)->processReturn($loan, [], 1);

        $this->assertEquals('returned', $loan->fresh()->status);
        $this->assertEquals(3, $eq->fresh()->quantity_available, 'Stok harus kembali penuh setelah dikembalikan.');
    }

    public function test_loan_updatestatus_to_returned_restores_stock(): void
    {
        $admin = User::create(['name' => 'A', 'email' => 'a' . uniqid() . '@t.id', 'password' => Hash::make('x'), 'role' => 'admin', 'is_active' => true]);
        $eq = $this->equipment(3);
        $loan = $this->approvedLoan($this->member(), $eq, 2);
        $eq->update(['quantity_available' => 1]); // stok terpotong saat approve

        app(EquipmentLoanService::class)->updateStatus($loan, 'returned', $admin->id, null);

        $this->assertEquals('returned', $loan->fresh()->status);
        $this->assertNotNull($loan->fresh()->returned_at);
        $this->assertEquals(3, $eq->fresh()->quantity_available);
    }

    public function test_booking_completed_restores_linked_loan_stock(): void
    {
        $admin = User::create(['name' => 'A', 'email' => 'a' . uniqid() . '@t.id', 'password' => Hash::make('x'), 'role' => 'admin', 'is_active' => true]);
        $member = $this->member();
        $room = Room::create(['name' => 'R', 'code' => 'R' . uniqid(), 'capacity' => 4, 'is_active' => true]);
        $eq = $this->equipment(2);

        $booking = Booking::create([
            'user_id' => $member->id, 'room_id' => $room->id, 'booking_code' => Booking::generateCode(),
            'title' => 't', 'start_datetime' => now()->addDay(), 'end_datetime' => now()->addDay()->addHours(2),
            'status' => 'approved',
        ]);
        $loan = EquipmentLoan::create([
            'user_id' => $member->id, 'booking_id' => $booking->id, 'loan_code' => EquipmentLoan::generateCode(),
            'loan_date' => now()->toDateString(), 'purpose' => 'x', 'status' => 'approved',
            'due_date' => now()->addDay()->toDateString(),
        ]);
        EquipmentLoanItem::create(['equipment_loan_id' => $loan->id, 'equipment_id' => $eq->id, 'quantity' => 1]);
        $eq->update(['quantity_available' => 1]);

        app(BookingService::class)->updateStatus($booking, 'completed', $admin->id, null);

        $this->assertEquals('completed', $booking->fresh()->status);
        $this->assertEquals('returned', $loan->fresh()->status);
        $this->assertEquals(2, $eq->fresh()->quantity_available);
    }

    public function test_pay_booking_marks_paid_with_amount(): void
    {
        $room = Room::create(['name' => 'R', 'code' => 'R' . uniqid(), 'capacity' => 4, 'is_active' => true]);
        $booking = Booking::create([
            'user_id' => $this->member()->id, 'room_id' => $room->id, 'booking_code' => Booking::generateCode(),
            'title' => 't', 'start_datetime' => now()->setTime(10, 0), 'end_datetime' => now()->setTime(13, 0),
            'status' => 'approved',
        ]);

        app(PaymentService::class)->payBooking($booking, 'qris');

        $fresh = $booking->fresh();
        $this->assertEquals('paid', $fresh->payment_status);
        $this->assertEquals('qris', $fresh->payment_method);
        $this->assertEquals(150000, (int) $fresh->amount, '3 jam × Rp50.000 = Rp150.000');
        $this->assertNotNull($fresh->paid_at);
    }

    public function test_cannot_pay_unapproved_booking(): void
    {
        $room = Room::create(['name' => 'R', 'code' => 'R' . uniqid(), 'capacity' => 4, 'is_active' => true]);
        $booking = Booking::create([
            'user_id' => $this->member()->id, 'room_id' => $room->id, 'booking_code' => Booking::generateCode(),
            'title' => 't', 'start_datetime' => now()->addDay(), 'end_datetime' => now()->addDay()->addHour(),
            'status' => 'pending',
        ]);

        $this->expectException(\Exception::class);
        app(PaymentService::class)->payBooking($booking, 'cash');
    }

    public function test_member_can_cancel_unpaid_booking(): void
    {
        $member = $this->member();
        $room = Room::create(['name' => 'R', 'code' => 'R' . uniqid(), 'capacity' => 4, 'is_active' => true]);
        $booking = Booking::create([
            'user_id' => $member->id, 'room_id' => $room->id, 'booking_code' => Booking::generateCode(),
            'title' => 't', 'start_datetime' => now()->addDay()->setTime(10, 0), 'end_datetime' => now()->addDay()->setTime(13, 0),
            'status' => 'approved',
        ]);

        $response = $this->actingAs($member)
            ->post("/member/bookings/{$booking->id}/cancel");

        $response->assertSessionHas('success');
        $this->assertEquals('cancelled', $booking->fresh()->status);
    }

    public function test_member_cannot_cancel_paid_booking(): void
    {
        $member = $this->member();
        $room = Room::create(['name' => 'R', 'code' => 'R' . uniqid(), 'capacity' => 4, 'is_active' => true]);
        $booking = Booking::create([
            'user_id' => $member->id, 'room_id' => $room->id, 'booking_code' => Booking::generateCode(),
            'title' => 't', 'start_datetime' => now()->addDay()->setTime(10, 0), 'end_datetime' => now()->addDay()->setTime(13, 0),
            'status' => 'approved',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'amount' => 150000,
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($member)
            ->post("/member/bookings/{$booking->id}/cancel");

        $response->assertSessionHas('error', 'Pemesanan yang sudah dibayar tidak dapat dibatalkan.');
        $this->assertEquals('approved', $booking->fresh()->status);
    }
}
