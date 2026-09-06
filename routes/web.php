<?php

use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\AiConsultantController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Публичная часть
|--------------------------------------------------------------------------
*/
Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalog/ajax', [CatalogController::class, 'ajax'])->name('catalog.ajax');
Route::get('/product/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/brand/{brand}', [BrandController::class, 'show'])->name('brands.show');
Route::view('/privacy', 'privacy')->name('privacy');

// ИИ-консультант: лимиты вместо ручной возни с $_SESSION['ai_log']
Route::post('/ai/chat', AiConsultantController::class)
    ->middleware(['throttle:ai-consultant'])
    ->name('ai.chat');

/*
|--------------------------------------------------------------------------
| Гости
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::post('/register/send-code', [RegisterController::class, 'sendCode'])
        ->middleware('throttle:verification-code')
        ->name('register.send-code');

    Route::post('/register/verify-code', [RegisterController::class, 'verifyCode'])
        ->middleware('throttle:10,10')
        ->name('register.verify-code');
});

/*
|--------------------------------------------------------------------------
| Авторизованные
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Корзина
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');
    Route::delete('/cart/{item}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::post('/cart/qty', [CartController::class, 'updateQty'])->name('cart.qty');

    // Оформление и оплата
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/payment/{order}/pending', [PaymentController::class, 'pending'])->name('payment.pending');
    Route::get('/payment/{order}/return', [PaymentController::class, 'return'])->name('payment.return');

    // Заказы
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Профиль
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
});

/*
|--------------------------------------------------------------------------
| Админка
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::resource('products', AdminProductController::class)->except('show');
        Route::resource('brands', AdminBrandController::class)->except('show');
    });
