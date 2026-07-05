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
use App\Http\Controllers\RestockRequestController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (will trigger login if unauthenticated)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // Main Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/restock-filter', [DashboardController::class, 'restockFilter'])->name('dashboard.restock-filter');

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
        // Step 1: FEFO in-memory preview → generates picking slip in session
        Route::post('/preview', [OutboundController::class, 'preview'])->name('preview');
        // Step 2a: Show picking slip + batch scan inputs
        Route::get('/confirm', [OutboundController::class, 'showConfirm'])->name('confirm.show');
        // Step 2b: Validate batch scans then commit to DB
        Route::post('/confirm', [OutboundController::class, 'confirm'])->name('confirm');
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
        Route::post('/{id}/approve', [StockOpnameController::class, 'approve'])->name('approve');
        Route::get('/{id}', [StockOpnameController::class, 'show'])->name('show');
    });

    // Restock Request (Staff: create + history)
    Route::prefix('restock-request')->name('restock-request.')->group(function () {
        Route::get('/create', [RestockRequestController::class, 'create'])->name('create');
        Route::post('/store', [RestockRequestController::class, 'store'])->name('store');
        Route::get('/history', [RestockRequestController::class, 'history'])->name('history');
    });

    // 2. Master Data & Procurement (Admin & Owner only)
    Route::middleware(['role:admin_gudang,owner'])->group(function () {
        Route::resource('product', ProductController::class)->except(['show']);
        Route::resource('supplier', SupplierController::class)->except(['show']);
        // Supplier KPI Detail Page
        Route::get('/supplier/{id}', [SupplierController::class, 'show'])->name('supplier.show');
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
            Route::post('/{id}/order', [PurchaseOrderController::class, 'order'])->name('order');
            Route::post('/{id}/history/{historyId}/retur', [InboundController::class, 'updateRetur'])->name('update-retur');
            Route::post('/{id}/history/{historyId}/mark-as-returned', [PurchaseOrderController::class, 'markAsReturned'])->name('mark-as-returned');
            Route::delete('/{id}', [PurchaseOrderController::class, 'destroy'])->name('destroy');
        });

        // Restock Request Review (Admin only)
        Route::get('/restock-request/review', [RestockRequestController::class, 'reviewIndex'])->name('restock-request.review');
        Route::post('/restock-request/{id}/approve', [RestockRequestController::class, 'approve'])->name('restock-request.approve');
        Route::post('/restock-request/{id}/reject', [RestockRequestController::class, 'reject'])->name('restock-request.reject');

        // Damaged Report Approvals (Admin Gudang)
        Route::post('/damaged/{id}/approve', [DamagedReportController::class, 'approve'])->name('damaged.approve');
        Route::post('/damaged/{id}/reject', [DamagedReportController::class, 'reject'])->name('damaged.reject');
    });

    // 4. Approvals (Owner only)
    Route::middleware(['role:owner'])->group(function () {
        Route::post('/po/{id}/approve', [PurchaseOrderController::class, 'approve'])->name('po.approve');
        Route::post('/po/{id}/reject', [PurchaseOrderController::class, 'reject'])->name('po.reject');
    });
});

require __DIR__.'/auth.php';
