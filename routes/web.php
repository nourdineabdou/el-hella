<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DistributorController as AdminDistributorController;
use App\Http\Controllers\Admin\GoalController as AdminGoalController;
use App\Http\Controllers\Admin\GpsAlertController as AdminGpsAlertController;
use App\Http\Controllers\Admin\MapController as AdminMapController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ShopController as AdminShopController;
use App\Http\Controllers\Admin\SoldProductController as AdminSoldProductController;
use App\Http\Controllers\Admin\VisitController as AdminVisitController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Distributor\DashboardController as DistributorDashboardController;
use App\Http\Controllers\Distributor\GoalController as DistributorGoalController;
use App\Http\Controllers\Distributor\VisitController as DistributorVisitController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->to(auth()->check() ? route('dashboard') : route('login'));
});

Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/visits', [AdminVisitController::class, 'index'])->name('visits.index');
        Route::get('/visits/export', [AdminVisitController::class, 'export'])->name('visits.export');
        Route::post('/visits/{visit}/cancel-sale', [AdminVisitController::class, 'cancelSale'])->name('visits.cancel-sale');
        Route::get('/shops', [AdminShopController::class, 'index'])->name('shops.index');
        Route::get('/map', [AdminMapController::class, 'index'])->name('map.index');
        Route::get('/gps-alerts', [AdminGpsAlertController::class, 'index'])->name('gps-alerts.index');
        Route::post('/gps-alerts/{gpsAlert}/review', [AdminGpsAlertController::class, 'review'])->name('gps-alerts.review');
        Route::get('/goals', [AdminGoalController::class, 'index'])->name('goals.index');
        Route::post('/goals', [AdminGoalController::class, 'store'])->name('goals.store');
        Route::get('/distributors', [AdminDistributorController::class, 'index'])->name('distributors.index');
        Route::post('/distributors', [AdminDistributorController::class, 'store'])->name('distributors.store');
        Route::post('/distributors/{distributor}/reset-password', [AdminDistributorController::class, 'resetPassword'])->name('distributors.reset-password');
        Route::post('/distributors/{distributor}/toggle-active', [AdminDistributorController::class, 'toggleActive'])->name('distributors.toggle-active');
        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
        Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::get('/products-sold', [AdminSoldProductController::class, 'index'])->name('products-sold.index');
        Route::get('/products-sold/export', [AdminSoldProductController::class, 'export'])->name('products-sold.export');
        Route::get('/admins', [AdminUserController::class, 'index'])->name('admins.index');
        Route::post('/admins', [AdminUserController::class, 'store'])->name('admins.store');
    });

    Route::middleware('role:distributor')->prefix('distributor')->name('distributor.')->group(function () {
        Route::get('/dashboard', [DistributorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/visits', [DistributorVisitController::class, 'index'])->name('visits.index');
        Route::get('/goals', [DistributorGoalController::class, 'index'])->name('goals.index');
        Route::get('/shops', [App\Http\Controllers\Distributor\ShopController::class, 'index'])->name('shops.index');
        Route::get('/shops/search', [App\Http\Controllers\Distributor\ShopController::class, 'search'])->name('shops.search');
        Route::get('/shops/nearby', [App\Http\Controllers\Distributor\ShopController::class, 'nearby'])->name('shops.nearby');
        Route::get('/shops/products/search', [App\Http\Controllers\Distributor\ShopController::class, 'searchProducts'])->name('shops.products.search');
        Route::post('/shops', [App\Http\Controllers\Distributor\ShopController::class, 'store'])->name('shops.store');
        Route::get('/shops/{shop}', [App\Http\Controllers\Distributor\ShopController::class, 'show'])->name('shops.show');
        Route::post('/shops/{shop}/sell', [App\Http\Controllers\Distributor\ShopController::class, 'sell'])->name('shops.sell');
        Route::post('/shops/{shop}/visit', [App\Http\Controllers\Distributor\ShopController::class, 'visit'])->name('shops.visit');
    });
});

require __DIR__.'/auth.php';
