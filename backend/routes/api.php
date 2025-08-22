<?php

// routes/api.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\{
    AuthController,
    ProductController,
    CategoryController,
    CartController,
    CheckoutController,
    OrderController,
    ShippingController,
    VoucherController,
    UserController,
    AddressController,
    WishlistController,
    MidtransWebhookController,
    AdminProductController,
    AdminOrderController,
    AdminDashboardController
};

/*
|--------------------------------------------------------------------------
| API Routes V1
|--------------------------------------------------------------------------
*/



Route::prefix('v1')->group(function () {

    // ==========================================
    // PUBLIC ROUTES (No Authentication Required)
    // ==========================================

    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
        Route::post('verify-email', [AuthController::class, 'verifyEmail']);
        Route::post('resend-verification', [AuthController::class, 'resendVerification']);
    });

    // Public Catalog
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category:slug}', [CategoryController::class, 'show']);
    Route::get('categories/{category:slug}/products', [CategoryController::class, 'products']);

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/featured', [ProductController::class, 'featured']);
    Route::get('products/search', [ProductController::class, 'search']);
    Route::get('products/{product:slug}', [ProductController::class, 'show']);
    Route::get('products/{product:slug}/variants', [ProductController::class, 'variants']);
    Route::get('products/{product:slug}/reviews', [ProductController::class, 'reviews']);

    // Shipping Calculator (Public)
    Route::get('shipping/provinces', [ShippingController::class, 'provinces']);
    Route::get('shipping/cities', [ShippingController::class, 'cities']);
    Route::get('shipping/subdistricts', [ShippingController::class, 'subdistricts']);
    Route::post('shipping/cost', [ShippingController::class, 'calculateCost']);

    // Voucher Validation (Public)
    Route::post('vouchers/validate', [VoucherController::class, 'validate']);

    // Guest Cart (Session-based)
    Route::prefix('guest')->group(function () {
        Route::get('cart', [CartController::class, 'guestShow']);
        Route::post('cart/items', [CartController::class, 'guestAdd']);
        Route::patch('cart/items/{itemId}', [CartController::class, 'guestUpdate']);
        Route::delete('cart/items/{itemId}', [CartController::class, 'guestRemove']);
        Route::delete('cart/clear', [CartController::class, 'guestClear']);
    });

    // Webhooks (No Auth - but secured with signature verification)
    Route::prefix('webhooks')->group(function () {
        Route::post('midtrans', [MidtransWebhookController::class, 'handle']);
    });

    // ==========================================
    // AUTHENTICATED ROUTES (Require Login)
    // ==========================================

    Route::middleware('auth:api')->group(function () {

        // User Profile & Authentication
        Route::prefix('auth')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::patch('profile', [AuthController::class, 'updateProfile']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
        });

        // User Addresses
        Route::prefix('addresses')->group(function () {
            Route::get('/', [AddressController::class, 'index']);
            Route::post('/', [AddressController::class, 'store']);
            Route::get('{address}', [AddressController::class, 'show']);
            Route::patch('{address}', [AddressController::class, 'update']);
            Route::delete('{address}', [AddressController::class, 'destroy']);
            Route::post('{address}/set-default', [AddressController::class, 'setDefault']);
        });

        // Authenticated Cart
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'show']);
            Route::post('items', [CartController::class, 'add']);
            Route::patch('items/{item}', [CartController::class, 'update']);
            Route::delete('items/{item}', [CartController::class, 'remove']);
            Route::delete('clear', [CartController::class, 'clear']);
            Route::post('merge', [CartController::class, 'mergeGuestCart']); // Merge guest cart on login
            Route::get('summary', [CartController::class, 'summary']); // Cart totals with shipping
        });

        // Wishlist
        Route::prefix('wishlist')->group(function () {
            Route::get('/', [WishlistController::class, 'index']);
            Route::post('/', [WishlistController::class, 'add']);
            Route::delete('{product}', [WishlistController::class, 'remove']);
            Route::post('move-to-cart/{product}', [WishlistController::class, 'moveToCart']);
        });

        // Checkout & Orders
        Route::prefix('checkout')->group(function () {
            Route::post('/', [CheckoutController::class, 'process']);
            Route::post('validate', [CheckoutController::class, 'validate']); // Validate before checkout
        });

        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::get('{order:code}', [OrderController::class, 'show']);
            Route::post('{order:code}/cancel', [OrderController::class, 'cancel']);
            Route::post('{order:code}/confirm-delivery', [OrderController::class, 'confirmDelivery']);
            Route::get('{order:code}/invoice', [OrderController::class, 'downloadInvoice']);
            Route::get('{order:code}/tracking', [OrderController::class, 'trackShipment']);
        });

        // Vouchers
        Route::prefix('vouchers')->group(function () {
            Route::get('available', [VoucherController::class, 'available']);
        });

        // ==========================================
        // ADMIN ROUTES (Require Admin Role)
        // ==========================================

        Route::middleware('role:admin')->prefix('admin')->group(function () {

            // Dashboard & Analytics
            Route::get('dashboard', [AdminDashboardController::class, 'index']);
            Route::get('analytics/sales', [AdminDashboardController::class, 'salesAnalytics']);
            Route::get('analytics/products', [AdminDashboardController::class, 'productAnalytics']);
            Route::get('analytics/customers', [AdminDashboardController::class, 'customerAnalytics']);

            // Product Management
            Route::prefix('products')->group(function () {
                Route::get('/', [AdminProductController::class, 'index']);
                Route::post('/', [AdminProductController::class, 'store']);
                Route::get('{product}', [AdminProductController::class, 'show']);
                Route::patch('{product}', [AdminProductController::class, 'update']);
                Route::delete('{product}', [AdminProductController::class, 'destroy']);
                Route::post('{product}/images', [AdminProductController::class, 'uploadImages']);
                Route::delete('{product}/images/{image}', [AdminProductController::class, 'deleteImage']);
                Route::post('{product}/variants', [AdminProductController::class, 'addVariant']);
                Route::patch('{product}/variants/{variant}', [AdminProductController::class, 'updateVariant']);
                Route::delete('{product}/variants/{variant}', [AdminProductController::class, 'deleteVariant']);
                Route::post('{product}/duplicate', [AdminProductController::class, 'duplicate']);
                Route::post('bulk-update', [AdminProductController::class, 'bulkUpdate']);
                Route::post('import', [AdminProductController::class, 'import']);
                Route::get('export', [AdminProductController::class, 'export']);
            });

            // Category Management
            Route::prefix('categories')->group(function () {
                Route::get('/', [AdminCategoryController::class, 'index']);
                Route::post('/', [AdminCategoryController::class, 'store']);
                Route::get('{category}', [AdminCategoryController::class, 'show']);
                Route::patch('{category}', [AdminCategoryController::class, 'update']);
                Route::delete('{category}', [AdminCategoryController::class, 'destroy']);
                Route::post('{category}/reorder', [AdminCategoryController::class, 'reorder']);
            });

            // Inventory Management
            Route::prefix('inventory')->group(function () {
                Route::get('/', [AdminInventoryController::class, 'index']);
                Route::get('low-stock', [AdminInventoryController::class, 'lowStock']);
                Route::post('adjust', [AdminInventoryController::class, 'adjust']);
                Route::get('movements', [AdminInventoryController::class, 'movements']);
                Route::get('movements/{product}', [AdminInventoryController::class, 'productMovements']);
            });

            // Order Management
            Route::prefix('orders')->group(function () {
                Route::get('/', [AdminOrderController::class, 'index']);
                Route::get('{order:code}', [AdminOrderController::class, 'show']);
                Route::patch('{order:code}/status', [AdminOrderController::class, 'updateStatus']);
                Route::post('{order:code}/ship', [AdminOrderController::class, 'ship']);
                Route::post('{order:code}/refund', [AdminOrderController::class, 'refund']);
                Route::get('{order:code}/invoice', [AdminOrderController::class, 'generateInvoice']);
                Route::post('bulk-update', [AdminOrderController::class, 'bulkUpdate']);
                Route::get('export', [AdminOrderController::class, 'export']);
            });

            // Customer Management
            Route::prefix('customers')->group(function () {
                Route::get('/', [AdminCustomerController::class, 'index']);
                Route::get('{user}', [AdminCustomerController::class, 'show']);
                Route::patch('{user}/status', [AdminCustomerController::class, 'updateStatus']);
                Route::get('{user}/orders', [AdminCustomerController::class, 'orders']);
                Route::post('{user}/send-email', [AdminCustomerController::class, 'sendEmail']);
            });

            // Voucher Management
            Route::prefix('vouchers')->group(function () {
                Route::get('/', [AdminVoucherController::class, 'index']);
                Route::post('/', [AdminVoucherController::class, 'store']);
                Route::get('{voucher}', [AdminVoucherController::class, 'show']);
                Route::patch('{voucher}', [AdminVoucherController::class, 'update']);
                Route::delete('{voucher}', [AdminVoucherController::class, 'destroy']);
                Route::get('{voucher}/usage', [AdminVoucherController::class, 'usage']);
                Route::post('{voucher}/toggle', [AdminVoucherController::class, 'toggle']);
            });

            // Settings & Configuration
            Route::prefix('settings')->group(function () {
                Route::get('/', [AdminSettingsController::class, 'index']);
                Route::patch('/', [AdminSettingsController::class, 'update']);
                Route::get('shipping', [AdminSettingsController::class, 'shippingSettings']);
                Route::patch('shipping', [AdminSettingsController::class, 'updateShippingSettings']);
                Route::get('payment', [AdminSettingsController::class, 'paymentSettings']);
                Route::patch('payment', [AdminSettingsController::class, 'updatePaymentSettings']);
            });

            // Reports
            Route::prefix('reports')->group(function () {
                Route::get('sales', [AdminReportController::class, 'sales']);
                Route::get('products', [AdminReportController::class, 'products']);
                Route::get('customers', [AdminReportController::class, 'customers']);
                Route::get('inventory', [AdminReportController::class, 'inventory']);
                Route::get('financial', [AdminReportController::class, 'financial']);
            });

            // System Tools
            Route::prefix('tools')->group(function () {
                Route::post('revalidate-cache', [AdminToolsController::class, 'revalidateCache']);
                Route::post('sync-inventory', [AdminToolsController::class, 'syncInventory']);
                Route::get('logs', [AdminToolsController::class, 'logs']);
                Route::post('backup-database', [AdminToolsController::class, 'backupDatabase']);
            });
        });
    });

    // ==========================================
    // API STATUS & HEALTH CHECK
    // ==========================================

    Route::get('status', function () {
        return response()->json([
            'status' => 'OK',
            'version' => '1.0.0',
            'timestamp' => now()->toISOString(),
            'environment' => app()->environment(),
        ]);
    });

    Route::get('health', function () {
        // Basic health check
        $checks = [
            'database' => DB::connection()->getPdo() ? 'OK' : 'FAILED',
            'cache' => Cache::store()->getStore() ? 'OK' : 'FAILED',
            'storage' => Storage::exists('app') ? 'OK' : 'FAILED',
        ];

        $allHealthy = !in_array('FAILED', $checks);

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'timestamp' => now()->toISOString(),
        ], $allHealthy ? 200 : 503);
    });
});

// ==========================================
// FALLBACK ROUTES
// ==========================================

Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found',
        'documentation' => url('/api/documentation'),
    ], 404);
});
