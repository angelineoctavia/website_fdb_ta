<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerOrderController;

// 1. Akses awal langsung diarahkan ke Login
Route::get('/', function () {
    return redirect()->route('home');
});

// 2. Auth Routes (Register, Login, Logout)
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 3. Public Pages (Bisa diakses tanpa login atau umum)
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/categories', [ShopController::class, 'index'])->name('categories.index');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{id}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/shop-detail/{id}', [ShopController::class, 'show'])->name('shop.detail'); // Alias pengaman

Route::get('/about', function () {
    if (!session()->has('user_id')) {
        return redirect()->route('login')->withErrors(['error' => 'Please login first.']);
    }
    return view('customer.about');
})->name('about');


// 4. Customer Routes (Memerlukan Session Login & Role Customer 'C')[cite: 4]
Route::middleware(['web', 'role:C'])->group(function () {
    // Cart & Wishlist Routes (Customer)
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{id}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/wishlist/{id}/to-cart', [WishlistController::class, 'toCart'])->name('wishlist.toCart');
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    // View My Orders
    Route::get('/my-orders', [CustomerOrderController::class, 'index'])->name('customer.orders');
    Route::get('/my-orders/{id}', [CustomerOrderController::class, 'show'])->name('customer.orders.show');

    // Midtrans
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('/checkout/select', [CheckoutController::class, 'select'])->name('checkout.select');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');
    Route::get('/checkout/pay/{id}', [CheckoutController::class, 'resume'])->name('checkout.resume');
    Route::get('/checkout/finish/{id}', [CheckoutController::class, 'finish'])->name('checkout.finish');
});


// 5. Seller Center Routes (Memerlukan Role Seller 'S')[cite: 4]
Route::middleware(['web', 'role:S'])->prefix('seller')->group(function () {
    Route::get('/dashboard', [SellerController::class, 'dashboard'])->name('seller.dashboard');

    Route::get('/upload', [SellerController::class, 'uploadForm'])->name('seller.upload');
    Route::post('/upload', [SellerController::class, 'uploadStore'])->name('seller.upload.process');

    // Product Management (Seller)
    Route::get('/products', [SellerController::class, 'products'])->name('seller.products');
    Route::get('/products/{id}/edit', [SellerController::class, 'productEdit'])->name('seller.products.edit');
    Route::put('/products/{id}', [SellerController::class, 'productUpdate'])->name('seller.products.update');
    Route::delete('/products/{id}', [SellerController::class, 'productDestroy'])->name('seller.products.destroy');
    Route::patch('/products/{id}/toggle', [SellerController::class, 'productToggle'])->name('seller.products.toggle');


    Route::get('/orders', [SellerController::class, 'orders'])->name('seller.orders');
    Route::get('/orders/{id}', [SellerController::class, 'orderShow'])->name('seller.orders.show');   // View Details
    Route::patch('/orders/{id}', [SellerController::class, 'orderUpdateStatus'])->name('seller.orders.update');

    Route::get('/profile', [SellerController::class, 'profile'])->name('seller.profile');
    Route::post('/profile', [SellerController::class, 'profileUpdate'])->name('seller.profile.update');
});


// 6. Admin Center Routes (Memerlukan Role Admin 'A')[cite: 4]
Route::middleware(['web', 'role:A'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/report', [AdminController::class, 'report'])->name('admin.report');   // download XLSX

    Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::get('/orders/{id}', [AdminController::class, 'orderShow'])->name('admin.orders.show');

    Route::post('/categories', [AdminController::class, 'categoryStore'])->name('admin.categories.store');
    Route::patch('/categories/{id}', [AdminController::class, 'categoryUpdate'])->name('admin.categories.update');
    Route::delete('/categories/{id}', [AdminController::class, 'categoryDestroy'])->name('admin.categories.destroy');

    Route::get('/profile', [AdminController::class, 'profile'])->name('admin.profile');
});

// Midtrans
// Webhook Midtrans: publik (tanpa login), diamankan signature
Route::post('/midtrans/notification', [MidtransController::class, 'notification']);
