<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\EquipmentLoan;
use App\Models\Room;
use App\Services\BookingService;
use App\Services\EquipmentLoanService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private EquipmentLoanService $loanService,
        private PaymentService $paymentService
    ) {}

    public function dashboard()
    {
        $myBookings = Booking::where('user_id', Auth::id())
            ->with('room')
            ->latest()
            ->take(4)
            ->get();

        $myLoans = EquipmentLoan::where('user_id', Auth::id())
            ->with('items.equipment')
            ->latest()
            ->take(4)
            ->get();

        $stats = [
            'total'    => Booking::where('user_id', Auth::id())->count(),
            'pending'  => Booking::where('user_id', Auth::id())->where('status', 'pending')->count(),
            'approved' => Booking::where('user_id', Auth::id())->where('status', 'approved')->count(),
            'loans'    => EquipmentLoan::where('user_id', Auth::id())->whereIn('status', ['approved', 'active'])->count(),
        ];

        // Etalase untuk tampilan ala e-commerce
        $featuredRooms = Room::where('is_active', true)->orderBy('capacity', 'desc')->take(4)->get();
        $featuredEquipment = Equipment::where('is_active', true)
            ->where('quantity_available', '>', 0)
            ->with('category:id,name')
            ->orderByDesc('quantity_available')
            ->take(8)
            ->get();

        return Inertia::render('Member/Dashboard', compact(
            'myBookings', 'myLoans', 'stats', 'featuredRooms', 'featuredEquipment'
        ));
    }

    public function rooms(Request $request)
    {
        $rooms = Room::where('is_active', true)
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->get();

        return Inertia::render('Member/Rooms', [
            'rooms'   => $rooms,
            'filters' => ['search' => $request->search],
        ]);
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
                ->with('success', "Pemesanan {$booking->booking_code} berhasil dibuat! Silakan lakukan pembayaran.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function bookings(Request $request)
    {
        $bookings = Booking::where('user_id', Auth::id())
            ->with('room')
            ->when($request->booking_status, fn ($q) => $q->where('status', $request->booking_status))
            ->latest()
            ->paginate(10, ['*'], 'bookings_page')
            ->withQueryString();

        $loans = EquipmentLoan::where('user_id', Auth::id())
            ->with('items.equipment')
            ->when($request->loan_status, fn ($q) => $q->where('status', $request->loan_status))
            ->latest()
            ->paginate(10, ['*'], 'loans_page')
            ->withQueryString();

        return Inertia::render('Member/Bookings', [
            'bookings'  => $bookings,
            'loans'     => $loans,
            'filters'   => ['booking_status' => $request->booking_status, 'loan_status' => $request->loan_status],
            'activeTab' => $request->tab === 'loans' ? 'loans' : 'bookings',
        ]);
    }

    // ── Peminjaman alat (self-service member) ─────────────────────────────────

    public function equipment(Request $request)
    {
        $equipment = Equipment::where('is_active', true)
            ->with('category:id,name')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->orderBy('name')
            ->get();

        return Inertia::render('Member/Equipment', [
            'equipment'  => $equipment,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'filters'    => ['search' => $request->search, 'category_id' => $request->category_id],
        ]);
    }

    public function storeLoan(Request $request)
    {
        $data = $request->validate([
            'purpose'              => ['required', 'string', 'max:500'],
            'loan_date'            => ['required', 'date', 'after_or_equal:today'],
            'due_date'             => ['required', 'date', 'after_or_equal:loan_date'],
            'notes'                => ['nullable', 'string', 'max:1000'],
            'items'                => ['required', 'array', 'min:1', 'max:10'],
            'items.*.equipment_id' => ['required', 'integer', 'exists:equipment,id'],
            'items.*.quantity'     => ['required', 'integer', 'min:1', 'max:10'],
        ], [
            'purpose.required'         => 'Keperluan peminjaman wajib diisi.',
            'loan_date.after_or_equal' => 'Tanggal pinjam tidak boleh di masa lalu.',
            'due_date.after_or_equal'  => 'Tanggal kembali harus setelah atau sama dengan tanggal pinjam.',
            'items.required'           => 'Pilih minimal satu peralatan.',
        ]);

        try {
            $loan = $this->loanService->create($data, Auth::id());

            return redirect()->route('member.bookings', ['tab' => 'loans'])
                ->with('success', "Peminjaman {$loan->loan_code} berhasil dibuat! Silakan lakukan pembayaran.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Pembayaran simulasi ───────────────────────────────────────────────────

    public function payBooking(Request $request, Booking $booking)
    {
        abort_if($booking->user_id !== Auth::id(), 403);
        $data = $request->validate(['method' => ['required', 'in:cash,transfer,ewallet,qris']]);

        try {
            $this->paymentService->payBooking($booking, $data['method']);

            return back()->with('success', "Pembayaran booking {$booking->booking_code} berhasil (simulasi).");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function payLoan(Request $request, EquipmentLoan $equipmentLoan)
    {
        abort_if($equipmentLoan->user_id !== Auth::id(), 403);
        $data = $request->validate(['method' => ['required', 'in:cash,transfer,ewallet,qris']]);

        try {
            $this->paymentService->payLoan($equipmentLoan, $data['method']);

            return back()->with('success', "Pembayaran peminjaman {$equipmentLoan->loan_code} berhasil (simulasi).");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancelBooking(Booking $booking)
    {
        abort_if($booking->user_id !== Auth::id(), 403);

        if ($booking->payment_status === 'paid') {
            return back()->with('error', 'Pemesanan yang sudah dibayar tidak dapat dibatalkan.');
        }

        try {
            $this->bookingService->cancel($booking, Auth::id());

            return back()->with('success', "Pemesanan {$booking->booking_code} berhasil dibatalkan.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
