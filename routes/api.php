<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\EquipmentLoanController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

// ─── Public Routes ────────────────────────────────────────────────────────────
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:60,1');
});

// ─── Authenticated Routes ──────────────────────────────────────────────────────
Route::prefix('v1')->middleware(['auth:sanctum', 'account.active'])->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Categories
    Route::get('/categories',       [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::middleware('role:admin')->group(function () {
        Route::post('/categories',              [CategoryController::class, 'store']);
        Route::put('/categories/{category}',    [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    });

    // Equipment
    Route::get('/equipment',           [EquipmentController::class, 'index']);
    Route::get('/equipment/{equipment}', [EquipmentController::class, 'show']);
    Route::middleware('role:admin')->group(function () {
        Route::post('/equipment',              [EquipmentController::class, 'store']);
        Route::put('/equipment/{equipment}',   [EquipmentController::class, 'update']);
        Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy']);
    });

    // Rooms
    Route::get('/rooms',          [RoomController::class, 'index']);
    Route::get('/rooms/{room}',   [RoomController::class, 'show']);
    Route::middleware('role:admin')->group(function () {
        Route::post('/rooms',           [RoomController::class, 'store']);
        Route::put('/rooms/{room}',     [RoomController::class, 'update']);
        Route::delete('/rooms/{room}',  [RoomController::class, 'destroy']);
    });

    // Bookings
    Route::get('/bookings',          [BookingController::class, 'index']);
    Route::post('/bookings',         [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::put('/bookings/{booking}', [BookingController::class, 'update']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);
    Route::middleware('role:admin')->group(function () {
        Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve']);
        Route::post('/bookings/{booking}/reject',  [BookingController::class, 'reject']);
    });

    // Equipment Loans
    Route::get('/equipment-loans',               [EquipmentLoanController::class, 'index']);
    Route::post('/equipment-loans',              [EquipmentLoanController::class, 'store']);
    Route::get('/equipment-loans/{equipmentLoan}', [EquipmentLoanController::class, 'show']);
    Route::put('/equipment-loans/{equipmentLoan}', [EquipmentLoanController::class, 'update']);
    Route::middleware('role:admin')->group(function () {
        Route::post('/equipment-loans/{equipmentLoan}/approve', [EquipmentLoanController::class, 'approve']);
        Route::post('/equipment-loans/{equipmentLoan}/reject',  [EquipmentLoanController::class, 'reject']);
    });

    // Check-ins (POST available for members, GET only for admin)
    Route::post('/check-ins', [CheckInController::class, 'store']);
    Route::middleware('role:admin')->group(function () {
        Route::get('/check-ins',            [CheckInController::class, 'index']);
        Route::get('/check-ins/{checkIn}',  [CheckInController::class, 'show']);
    });

    // Notifications
    Route::get('/notifications',                          [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read',     [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all',                [NotificationController::class, 'markAllAsRead']);
});
