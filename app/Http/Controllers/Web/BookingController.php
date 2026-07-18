<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    public function index(Request $request)
    {
        $bookings = Booking::with(['user', 'room', 'approvedBy'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q) => $q->where(
                fn ($q2) => $q2->where('booking_code', 'like', "%{$request->search}%")
                    ->orWhere('customer_name', 'like', "%{$request->search}%")
                    ->orWhereHas('user', fn ($q3) => $q3->where('name', 'like', "%{$request->search}%"))
            ))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings,
            'rooms'    => Room::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code', 'price_per_hour']),
            'members'  => User::whereIn('role', ['member', 'receptionist'])->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'filters'  => ['status' => $request->status, 'search' => $request->search],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id'        => ['required', 'exists:rooms,id'],
            'user_id'        => ['nullable', 'exists:users,id'],
            'customer_name'  => ['nullable', 'string', 'max:200'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'title'          => ['required', 'string', 'max:200'],
            'start_datetime' => ['required', 'date', 'after_or_equal:now'],
            'end_datetime'   => ['required', 'date', 'after:start_datetime'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        // Pemilik booking: anggota terpilih, atau admin yang menginput (walk-in).
        $ownerId = $data['user_id'] ?? Auth::id();

        try {
            $this->bookingService->create($data, $ownerId);

            return redirect()->route('bookings.index')->with('success', 'Pemesanan berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status'      => ['required', 'in:pending,approved,rejected,cancelled,completed'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->bookingService->updateStatus($booking, $request->status, Auth::id(), $request->admin_notes);

            return back()->with('success', 'Status pemesanan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(Request $request, Booking $booking)
    {
        try {
            $this->bookingService->approve($booking, Auth::id(), $request->admin_notes);

            return back()->with('success', 'Pemesanan berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, Booking $booking)
    {
        $request->validate(['admin_notes' => ['required', 'string', 'max:1000']]);

        try {
            $this->bookingService->reject($booking, Auth::id(), $request->admin_notes);

            return back()->with('success', 'Pemesanan berhasil ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
