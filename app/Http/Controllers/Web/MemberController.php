<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    public function dashboard()
    {
        $myBookings = Booking::where('user_id', Auth::id())
            ->with('room')
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'total'    => Booking::where('user_id', Auth::id())->count(),
            'pending'  => Booking::where('user_id', Auth::id())->where('status', 'pending')->count(),
            'approved' => Booking::where('user_id', Auth::id())->where('status', 'approved')->count(),
        ];

        return view('member.dashboard', compact('myBookings', 'stats'));
    }

    public function rooms(Request $request)
    {
        $rooms = Room::where('is_active', true)
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->get();

        return view('member.rooms', compact('rooms'));
    }

    public function createBooking(Room $room)
    {
        return view('member.booking-form', compact('room'));
    }

    public function storeBooking(Request $request)
    {
        $data = $request->validate([
            'room_id'        => ['required', 'integer', 'exists:rooms,id'],
            'title'          => ['required', 'string', 'max:200'],
            'start_datetime' => ['required', 'date', 'after:now'],
            'end_datetime'   => ['required', 'date', 'after:start_datetime'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ], [
            'start_datetime.after' => 'Waktu mulai harus di masa mendatang.',
            'end_datetime.after'   => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        try {
            $booking = $this->bookingService->create($data, Auth::id());
            return redirect()->route('member.bookings')
                ->with('success', "Pemesanan {$booking->booking_code} berhasil diajukan! Menunggu persetujuan admin.");
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function bookings(Request $request)
    {
        $bookings = Booking::where('user_id', Auth::id())
            ->with('room')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10);

        return view('member.bookings', compact('bookings'));
    }

    public function showBooking(Booking $booking)
    {
        abort_if($booking->user_id !== Auth::id(), 403);
        $booking->load(['room', 'approvedBy']);
        return view('member.booking-detail', compact('booking'));
    }
}
