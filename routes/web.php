<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BookingController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\EquipmentController;
use App\Http\Controllers\Web\EquipmentLoanController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\RoomController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ─── Root ──────────────────────────────────────────────────────────────────────
Route::get('/', function () {
    if (! auth()->check()) {
        return Inertia::render('Welcome', [
            'rooms' => \App\Models\Room::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'description', 'capacity', 'facilities', 'image', 'images']),
            'equipment' => \App\Models\Equipment::where('is_active', true)
                ->with('category:id,name')
                ->orderBy('name')
                ->take(12)
                ->get(['id', 'name', 'code', 'image', 'category_id', 'quantity_available']),
        ]);
    }

    return match (auth()->user()->role) {
        'receptionist' => redirect()->route('receptionist.dashboard'),
        'member'       => redirect()->route('member.dashboard'),
        default        => redirect()->route('dashboard'),
    };
})->name('welcome');

// ─── Guest Routes ─────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post')
        ->middleware('throttle:5,1');
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post')
        ->middleware('throttle:5,1');
});

// ─── Authenticated Routes ──────────────────────────────────────────────────────
Route::middleware(['auth', 'account.active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profil (semua role)
    Route::get('/profile', [\App\Http\Controllers\Web\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Web\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\Web\ProfileController::class, 'updatePassword'])->name('profile.password');

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

        // ── Data Master (list / create / update / delete) ──
        // Users
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('users.toggle-active');

        // Categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Equipment
        Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment.index');
        Route::post('/equipment', [EquipmentController::class, 'store'])->name('equipment.store');
        Route::put('/equipment/{equipment}', [EquipmentController::class, 'update'])->name('equipment.update');
        Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy'])->name('equipment.destroy');

        // Rooms
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

        // ── Transaksi ──
        // Bookings (list / create / update status)
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
        Route::post('/bookings/{booking}/reject',  [BookingController::class, 'reject'])->name('bookings.reject');
        Route::post('/bookings/{booking}/status',  [BookingController::class, 'updateStatus'])->name('bookings.updateStatus');

        // Equipment Loans (list / create / update status / return)
        Route::get('/loans', [EquipmentLoanController::class, 'index'])->name('loans.index');
        Route::post('/loans', [EquipmentLoanController::class, 'store'])->name('loans.store');
        Route::post('/loans/{equipmentLoan}/status',  [EquipmentLoanController::class, 'updateStatus'])->name('loans.updateStatus');
        Route::post('/loans/{equipmentLoan}/return',  [EquipmentLoanController::class, 'processReturn'])->name('loans.return');

        // Laporan Transaksi
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    // Portal Member (semua anggota terautentikasi)
    Route::prefix('member')->name('member.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Web\MemberController::class, 'dashboard'])->name('dashboard');
        Route::get('/rooms', [\App\Http\Controllers\Web\MemberController::class, 'rooms'])->name('rooms');
        Route::post('/bookings', [\App\Http\Controllers\Web\MemberController::class, 'storeBooking'])->name('bookings.store');
        Route::get('/bookings', [\App\Http\Controllers\Web\MemberController::class, 'bookings'])->name('bookings');
        // Peminjaman alat self-service
        Route::get('/equipment', [\App\Http\Controllers\Web\MemberController::class, 'equipment'])->name('equipment');
        Route::post('/loans', [\App\Http\Controllers\Web\MemberController::class, 'storeLoan'])->name('loans.store');
        Route::get('/loans', [\App\Http\Controllers\Web\MemberController::class, 'loans'])->name('loans');
        // Pembayaran simulasi
        Route::post('/bookings/{booking}/pay', [\App\Http\Controllers\Web\MemberController::class, 'payBooking'])->name('bookings.pay');
        Route::post('/bookings/{booking}/cancel', [\App\Http\Controllers\Web\MemberController::class, 'cancelBooking'])->name('bookings.cancel');
        Route::post('/loans/{equipmentLoan}/pay', [\App\Http\Controllers\Web\MemberController::class, 'payLoan'])->name('loans.pay');
    });

    // Portal Receptionist (tablet front desk — booking atas nama pelanggan walk-in)
    Route::middleware('role:receptionist')->prefix('receptionist')->name('receptionist.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Web\ReceptionistController::class, 'dashboard'])->name('dashboard');
        Route::get('/schedule', [\App\Http\Controllers\Web\ReceptionistController::class, 'schedule'])->name('schedule');
        Route::get('/rooms', [\App\Http\Controllers\Web\ReceptionistController::class, 'rooms'])->name('rooms');
        Route::post('/bookings', [\App\Http\Controllers\Web\ReceptionistController::class, 'storeBooking'])->name('bookings.store');
        Route::get('/bookings', [\App\Http\Controllers\Web\ReceptionistController::class, 'bookings'])->name('bookings');
        Route::get('/loans', [\App\Http\Controllers\Web\ReceptionistController::class, 'loans'])->name('loans');
        Route::post('/loans', [\App\Http\Controllers\Web\ReceptionistController::class, 'storeLoan'])->name('loans.store');
        // Pembayaran simulasi (atas nama pelanggan walk-in)
        Route::post('/bookings/{booking}/pay', [\App\Http\Controllers\Web\ReceptionistController::class, 'payBooking'])->name('bookings.pay');
        Route::post('/bookings/{booking}/cancel', [\App\Http\Controllers\Web\ReceptionistController::class, 'cancelBooking'])->name('bookings.cancel');
        Route::post('/loans/{equipmentLoan}/pay', [\App\Http\Controllers\Web\ReceptionistController::class, 'payLoan'])->name('loans.pay');

        // Laporan Transaksi (transaksi yang dibuat resepsionis ini saja)
        Route::get('/reports', [ReportController::class, 'receptionist'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'receptionistExport'])->name('reports.export');
    });
});
