<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\EquipmentLoan;
use App\Models\Room;
use App\Services\BookingService;
use App\Services\EquipmentLoanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceptionistController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private EquipmentLoanService $loanService
    ) {}

    // ═══════════════════════════════════════
    //  DASHBOARD & STATS
    // ═══════════════════════════════════════

    public function dashboard()
    {
        $today = now()->toDateString();

        $todayBookings = Booking::with('room')
            ->whereDate('start_datetime', $today)
            ->orderBy('start_datetime')
            ->get();

        $stats = [
            'today'       => $todayBookings->count(),
            'pending'     => Booking::where('status', 'pending')->count(),
            'approved'    => Booking::where('status', 'approved')->whereDate('start_datetime', $today)->count(),
            'total_month' => Booking::whereMonth('created_at', now()->month)->count(),
            'loan_pending' => EquipmentLoan::where('status', 'pending')->count(),
        ];

        $recentBookings = Booking::with('room')
            ->where('user_id', Auth::id())
            ->latest()
            ->take(5)
            ->get();

        return view('receptionist.dashboard', compact('stats', 'todayBookings', 'recentBookings'));
    }

    /** JSON endpoint untuk auto-polling */
    public function stats()
    {
        $today = now()->toDateString();
        return response()->json([
            'pending'      => Booking::where('status', 'pending')->count(),
            'approved'     => Booking::where('status', 'approved')->whereDate('start_datetime', $today)->count(),
            'today'        => Booking::whereDate('start_datetime', $today)->count(),
            'total_month'  => Booking::whereMonth('created_at', now()->month)->count(),
            'loan_pending' => EquipmentLoan::where('status', 'pending')->count(),
            'my_bookings_updated' => Booking::where('user_id', Auth::id())
                ->where('updated_at', '>=', now()->subMinutes(2))
                ->count(),
        ]);
    }

    // ═══════════════════════════════════════
    //  RUANGAN & BOOKING
    // ═══════════════════════════════════════

    public function rooms(Request $request)
    {
        $rooms = Room::where('is_active', true)
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->get();

        return view('receptionist.rooms', compact('rooms'));
    }

    public function availability(Room $room)
    {
        $bookings = Booking::where('room_id', $room->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('end_datetime', '>=', now())
            ->where('start_datetime', '<=', now()->addDays(14))
            ->orderBy('start_datetime')
            ->get(['customer_name', 'title', 'start_datetime', 'end_datetime', 'status']);

        return view('receptionist.availability', compact('room', 'bookings'));
    }

    public function createBooking(Room $room)
    {
        $equipment = Equipment::where('is_active', true)
            ->where('condition', '!=', 'maintenance')
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('receptionist.booking-form', compact('room', 'equipment'));
    }

    public function storeBooking(Request $request)
    {
        // Buang baris equipment yang tidak dipilih (equipment_id kosong)
        $request->merge([
            'equipment_items' => collect($request->input('equipment_items', []))
                ->filter(fn($item) => !empty($item['equipment_id']))
                ->values()
                ->all() ?: null,
        ]);

        $data = $request->validate([
            'room_id'                          => ['required', 'integer', 'exists:rooms,id'],
            'customer_name'                    => ['required', 'string', 'max:200'],
            'customer_phone'                   => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'title'                            => ['required', 'string', 'max:200'],
            'start_datetime'                   => ['required', 'date', 'after:now'],
            'end_datetime'                     => ['required', 'date', 'after:start_datetime'],
            'notes'                            => ['nullable', 'string', 'max:1000'],
            // Peralatan opsional (include dengan booking)
            'equipment_items'                  => ['nullable', 'array', 'max:10'],
            'equipment_items.*.equipment_id'   => ['required_with:equipment_items', 'integer', 'exists:equipment,id'],
            'equipment_items.*.quantity'       => ['required_with:equipment_items', 'integer', 'min:1', 'max:20'],
        ], [
            'customer_name.required'  => 'Nama pelanggan wajib diisi.',
            'customer_phone.regex'    => 'Format nomor telepon tidak valid.',
            'start_datetime.after'    => 'Waktu mulai harus di masa mendatang.',
            'end_datetime.after'      => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        try {
            [$booking, $hasLoan] = DB::transaction(function () use ($data) {
                $booking = $this->bookingService->create($data, Auth::id());

                $hasLoan = false;
                if (!empty($data['equipment_items'])) {
                    $this->loanService->create([
                        'booking_id'     => $booking->id,
                        'customer_name'  => $data['customer_name'],
                        'customer_phone' => $data['customer_phone'] ?? null,
                        'purpose'        => "Peralatan untuk booking {$booking->booking_code}: {$data['title']}",
                        'loan_date'      => \Carbon\Carbon::parse($data['start_datetime'])->toDateString(),
                        'due_date'       => \Carbon\Carbon::parse($data['end_datetime'])->toDateString(),
                        'notes'          => "Diajukan bersamaan dengan booking ruangan {$booking->room->name}.",
                        'items'          => array_filter($data['equipment_items'], fn($i) => !empty($i['equipment_id'])),
                    ], Auth::id());
                    $hasLoan = true;
                }

                return [$booking, $hasLoan];
            });

            $msg = "Booking {$booking->booking_code} atas nama {$data['customer_name']} berhasil dibuat!";
            if ($hasLoan) {
                $msg .= " Peminjaman peralatan juga diajukan.";
            }

            return redirect()->route('receptionist.bookings')->with('success', $msg);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function bookings(Request $request)
    {
        $bookings = Booking::with('room')
            ->where('user_id', Auth::id())
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date, fn($q) => $q->whereDate('start_datetime', $request->date))
            ->latest()
            ->paginate(12);

        return view('receptionist.bookings', compact('bookings'));
    }

    public function showBooking(Booking $booking)
    {
        abort_if($booking->user_id !== Auth::id(), 403);
        $booking->load(['room', 'approvedBy', 'equipmentLoans.items.equipment']);
        return view('receptionist.booking-detail', compact('booking'));
    }

    // ═══════════════════════════════════════
    //  JADWAL
    // ═══════════════════════════════════════

    public function schedule()
    {
        $today   = now()->toDateString();
        $rooms   = Room::where('is_active', true)->get();
        $bookings = Booking::with('room')
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('start_datetime', $today)
            ->orderBy('start_datetime')
            ->get();

        return view('receptionist.schedule', compact('rooms', 'bookings', 'today'));
    }

    // ═══════════════════════════════════════
    //  PEMINJAMAN PERALATAN (STANDALONE)
    // ═══════════════════════════════════════

    public function loans(Request $request)
    {
        $loans = EquipmentLoan::with(['items.equipment'])
            ->where('user_id', Auth::id())
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(12);

        return view('receptionist.loans', compact('loans'));
    }

    public function createLoan()
    {
        $equipment = Equipment::where('is_active', true)
            ->where('condition', '!=', 'maintenance')
            ->with('category')
            ->orderBy('name')
            ->get();

        $equipmentList = $equipment->map(fn($e) => [
            'id'            => $e->id,
            'name'          => $e->name,
            'category'      => optional($e->category)->name,
            'qty_available' => $e->quantity_available,
        ])->values();

        return view('receptionist.loan-form', compact('equipment', 'equipmentList'));
    }

    public function storeLoan(Request $request)
    {
        $data = $request->validate([
            'customer_name'                 => ['required', 'string', 'max:200'],
            'customer_phone'                => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'purpose'                       => ['required', 'string', 'max:500'],
            'loan_date'                     => ['required', 'date', 'after_or_equal:today'],
            'due_date'                      => ['required', 'date', 'after_or_equal:loan_date'],
            'notes'                         => ['nullable', 'string', 'max:1000'],
            'items'                         => ['required', 'array', 'min:1', 'max:10'],
            'items.*.equipment_id'          => ['required', 'integer', 'exists:equipment,id'],
            'items.*.quantity'              => ['required', 'integer', 'min:1', 'max:20'],
        ], [
            'customer_name.required' => 'Nama pelanggan wajib diisi.',
            'purpose.required'       => 'Keperluan peminjaman wajib diisi.',
            'loan_date.after_or_equal' => 'Tanggal pinjam tidak boleh di masa lalu.',
            'due_date.after_or_equal'  => 'Tanggal kembali harus setelah atau sama dengan tanggal pinjam.',
            'items.required'         => 'Pilih minimal satu peralatan.',
            'items.min'              => 'Pilih minimal satu peralatan.',
        ]);

        try {
            $loan = $this->loanService->create(array_merge($data, [
                'customer_name'  => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
            ]), Auth::id());

            return redirect()->route('receptionist.loans')
                ->with('success', "Peminjaman {$loan->loan_code} atas nama {$data['customer_name']} berhasil diajukan!");
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function showLoan(EquipmentLoan $equipmentLoan)
    {
        abort_if($equipmentLoan->user_id !== Auth::id(), 403);
        $equipmentLoan->load(['items.equipment', 'approvedBy', 'booking']);
        return view('receptionist.loan-detail', compact('equipmentLoan'));
    }
}
