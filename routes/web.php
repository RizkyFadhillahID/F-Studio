<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BookingController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\EquipmentController;
use App\Http\Controllers\Web\EquipmentLoanController;
use App\Http\Controllers\Web\ReceptionistController;
use App\Http\Controllers\Web\RoomController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

// ─── Public Landing Page ──────────────────────────────────────────────────────
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
});

// ─── Guest Routes ─────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post')
        ->middleware('throttle:5,1');
});

// ─── Authenticated Routes ──────────────────────────────────────────────────────
Route::middleware(['auth', 'account.active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::redirect('/', '/dashboard');

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        // Users
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('users.toggle-active');

        // Categories
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Equipment
        Route::resource('equipment', EquipmentController::class);

        // Rooms
        Route::resource('rooms', RoomController::class);

        // Bookings
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
        Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
        Route::post('/bookings/{booking}/reject',  [BookingController::class, 'reject'])->name('bookings.reject');
        Route::post('/bookings/{booking}/status',  [BookingController::class, 'updateStatus'])->name('bookings.updateStatus');

        // Equipment Loans
        Route::get('/loans', [EquipmentLoanController::class, 'index'])->name('loans.index');
        Route::get('/loans/{equipmentLoan}', [EquipmentLoanController::class, 'show'])->name('loans.show');
        Route::post('/loans/{equipmentLoan}/approve', [EquipmentLoanController::class, 'approve'])->name('loans.approve');
        Route::post('/loans/{equipmentLoan}/reject',  [EquipmentLoanController::class, 'reject'])->name('loans.reject');
        Route::post('/loans/{equipmentLoan}/status',  [EquipmentLoanController::class, 'updateStatus'])->name('loans.updateStatus');
        Route::post('/loans/{equipmentLoan}/return',  [EquipmentLoanController::class, 'processReturn'])->name('loans.return');
    });

    // Member portal routes (all authenticated members)
    Route::prefix('member')->name('member.')->group(function () {
        Route::get('/',                            [\App\Http\Controllers\Web\MemberController::class, 'dashboard'])->name('dashboard');
        Route::get('/rooms',                       [\App\Http\Controllers\Web\MemberController::class, 'rooms'])->name('rooms');
        Route::get('/rooms/{room}/book',           [\App\Http\Controllers\Web\MemberController::class, 'createBooking'])->name('book');
        Route::post('/bookings',                   [\App\Http\Controllers\Web\MemberController::class, 'storeBooking'])->name('bookings.store');
        Route::get('/bookings',                    [\App\Http\Controllers\Web\MemberController::class, 'bookings'])->name('bookings');
        Route::get('/bookings/{booking}',          [\App\Http\Controllers\Web\MemberController::class, 'showBooking'])->name('bookings.show');
    });

    // Receptionist portal (tablet front desk — books on behalf of customers)
    Route::middleware('role:receptionist')->prefix('receptionist')->name('receptionist.')->group(function () {
        Route::get('/',                              [ReceptionistController::class, 'dashboard'])->name('dashboard');
        Route::get('/stats',                         [ReceptionistController::class, 'stats'])->name('stats');
        Route::get('/schedule',                      [ReceptionistController::class, 'schedule'])->name('schedule');
        Route::get('/rooms',                         [ReceptionistController::class, 'rooms'])->name('rooms');
        Route::get('/rooms/{room}/availability',     [ReceptionistController::class, 'availability'])->name('availability');
        Route::get('/rooms/{room}/book',             [ReceptionistController::class, 'createBooking'])->name('book');
        Route::post('/bookings',                     [ReceptionistController::class, 'storeBooking'])->name('bookings.store');
        Route::get('/bookings',                      [ReceptionistController::class, 'bookings'])->name('bookings');
        Route::get('/bookings/{booking}',            [ReceptionistController::class, 'showBooking'])->name('bookings.show');
        // Equipment Loans (standalone)
        Route::get('/loans',                         [ReceptionistController::class, 'loans'])->name('loans');
        Route::get('/loans/create',                  [ReceptionistController::class, 'createLoan'])->name('loans.create');
        Route::post('/loans',                        [ReceptionistController::class, 'storeLoan'])->name('loans.store');
        Route::get('/loans/{equipmentLoan}',         [ReceptionistController::class, 'showLoan'])->name('loans.show');
    });
});
