<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\EquipmentLoan;
use App\Models\Room;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    public function index(Request $request)
    {
        $bookings = Booking::with(['user', 'room', 'approvedBy'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->room_id, fn($q) => $q->where('room_id', $request->room_id))
            ->when($request->search, fn($q) =>
            $q->where(
                fn($q2) =>
                $q2->where('booking_code', 'like', "%{$request->search}%")
                    ->orWhere('customer_name', 'like', "%{$request->search}%")
                    ->orWhereHas('user', fn($q3) => $q3->where('name', 'like', "%{$request->search}%"))
            ))
            ->latest()
            ->paginate(10, ['*'], 'bpage');

        // Standalone loans only (not linked to a booking — those show via booking detail)
        $loans = EquipmentLoan::with(['user', 'items.equipment', 'approvedBy'])
            ->whereNull('booking_id')
            ->when($request->loan_status, fn($q) => $q->where('status', $request->loan_status))
            ->when($request->search, fn($q) =>
            $q->where(
                fn($q2) =>
                $q2->where('loan_code', 'like', "%{$request->search}%")
                    ->orWhere('customer_name', 'like', "%{$request->search}%")
                    ->orWhereHas('user', fn($q3) => $q3->where('name', 'like', "%{$request->search}%"))
            ))
            ->latest()
            ->paginate(10, ['*'], 'lpage');

        $rooms = Room::where('is_active', true)->get();

        return view('bookings.index', compact('bookings', 'loans', 'rooms'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'room', 'approvedBy', 'equipmentLoans.items.equipment', 'equipmentLoans.approvedBy']);
        return view('bookings.show', compact('booking'));
    }

    public function approve(Request $request, Booking $booking)
    {
        $request->validate(['admin_notes' => ['nullable', 'string', 'max:1000']]);

        try {
            $this->bookingService->approve($booking, Auth::id(), $request->admin_notes);
            return back()->with('success', 'Pemesanan berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, Booking $booking)
    {
        $request->validate(['admin_notes' => ['required', 'string', 'max:1000']]);

        try {
            $this->bookingService->reject($booking, Auth::id(), $request->admin_notes);
            return back()->with('success', 'Pemesanan berhasil ditolak.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status'      => ['required', 'in:pending,approved,rejected,cancelled'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->bookingService->updateStatus($booking, $request->status, Auth::id(), $request->admin_notes);
            return back()->with('success', 'Status pemesanan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
