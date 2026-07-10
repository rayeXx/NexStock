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
use App\Http\Controllers\DestructionController;
use App\Http\Controllers\FinancialAnalysisController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (will trigger login if unauthenticated)
// Welcome / Landing Page for guests, redirect authenticated users
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->role === 'staff_gudang') {
            return redirect()->route('inbound.index');
        }
        return redirect()->route('dashboard');
    }
    return view('welcome');
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
        Route::post('/store', [OutboundController::class, 'store'])->name('store');
        Route::get('/{id}/confirm', [OutboundController::class, 'showConfirm'])->name('confirm');
        Route::post('/{id}/confirm', [OutboundController::class, 'confirm']);
    });

    Route::prefix('damaged')->name('damaged.')->group(function () {
        Route::get('/', [DamagedReportController::class, 'index'])->name('index');
        Route::get('/create', [DamagedReportController::class, 'create'])->name('create');
        Route::post('/store', [DamagedReportController::class, 'store'])->name('store');
        Route::get('/{id}/confirm-expired', [DamagedReportController::class, 'showConfirmExpired'])->name('confirm-expired');
        Route::post('/{id}/confirm-expired', [DamagedReportController::class, 'confirmExpired'])->name('confirm-expired-post');
    });

    Route::prefix('destruction')->name('destruction.')->group(function () {
        Route::get('/', [DestructionController::class, 'index'])->name('index');
        Route::get('/{id}/confirm', [DestructionController::class, 'showConfirm'])->name('confirm');
        Route::post('/{id}/confirm', [DestructionController::class, 'confirm'])->name('confirm-post');
    });

    Route::prefix('opname')->name('opname.')->group(function () {
        Route::get('/', [StockOpnameController::class, 'index'])->name('index');
        Route::get('/create', [StockOpnameController::class, 'create'])->name('create');
        Route::post('/store', [StockOpnameController::class, 'store'])->name('store');
        Route::post('/{id}/approve', [StockOpnameController::class, 'approve'])->name('approve');
        Route::get('/{id}', [StockOpnameController::class, 'show'])->name('show');
    });



    // 2. Master Data & Procurement (Admin & Owner only for read, Admin only for write)
    Route::middleware(['role:admin_gudang,owner'])->group(function () {
        // Read-only endpoints
        Route::get('/product', [ProductController::class, 'index'])->name('product.index');
        Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');
        Route::get('/product/{product}/edit', [ProductController::class, 'edit'])->name('product.edit');
        Route::put('/product/{product}', [ProductController::class, 'update'])->name('product.update');
        Route::patch('/product/{product}', [ProductController::class, 'update']);
        
        Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
        Route::get('/supplier/{id}', [SupplierController::class, 'show'])->name('supplier.show');
        
        Route::get('/rack', [RackController::class, 'index'])->name('rack.index');

        // Procurement PO (View)
        Route::prefix('po')->name('po.')->group(function () {
            Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
            Route::get('/{id}', [PurchaseOrderController::class, 'show'])->name('show');
        });



        // User management index accessible by Admin & Owner
        Route::get('/user', [UserController::class, 'index'])->name('user.index');

        // Write actions for Master Data, Procurement, and User Management (Admin only)
        Route::middleware(['role:admin_gudang'])->group(function () {
            // Product write (Admin only for create and delete)
            Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
            Route::post('/product', [ProductController::class, 'store'])->name('product.store');
            Route::delete('/product/{product}', [ProductController::class, 'destroy'])->name('product.destroy');


            // Supplier write
            Route::get('/supplier/create', [SupplierController::class, 'create'])->name('supplier.create');
            Route::post('/supplier', [SupplierController::class, 'store'])->name('supplier.store');
            Route::get('/supplier/{supplier}/edit', [SupplierController::class, 'edit'])->name('supplier.edit');
            Route::put('/supplier/{supplier}', [SupplierController::class, 'update'])->name('supplier.update');
            Route::patch('/supplier/{supplier}', [SupplierController::class, 'update']);
            Route::delete('/supplier/{supplier}', [SupplierController::class, 'destroy'])->name('supplier.destroy');

            // Rack write
            Route::get('/rack/create', [RackController::class, 'create'])->name('rack.create');
            Route::post('/rack', [RackController::class, 'store'])->name('rack.store');
            Route::get('/rack/{rack}/edit', [RackController::class, 'edit'])->name('rack.edit');
            Route::put('/rack/{rack}', [RackController::class, 'update'])->name('rack.update');
            Route::patch('/rack/{rack}', [RackController::class, 'update']);
            Route::delete('/rack/{rack}', [RackController::class, 'destroy'])->name('rack.destroy');

            // User management write
            Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
            Route::post('/user', [UserController::class, 'store'])->name('user.store');
            Route::get('/user/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
            Route::put('/user/{user}', [UserController::class, 'update'])->name('user.update');
            Route::patch('/user/{user}', [UserController::class, 'update']);
            Route::delete('/user/{user}', [UserController::class, 'destroy'])->name('user.destroy');

            // PO write / action
            Route::prefix('po')->name('po.')->group(function () {
                Route::get('/draft/create', [PurchaseOrderController::class, 'create'])->name('create');
                Route::post('/store', [PurchaseOrderController::class, 'store'])->name('store');
                Route::post('/{id}/order', [PurchaseOrderController::class, 'order'])->name('order');
                Route::post('/{id}/history/{historyId}/retur', [InboundController::class, 'updateRetur'])->name('update-retur');
                Route::post('/{id}/history/{historyId}/mark-as-returned', [PurchaseOrderController::class, 'markAsReturned'])->name('mark-as-returned');
                Route::delete('/{id}', [PurchaseOrderController::class, 'destroy'])->name('destroy');
            });

            // Damaged Report Approvals & Destruction Assignment (Admin Gudang)
            Route::post('/damaged/{id}/approve', [DamagedReportController::class, 'approve'])->name('damaged.approve');
            Route::post('/damaged/{id}/reject', [DamagedReportController::class, 'reject'])->name('damaged.reject');
            Route::post('/destruction/{id}/assign', [DestructionController::class, 'assign'])->name('destruction.assign');
        });
    });

    // 4. Approvals & Financial Analysis (Owner only)
    Route::middleware(['role:owner'])->group(function () {
        Route::post('/po/{id}/approve', [PurchaseOrderController::class, 'approve'])->name('po.approve');
        Route::post('/po/{id}/reject', [PurchaseOrderController::class, 'reject'])->name('po.reject');

        // Financial Analysis
        Route::get('/financial-analysis', [FinancialAnalysisController::class, 'index'])->name('financial.index');
        Route::get('/financial-analysis/ai-refresh', [FinancialAnalysisController::class, 'aiRefresh'])->name('financial.ai-refresh');
    });
});

require __DIR__.'/auth.php';
