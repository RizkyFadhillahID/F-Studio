<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Equipment;
use App\Models\EquipmentLoan;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'      => User::where('is_active', true)->count(),
            'total_equipment'  => Equipment::where('is_active', true)->count(),
            'total_rooms'      => Room::where('is_active', true)->count(),
            'active_loans'     => EquipmentLoan::where('status', 'active')->count(),
            'overdue_loans'    => EquipmentLoan::where('status', 'overdue')->count(),
            'bookings_today'   => Booking::whereDate('start_datetime', today())->count(),
            'pending_loans'    => EquipmentLoan::where('status', 'pending')->count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'total_members'    => User::where('role', 'member')->where('is_active', true)->count(),
            'unpaid_bookings'  => Booking::where('payment_status', 'unpaid')->count(),
            'unpaid_loans'     => EquipmentLoan::where('payment_status', 'unpaid')->count(),
        ];

        $recentBookingPayments = Booking::with(['user', 'room'])
            ->latest()
            ->take(6)
            ->get();

        $recentLoanPayments = EquipmentLoan::with(['user'])
            ->latest()
            ->take(6)
            ->get();

        $pendingLoans    = EquipmentLoan::with(['user', 'items.equipment'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $pendingBookings = Booking::with(['user', 'room'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $overdueLoans = EquipmentLoan::with(['user', 'items.equipment'])
            ->where('status', 'overdue')
            ->latest()
            ->take(5)
            ->get();

        $recentCheckIns = \App\Models\CheckIn::with(['user', 'loan'])
            ->latest('checked_at')
            ->take(10)
            ->get();

        $revenueTrend = $this->revenueTrend();
        $bookingStatusBreakdown = Booking::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');
        $loanStatusBreakdown = EquipmentLoan::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return Inertia::render('Dashboard', compact(
            'stats',
            'pendingLoans',
            'pendingBookings',
            'overdueLoans',
            'recentCheckIns',
            'recentBookingPayments',
            'recentLoanPayments',
            'revenueTrend',
            'bookingStatusBreakdown',
            'loanStatusBreakdown'
        ));
    }

    /** Total pendapatan (booking + peminjaman lunas) per hari, 14 hari terakhir. */
    private function revenueTrend(): array
    {
        $start = today()->subDays(13);

        $bookingRevenue = Booking::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$start->copy()->startOfDay(), today()->endOfDay()])
            ->selectRaw('DATE(paid_at) as d, SUM(amount) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $loanRevenue = EquipmentLoan::where('payment_status', 'paid')
            ->whereBetween('paid_at', [$start->copy()->startOfDay(), today()->endOfDay()])
            ->selectRaw('DATE(paid_at) as d, SUM(amount) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        return collect(range(0, 13))
            ->map(function ($i) use ($start, $bookingRevenue, $loanRevenue) {
                $date = $start->copy()->addDays($i)->toDateString();

                return [
                    'date'   => $date,
                    'amount' => (float) ($bookingRevenue[$date] ?? 0) + (float) ($loanRevenue[$date] ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    /** JSON endpoint untuk admin dashboard auto-polling */
    public function stats()
    {
        return response()->json([
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'pending_loans'    => EquipmentLoan::where('status', 'pending')->count(),
            'active_loans'     => EquipmentLoan::where('status', 'active')->count(),
            'overdue_loans'    => EquipmentLoan::where('status', 'overdue')->count(),
            'bookings_today'   => Booking::whereDate('start_datetime', today())->count(),
        ]);
    }
}
