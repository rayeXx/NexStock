<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\RackController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\InboundController;
use App\Http\Controllers\OutboundController;
use App\Http\Controllers\DamagedReportController;
use App\Http\Controllers\StockOpnameController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (will trigger login if unauthenticated)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // Main Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 1. Transaction Routes (accessible by all roles)
    Route::prefix('inbound')->name('inbound.')->group(function () {
        Route::get('/', [InboundController::class, 'index'])->name('index');
        Route::get('/create', [InboundController::class, 'create'])->name('create');
        Route::post('/store', [InboundController::class, 'store'])->name('store');
    });

    Route::prefix('outbound')->name('outbound.')->group(function () {
        Route::get('/', [OutboundController::class, 'index'])->name('index');
        Route::get('/create', [OutboundController::class, 'create'])->name('create');
        Route::post('/store', [OutboundController::class, 'store'])->name('store');
    });

    Route::prefix('damaged')->name('damaged.')->group(function () {
        Route::get('/', [DamagedReportController::class, 'index'])->name('index');
        Route::get('/create', [DamagedReportController::class, 'create'])->name('create');
        Route::post('/store', [DamagedReportController::class, 'store'])->name('store');
    });

    Route::prefix('opname')->name('opname.')->group(function () {
        Route::get('/', [StockOpnameController::class, 'index'])->name('index');
        Route::get('/create', [StockOpnameController::class, 'create'])->name('create');
        Route::post('/store', [StockOpnameController::class, 'store'])->name('store');
        Route::get('/{id}', [StockOpnameController::class, 'show'])->name('show');
    });

    // 2. Master Data & Procurement (Admin & Owner only)
    Route::middleware(['role:admin_gudang,owner'])->group(function () {
        Route::resource('product', ProductController::class)->except(['show']);
        Route::resource('supplier', SupplierController::class)->except(['show']);
        Route::resource('rack', RackController::class)->except(['show']);

        // Procurement PO (View)
        Route::prefix('po')->name('po.')->group(function () {
            Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
            Route::get('/{id}', [PurchaseOrderController::class, 'show'])->name('show');
        });
    });

    // 3. User Management & PO Creation (Admin only)
    Route::middleware(['role:admin_gudang'])->group(function () {
        Route::resource('user', UserController::class)->except(['show']);

        // PO Management
        Route::prefix('po')->name('po.')->group(function () {
            Route::get('/draft/create', [PurchaseOrderController::class, 'create'])->name('create'); // changed url to /draft/create to avoid {id} conflict
            Route::post('/store', [PurchaseOrderController::class, 'store'])->name('store');
            Route::post('/{id}/submit', [PurchaseOrderController::class, 'submit'])->name('submit');
            Route::delete('/{id}', [PurchaseOrderController::class, 'destroy'])->name('destroy');
        });
    });

    // 4. Approvals (Owner only)
    Route::middleware(['role:owner'])->group(function () {
        Route::post('/po/{id}/approve', [PurchaseOrderController::class, 'approve'])->name('po.approve');
        Route::post('/po/{id}/reject', [PurchaseOrderController::class, 'reject'])->name('po.reject');

        Route::post('/damaged/{id}/approve', [DamagedReportController::class, 'approve'])->name('damaged.approve');
        Route::post('/damaged/{id}/reject', [DamagedReportController::class, 'reject'])->name('damaged.reject');
    });
});

require __DIR__.'/auth.php';
