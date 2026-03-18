<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\EventController;
use App\Http\Middleware\EnsureBookingOwner;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes ────────────────────────────────────────────────────────
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login',    [AuthController::class, 'login'])->name('login');
});
Route::prefix('events')->name('events.')->group(function () {
    Route::get('/',        [EventController::class, 'index'])->name('index');
    Route::get('/{event}', [EventController::class, 'show'])->name('show');
});

//Authenticated routes ──────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me',      [AuthController::class, 'me'])->name('me');
    });

    // Events Routes
    Route::prefix('events')->name('events.')->group(function () {
        Route::post('/',          [EventController::class, 'store'])->name('store');
        Route::put('/{event}',    [EventController::class, 'update'])->name('update');
        Route::patch('/{event}',  [EventController::class, 'update'])->name('update.patch');
        Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy');
    });

    // Bookings Routes
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/',  [BookingController::class, 'index'])->name('index');
        Route::post('/', [BookingController::class, 'store'])->name('store');

        // Booking Ownership
        Route::middleware(EnsureBookingOwner::class)->group(function () {
            Route::get('/{booking}',          [BookingController::class, 'show'])->name('show');
            Route::patch('/{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel');
        });
    });
});
