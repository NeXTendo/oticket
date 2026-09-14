<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Livewire\Checkout;
use Illuminate\Support\Facades\Route;

// ─── Public ──────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/events/{event:slug}', [HomeController::class, 'showEvent'])->name('events.show');

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Authenticated ────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/my-tickets', [HomeController::class, 'myTickets'])->name('my-tickets');

    Route::get('/checkout/{order:order_number}', Checkout::class)->name('checkout');

    Route::get('/orders/{order:order_number}/confirmation', [HomeController::class, 'orderConfirmation'])->name('orders.confirmation');
});
