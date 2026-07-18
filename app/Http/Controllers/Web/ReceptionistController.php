<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\EquipmentLoan;
use App\Models\Room;
use App\Services\BookingService;
use App\Services\EquipmentLoanService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReceptionistController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private EquipmentLoanService $loanService,
        private PaymentService $paymentService
    ) {}

    public function dashboard()
    {
        $today = now()->toDateString();

        $todayBookings = Booking::with('room')
            ->whereDate('start_datetime', $today)
            ->orderBy('start_datetime')
            ->get();

        $stats = [
            'today'        => $todayBookings->count(),
            'pending'      => Booking::where('status', 'pending')->count(),
            'approved'     => Booking::where('status', 'approved')->whereDate('start_datetime', $today)->count(),
            'total_month'  => Booking::whereMonth('created_at', now()->month)->count(),
            'loan_pending' => EquipmentLoan::where('status', 'pending')->count(),
        ];

        $recentBookings = Booking::with('room')
            ->where('user_id', Auth::id())
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Receptionist/Dashboard', compact('stats', 'todayBookings', 'recentBookings'));
    }

    public function rooms(Request $request)
    {
        $rooms = Room::where('is_active', true)
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->get();

        return Inertia::render('Receptionist/Rooms', [
            'rooms'     => $rooms,
            'equipment' => Equipment::where('is_active', true)->orderBy('name')->get(['id', 'name', 'quantity_available', 'price_per_day']),
            'filters'   => ['search' => $request->search],
        ]);
    }

    public function storeBooking(Request $request)
    {
        $request->merge([
            'equipment_items' => collect($request->input('equipment_items', []))
                ->filter(fn ($item) => ! empty($item['equipment_id']))
                ->values()
                ->all() ?: null,
        ]);

        $data = $request->validate([
            'room_id'                         => ['required', 'integer', 'exists:rooms,id'],
            'customer_name'                   => ['required', 'string', 'max:200'],
            'customer_phone'                  => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'title'                           => ['required', 'string', 'max:200'],
            'start_datetime'                  => ['required', 'date', 'after:now'],
            'end_datetime'                    => ['required', 'date', 'after:start_datetime'],
            'notes'                           => ['nullable', 'string', 'max:1000'],
            'equipment_items'                 => ['nullable', 'array', 'max:10'],
            'equipment_items.*.equipment_id'  => ['required_with:equipment_items', 'integer', 'exists:equipment,id'],
            'equipment_items.*.quantity'      => ['required_with:equipment_items', 'integer', 'min:1', 'max:20'],
        ], [
            'customer_name.required' => 'Nama pelanggan wajib diisi.',
            'customer_phone.regex'   => 'Format nomor telepon tidak valid.',
            'start_datetime.after'   => 'Waktu mulai harus di masa mendatang.',
            'end_datetime.after'     => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        try {
            [$booking, $hasLoan] = DB::transaction(function () use ($data) {
                $booking = $this->bookingService->create($data, Auth::id());

                $hasLoan = false;
                if (! empty($data['equipment_items'])) {
                    $this->loanService->create([
                        'booking_id'     => $booking->id,
                        'customer_name'  => $data['customer_name'],
                        'customer_phone' => $data['customer_phone'] ?? null,
                        'purpose'        => "Peralatan untuk booking {$booking->booking_code}: {$data['title']}",
                        'loan_date'      => \Carbon\Carbon::parse($data['start_datetime'])->toDateString(),
                        'due_date'       => \Carbon\Carbon::parse($data['end_datetime'])->toDateString(),
                        'notes'          => "Diajukan bersamaan dengan booking ruangan {$booking->room->name}.",
                        'items'          => $data['equipment_items'],
                    ], Auth::id());
                    $hasLoan = true;
                }

                return [$booking, $hasLoan];
            });

            $msg = "Booking {$booking->booking_code} atas nama {$data['customer_name']} berhasil dibuat!";
            if ($hasLoan) {
                $msg .= ' Peminjaman peralatan juga diajukan.';
            }

            return redirect()->route('receptionist.bookings')->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function bookings(Request $request)
    {
        $bookings = Booking::with('room')
            ->where('user_id', Auth::id())
            ->when($request->booking_status, fn ($q) => $q->where('status', $request->booking_status))
            ->latest()
            ->paginate(12, ['*'], 'bookings_page')
            ->withQueryString();

        $loans = EquipmentLoan::with(['items.equipment'])
            ->where('user_id', Auth::id())
            ->when($request->loan_status, fn ($q) => $q->where('status', $request->loan_status))
            ->latest()
            ->paginate(12, ['*'], 'loans_page')
            ->withQueryString();

        return Inertia::render('Receptionist/Bookings', [
            'bookings'  => $bookings,
            'loans'     => $loans,
            'filters'   => ['booking_status' => $request->booking_status, 'loan_status' => $request->loan_status],
            'activeTab' => $request->tab === 'loans' ? 'loans' : 'bookings',
        ]);
    }

    public function schedule()
    {
        $today = now()->toDateString();
        $rooms = Room::where('is_active', true)->orderBy('name')->get();
        $bookings = Booking::with('room')
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('start_datetime', $today)
            ->orderBy('start_datetime')
            ->get();

        return Inertia::render('Receptionist/Schedule', compact('rooms', 'bookings', 'today'));
    }

    public function loans()
    {
        return Inertia::render('Receptionist/Loans', [
            'equipment' => Equipment::where('is_active', true)->orderBy('name')->get(['id', 'name', 'quantity_available', 'price_per_day']),
        ]);
    }

    public function storeLoan(Request $request)
    {
        $data = $request->validate([
            'customer_name'        => ['required', 'string', 'max:200'],
            'customer_phone'       => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'purpose'              => ['required', 'string', 'max:500'],
            'loan_date'            => ['required', 'date', 'after_or_equal:today'],
            'due_date'             => ['required', 'date', 'after_or_equal:loan_date'],
            'notes'                => ['nullable', 'string', 'max:1000'],
            'items'                => ['required', 'array', 'min:1', 'max:10'],
            'items.*.equipment_id' => ['required', 'integer', 'exists:equipment,id'],
            'items.*.quantity'     => ['required', 'integer', 'min:1', 'max:20'],
        ], [
            'customer_name.required'   => 'Nama pelanggan wajib diisi.',
            'purpose.required'         => 'Keperluan peminjaman wajib diisi.',
            'loan_date.after_or_equal' => 'Tanggal pinjam tidak boleh di masa lalu.',
            'due_date.after_or_equal'  => 'Tanggal kembali harus setelah atau sama dengan tanggal pinjam.',
            'items.required'           => 'Pilih minimal satu peralatan.',
            'items.min'                => 'Pilih minimal satu peralatan.',
        ]);

        try {
            $loan = $this->loanService->create($data, Auth::id());

            return redirect()->route('receptionist.bookings', ['tab' => 'loans'])
                ->with('success', "Peminjaman {$loan->loan_code} atas nama {$data['customer_name']} berhasil dibuat! Silakan lakukan pembayaran.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Pembayaran simulasi (atas nama pelanggan walk-in) ─────────────────────

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
