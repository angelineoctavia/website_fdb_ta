<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SellerController;

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

// 3. Halaman Customer (Memerlukan Session Login & Folder customer/)
Route::middleware(['web'])->group(function () {

    // Home Page
    Route::get('/home', function () {
        if (!session()->has('user_id')) {
            return redirect()->route('login')->withErrors(['error' => 'Silakan login terlebih dahulu.']);
        }
        return view('customer.home');
    })->name('home');

    // Categories & Shop Katalog
    Route::get('/categories', [ShopController::class, 'index'])->name('categories.index');
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');

    // Detail Produk Berdasarkan ID (Menggunakan shop_detail)
    Route::get('/shop/{id}', [ShopController::class, 'show'])->name('shop.detail');

    // About Us Page
    Route::get('/about', function () {
        if (!session()->has('user_id')) {
            return redirect()->route('login')->withErrors(['error' => 'Silakan login terlebih dahulu.']);
        }
        return view('customer.about');
    })->name('about');
});

// 4. Seller Center Routes (Dashboard, Upload, Orders, Profile)
Route::middleware(['web'])->prefix('seller')->group(function () {
    Route::get('/dashboard', [SellerController::class, 'dashboard'])->name('seller.dashboard');

    Route::get('/upload', [SellerController::class, 'uploadForm'])->name('seller.upload');
    Route::post('/upload', [SellerController::class, 'uploadStore'])->name('seller.upload.process');

    Route::get('/orders', [SellerController::class, 'orders'])->name('seller.orders');
    Route::get('/orders/{id}', [SellerController::class, 'orderShow'])->name('seller.orders.show');
    Route::patch('/orders/{id}', [SellerController::class, 'orderUpdateStatus'])->name('seller.orders.update');

    Route::get('/profile', [SellerController::class, 'profile'])->name('seller.profile');
    Route::post('/profile', [SellerController::class, 'profileUpdate'])->name('seller.profile.update');
});