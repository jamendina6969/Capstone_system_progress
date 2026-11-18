<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ForecastSettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SalesRecordController;
use App\Http\Controllers\EoqSettingController;
use App\Http\Controllers\EoqCalibrationController;
use App\Http\Controllers\RestockBatchController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\UserGuideController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\SupplierReturnController;
use App\Http\Controllers\CustomerReturnController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BackupController;

// ==================== GUEST ROUTES ====================

Route::middleware('guest')->group(function () {
    Route::get('/login', [UserController::class, 'showLogin'])->name('login');
    Route::post('/login', [UserController::class, 'login']);
});

// ==================== AUTHENTICATED ROUTES ====================

Route::middleware('auth')->group(function () {

    // ==================== TEMPORARY CACHE CLEAR ====================
    Route::get('/clear-view-cache', function() {
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Cache::clear();
        return 'View and Application caches cleared successfully!';
    });

    // ==================== ADMIN USERS & BACKUPS ====================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);

        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups/create', [BackupController::class, 'create'])->name('backups.create');
        Route::get('/backups/download/{filename}', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('/backups/{filename}', [BackupController::class, 'delete'])->name('backups.delete');
        Route::post('/backups/restore', [BackupController::class, 'restore'])->name('backups.restore');
    });

    // ==================== ADMIN / MANAGER SETTINGS ====================
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('/forecast/settings', [ForecastSettingController::class, 'index'])->name('forecast.settings');
        Route::post('/forecast/settings/global', [ForecastSettingController::class, 'updateGlobal'])->name('forecast.settings.global');
        Route::post('/forecast/settings', [ForecastSettingController::class, 'storeOverride'])->name('forecast.settings.store');
        Route::delete('/forecast/settings/{forecastSetting}', [ForecastSettingController::class, 'destroyOverride'])->name('forecast.settings.destroy');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{id}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    });

    // ==================== INVENTORY / SALES / FORECAST ====================
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
        Route::post('/inventory/{product}/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer');
        Route::get('/inventory/logs', [InventoryController::class, 'logs'])->name('inventory.logs');
        Route::get('/inventory/approvals', [InventoryController::class, 'pendingApprovals'])->name('inventory.approvals');
        Route::post('/inventory/approvals/{movement}/approve', [InventoryController::class, 'approve'])->name('inventory.approve');
        Route::post('/inventory/approvals/{movement}/reject', [InventoryController::class, 'reject'])->name('inventory.reject');

        Route::get('/sales/template', [SalesRecordController::class, 'downloadTemplate'])->name('sales.template');
        Route::post('/sales/sync-records', [SalesRecordController::class, 'syncRecords'])->name('sales.syncRecords');
        Route::get('/sales/import', [SalesRecordController::class, 'showImport'])->name('sales.import');
        Route::post('/sales/import', [SalesRecordController::class, 'processImport'])->name('sales.import.process');

        Route::get('/forecast/eoq-settings', [EoqSettingController::class, 'index'])->name('eoq.settings');
        Route::post('/forecast/eoq-settings', [EoqSettingController::class, 'update'])->name('eoq.settings.update');
        Route::get('/forecast/eoq-calibration', [EoqCalibrationController::class, 'index'])->name('eoq.calibration');
        Route::post('/forecast/eoq-calibration/apply', [EoqCalibrationController::class, 'applyBatch'])->name('eoq.calibration.apply');
    });

    // ==================== PRODUCTS / CATEGORIES ====================
    Route::middleware('role:admin,manager')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('products', ProductController::class)->except(['show']);

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
        Route::get('/categories/subcategories/{category}', [CategoryController::class, 'showSubcategoryDetail'])->name('categories.subcategories.show');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::post('/categories/{category}/subcategories', [CategoryController::class, 'storeSubcategory'])->name('categories.subcategories.store');

        Route::put('/categories/subcategories/{subcategory}', [CategoryController::class, 'updateSubcategory'])->name('categories.subcategories.update');
        Route::delete('/categories/subcategories/{subcategory}', [CategoryController::class, 'destroySubcategory'])->name('categories.subcategories.destroy');

        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // ==================== POS / CHECKOUT / RECEIPTS / SALES VIEW ====================
    Route::middleware('role:admin,manager,staff')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos');
        Route::post('/pos/add/{id}', [PosController::class, 'addToCart'])->name('pos.add');
        Route::post('/pos/remove/{id}', [PosController::class, 'removeFromCart'])->name('pos.remove');
        Route::post('/pos/clear', [PosController::class, 'clearCart'])->name('pos.clear');
        Route::post('/pos/hold', [PosController::class, 'holdOrder'])->name('pos.hold');
        Route::post('/pos/parked/{index}/restore', [PosController::class, 'restoreParkedOrder'])->name('pos.parked.restore');
        Route::post('/pos/parked/{index}/add', [PosController::class, 'addParkedOrder'])->name('pos.parked.add');
        Route::delete('/pos/parked/{index}', [PosController::class, 'deleteParkedOrder'])->name('pos.parked.delete');
        Route::post('/pos/discount', [PosController::class, 'applyDiscount'])->name('pos.discount');
        Route::post('/pos/item-discount/{id}', [PosController::class, 'applyItemDiscount'])->name('pos.item-discount');

        Route::post('/pos/add-project', [PosController::class, 'addProjectToCart'])->name('pos.addProject');
        Route::get('/pos/project/{id}/details', [PosController::class, 'getProjectDetails'])->name('pos.project.details');
        Route::post('/pos/update-project/{projectId}', [PosController::class, 'updateProjectInCart'])->name('pos.updateProject');

        Route::post('/pos/project/{projectId}/inline-update', [PosController::class, 'updateProjectInline'])->name('pos.updateProjectInline');
        Route::post('/pos/project/quick-add', [PosController::class, 'quickAddMaterial'])->name('pos.quickAddMaterial');
        Route::post('/pos/project/quick-drop', [PosController::class, 'quickDropMaterial'])->name('pos.quickDropMaterial');

        Route::get('/checkout', [PosController::class, 'checkout'])->name('checkout');
        Route::post('/checkout', [PosController::class, 'processCheckout'])->name('checkout.process');
        
        Route::get('/receipt/{id}', [TransactionController::class, 'showReceipt'])->name('receipt');
        Route::get('/transactions/payment/{id}/receipt', [TransactionController::class, 'showPaymentReceipt'])->name('transaction.payment.receipt');
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transaction.history');

        Route::get('/sales', [SalesRecordController::class, 'index'])->name('sales.records');
    });

    // ==================== CUSTOMER RETURNS ====================
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('/customer-returns', [CustomerReturnController::class, 'index'])->name('customer.returns.index');
        Route::get('/customer-returns/search-transactions', [CustomerReturnController::class, 'searchTransactions'])->name('customer.returns.searchTransactions');
        Route::get('/customer-returns/verify-sku', [CustomerReturnController::class, 'verifySku'])->name('customer.returns.verifySku');
        Route::post('/transactions/{transaction}/return', [CustomerReturnController::class, 'store'])->name('transactions.return');
        Route::get('/transactions/{transaction}/return-items', [CustomerReturnController::class, 'items'])->name('transactions.return.items');
        Route::post('/returns/{customerReturn}/void', [CustomerReturnController::class, 'void'])->name('returns.void');
    });

    // ==================== INVENTORY STAFF ROUTES ====================
    Route::middleware('role:admin,manager,warehouse_staff,staff')->group(function () {
        Route::get('/inventory/view', [InventoryController::class, 'index'])->name('inventory.view');
        Route::post('/inventory/{product}/restock', [InventoryController::class, 'restock'])->name('inventory.restock');

        Route::get('/inventory/my-requests', [InventoryController::class, 'myRequests'])->name('inventory.myRequests');
        Route::post('/inventory/my-requests/store', [InventoryController::class, 'storeRequest'])->name('inventory.storeRequest');
        Route::post('/inventory/my-requests/{movement}/receive', [InventoryController::class, 'receiveRequest'])->name('inventory.receiveRequest');
        
        Route::get('/inventory/restock-mode', [RestockBatchController::class, 'index'])->name('inventory.restockBatch');
        Route::post('/inventory/restock-mode', [RestockBatchController::class, 'store'])->name('inventory.restockBatch.store');
    });

    // ==================== SUPPLIERS & PURCHASE ORDERS ====================
    Route::middleware('role:admin,manager')->group(function () {
        Route::prefix('suppliers')->name('suppliers.')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('index');
            Route::post('/', [SupplierController::class, 'store'])->name('store');
            Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
            Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
            Route::post('/{supplier}/payment', [SupplierController::class, 'addPayment'])->name('payment');
            Route::get('/{supplier}/ledger', [SupplierController::class, 'ledger'])->name('ledger');
            Route::post('/{supplier}/rate', [SupplierController::class, 'rate'])->name('rate');
            Route::get('/discrepancy-report', [SupplierController::class, 'discrepancyReport'])->name('discrepancy');
        });

        Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
            Route::get('/', [PurchaseOrderController::class, 'index'])->name('index');
            Route::get('/create', [PurchaseOrderController::class, 'create'])->name('create');
            Route::post('/', [PurchaseOrderController::class, 'store'])->name('store');
            Route::get('/low-stock-suggestions', [PurchaseOrderController::class, 'lowStockSuggestions'])->name('lowStock');

            Route::get('/export', [PurchaseOrderController::class, 'export'])->name('export');
            Route::get('/import/template', [PurchaseOrderController::class, 'importTemplate'])->name('import.template');
            Route::post('/import', [PurchaseOrderController::class, 'import'])->name('import');

            Route::get('/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receiveForm'])->name('receiveForm');
            Route::patch('/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('receive');

            Route::get('/{purchaseOrder}/pdf', [PurchaseOrderController::class, 'downloadPdf'])->name('pdf');
        });

        Route::prefix('supplier-returns')->name('supplier-returns.')->group(function () {
            Route::get('/', [SupplierReturnController::class, 'index'])->name('index');
            Route::get('/create', [SupplierReturnController::class, 'create'])->name('create');
            Route::post('/', [SupplierReturnController::class, 'store'])->name('store');
            Route::post('/{supplierReturn}/approve', [SupplierReturnController::class, 'approve'])->name('approve');
            Route::post('/{supplierReturn}/reject', [SupplierReturnController::class, 'reject'])->name('reject');
        });
    });

    // ==================== PURCHASE REQUESTS ====================
    Route::middleware('role:admin,manager,staff,warehouse_staff')->group(function () {
        Route::prefix('purchase-requests')->name('purchase-requests.')->group(function () {
            Route::get('/', [PurchaseRequestController::class, 'index'])->name('index');
            Route::get('/create', [PurchaseRequestController::class, 'create'])->name('create');
            Route::post('/', [PurchaseRequestController::class, 'store'])->name('store');
        });
    });

    Route::middleware('role:admin,manager')->group(function () {
        Route::prefix('purchase-requests')->name('purchase-requests.')->group(function () {
            Route::post('/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve'])->name('approve');
            Route::post('/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject'])->name('reject');
            Route::post('/{purchaseRequest}/items/{item}/reject', [PurchaseRequestController::class, 'rejectItem'])->name('rejectItem');
            Route::get('/{purchaseRequest}/convert', [PurchaseRequestController::class, 'convertToPO'])->name('convert');
        });
    });

    // ==================== GENERAL ROUTES ====================
    Route::get('/user-guide', [UserGuideController::class, 'index'])->name('user-guide');
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    Route::post('/theme', [ThemeController::class, 'update'])->name('theme.update');
});