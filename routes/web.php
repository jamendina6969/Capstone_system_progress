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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AccountSettingsController;

// ==================== PUBLIC SEO & SITEMAP ROUTES ====================

Route::get('/sitemap.xml', function () {
    $rootUrl  = htmlspecialchars(url('/'), ENT_XML1, 'UTF-8');
    $loginUrl = htmlspecialchars(url('/login'), ENT_XML1, 'UTF-8');
    $lastmod  = now()->toAtomString();
    $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{$rootUrl}</loc>
        <lastmod>{$lastmod}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{$loginUrl}</loc>
        <lastmod>{$lastmod}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
</urlset>
XML;
    return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap');

// Root homepage serves HTTP 200 Login view to guests/search crawlers (no 302 redirect blocker) and Dashboard to authenticated users
Route::get('/', function (\Illuminate\Http\Request $request) {
    if (!\Illuminate\Support\Facades\Auth::check()) {
        return app(UserController::class)->showLogin();
    }
    return app(DashboardController::class)->index($request);
})->middleware(['user.active', 'password.changed'])->name('home');

// ==================== GUEST ROUTES ====================

Route::middleware('guest')->group(function () {
    Route::get('/login', [UserController::class, 'showLogin'])->name('login');
    Route::post('/login', [UserController::class, 'login']);

    // Dedicated Secret System Administrator Portal (configurable slug via ADMIN_PORTAL_SLUG)
    $adminPortalSlug = trim((string) config('app.admin_portal_slug', 'sys-portal-hydrotech'), '/');
    Route::get('/' . $adminPortalSlug, [UserController::class, 'showAdminLogin'])->name('admin.portal.login');
    Route::post('/' . $adminPortalSlug, [UserController::class, 'adminLogin'])->name('admin.portal.authenticate');
});

// Logout accessible via both GET & POST (handles expired sessions gracefully)
Route::match(['get', 'post'], '/logout', [UserController::class, 'logout'])->name('logout');

// ==================== AUTHENTICATED ROUTES ====================

Route::middleware(['auth', 'user.active', 'password.changed'])->group(function () {

    // Persistent DB-backed product image & unit SKU inspector endpoints
    Route::get('/products/{product}/image', [ProductController::class, 'serveImage'])->name('products.image');
    Route::get('/products/{product}/item-skus', [ProductController::class, 'itemSkus'])->name('products.itemSkus');

    // ==================== ROLE-BASED NOTIFICATIONS (ALL AUTHENTICATED ROLES) ====================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/poll', [NotificationController::class, 'poll'])->name('poll');
        Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('readAll');
        Route::match(['get', 'post'], '/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
    });

    // ==================== ACCOUNT SETTINGS & SELF-SERVICE SECURITY (ALL ROLES) ====================
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/settings', [AccountSettingsController::class, 'index'])->name('settings');
        Route::get('/avatar/{user}', [AccountSettingsController::class, 'serveAvatar'])->name('avatar');
        Route::put('/profile', [AccountSettingsController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [AccountSettingsController::class, 'updatePassword'])->name('password.update');
        Route::put('/action-password', [AccountSettingsController::class, 'updateActionPassword'])->name('actionPassword.update');
        Route::put('/preferences', [AccountSettingsController::class, 'updatePreferences'])->name('preferences.update');
        Route::delete('/sessions/others', [AccountSettingsController::class, 'revokeOtherSessions'])->name('sessions.revokeOthers');
        Route::delete('/sessions/{sessionId}', [AccountSettingsController::class, 'revokeSession'])->name('sessions.revoke');
    });

    // ==================== FIRST-LOGIN FORCED PASSWORD CHANGE ====================
    Route::get('/change-password', [UserController::class, 'showForcePasswordChange'])->name('password.force.edit');
    Route::post('/change-password', [UserController::class, 'updateForcePasswordChange'])->name('password.force.update');

    // ==================== TEMPORARY CACHE & MIGRATION HELPERS ====================
    Route::get('/clear-view-cache', function() {
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Cache::clear();
        return 'View and Application caches cleared successfully!';
    });

    Route::get('/run-migrations', function() {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $migrateOutput = trim(\Illuminate\Support\Facades\Artisan::output());

            \Illuminate\Support\Facades\Artisan::call('migrate:status');
            $statusOutput = \Illuminate\Support\Facades\Artisan::output();

            // Direct schema verification checks
            $categoryType = 'unknown';
            $uniqueIndexExists = false;
            try {
                $col = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM `categories` LIKE 'type'");
                if (!empty($col)) {
                    $categoryType = $col[0]->Type ?? 'found';
                }
                $idx = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM `demand_records` WHERE Key_name = 'demand_records_product_date_source_unique'");
                $uniqueIndexExists = !empty($idx);
            } catch (\Exception $ex) {
                // Ignore DB dialect differences
            }

            $schemaChecks = "1. categories.type: " . $categoryType . ($categoryType === 'varchar(50)' ? " [PASSED - 3NF Flexible]" : " [CHECK REQUIRED]") . "\n"
                          . "2. demand_records unique index: " . ($uniqueIndexExists ? "[PASSED - Unique Compound Constraint Active]" : "[NOT DETECTED]") . "\n";

            return '<pre style="font-family: monospace; background: #10141D; color: #4ade80; padding: 24px; border-radius: 12px; font-size: 13px; line-height: 1.6;">' 
                . "========================================================\n"
                . " HYDROTECH MIGRATION RUNNER & SCHEMA VERIFICATION\n"
                . "========================================================\n\n"
                . "--- STEP 1: ARTISAN MIGRATE EXECUTION ---\n"
                . ($migrateOutput ?: "INFO: Database is already up-to-date (no pending migrations).\n")
                . "\n--- STEP 2: SCHEMA FORMALIZATION AUDIT ---\n"
                . $schemaChecks
                . "\n--- STEP 3: MIGRATION HISTORY & BATCH STATUS ---\n"
                . $statusOutput
                . '</pre>';
        } catch (\Exception $e) {
            return '<pre style="font-family: monospace; background: #10141D; color: #f87171; padding: 24px; border-radius: 12px;">Migration Failed: ' . $e->getMessage() . '</pre>';
        }
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
        Route::get('/audit-logs/export', [AuditLogController::class, 'exportCsv'])->name('audit-logs.export');
        Route::get('/audit-logs/{id}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    });

    // ==================== INVENTORY / SALES / FORECAST ====================
    Route::middleware('role:admin,manager,warehouse_staff')->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
    });

    Route::middleware('role:admin,manager')->group(function () {
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
        Route::post('/products/transfer', [ProductController::class, 'transfer'])->name('products.transfer');

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
        Route::post('/pos/sync-offline', [PosController::class, 'syncOfflineOrders'])->name('pos.syncOffline');
        
        Route::get('/receipt/{id}', [TransactionController::class, 'showReceipt'])->name('receipt');
        Route::get('/transactions/payment/{id}/receipt', [TransactionController::class, 'showPaymentReceipt'])->name('transaction.payment.receipt');
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transaction.history');
    });

    // Enterprise-wide sales analytics: management only (cashiers use their own shift receipts)
    Route::middleware('role:admin,manager')->group(function () {
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
        Route::get('/inventory/live-sync', [InventoryController::class, 'liveSync'])->name('inventory.liveSync');
        Route::get('/inventory/view', [InventoryController::class, 'index'])->name('inventory.view');
        Route::get('/inventory/{product}/details', [InventoryController::class, 'details'])->name('inventory.details');
        Route::post('/inventory/{product}/restock', [InventoryController::class, 'restock'])->name('inventory.restock');

        Route::get('/inventory/my-requests', [InventoryController::class, 'myRequests'])->name('inventory.myRequests');
        Route::post('/inventory/my-requests/store', [InventoryController::class, 'storeRequest'])->name('inventory.storeRequest');
        Route::post('/inventory/my-requests/{movement}/receive', [InventoryController::class, 'receiveRequest'])->name('inventory.receiveRequest');
        
        Route::get('/inventory/restock-mode', [RestockBatchController::class, 'index'])->name('inventory.restockBatch');
        Route::post('/inventory/restock-mode', [RestockBatchController::class, 'store'])->name('inventory.restockBatch.store');
    });

    // ==================== INWARD GOODS RECEIVING (GRN) ====================
    // Warehouse staff physically verify supplier deliveries; PO creation/pricing stays manager-only.
    Route::middleware('role:admin,manager,warehouse_staff')->prefix('purchase-orders')->name('purchase-orders.')->group(function () {
        Route::get('/deliveries', [PurchaseOrderController::class, 'deliveries'])->name('deliveries');
        Route::get('/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receiveForm'])->name('receiveForm');
        Route::patch('/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('receive');
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
    Route::post('/theme', [ThemeController::class, 'update'])->name('theme.update');
});