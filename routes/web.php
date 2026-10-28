<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShopController;

// 1. Akses awal langsung diarahkan ke Register
Route::get('/', function () {
    return redirect()->route('register');
});

// 2. Auth Routes (Register, Login, Logout)
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 3. Halaman Customer (Memerlukan Session Login)
Route::middleware(['web'])->group(function () {

    // Home Page
    Route::get('/home', function () {
        if (!session()->has('user_id')) {
            return redirect()->route('login')->withErrors(['error' => 'Silakan login terlebih dahulu.']);
        }
        return view('home');
    })->name('home');

    // Categories & Shop Katalog (Mengarahkan ke ShopController)
    Route::get('/categories', [ShopController::class, 'index'])->name('categories.index');
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');

    // Detail Produk Berdasarkan ID
    Route::get('/shop/{id}', [ShopController::class, 'show'])->name('shop.detail');

    // About Us Page
    Route::get('/about', function () {
        if (!session()->has('user_id')) {
            return redirect()->route('login')->withErrors(['error' => 'Silakan login terlebih dahulu.']);
        }
        return view('about');
    })->name('about');
});