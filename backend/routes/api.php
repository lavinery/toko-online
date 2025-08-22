<?php

// routes/api.php (Simplified - Remove references to missing controllers)
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
    AddressController,
    WishlistController,
    MidtransWebhookController
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
            Route::post('merge', [CartController::class, 'mergeGuestCart']);
            Route::get('summary', [CartController::class, 'summary']);
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
            Route::post('validate', [CheckoutController::class, 'validate']);
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
        // ADMIN ROUTES (Placeholder - implement later)
        // ==========================================

        Route::middleware('role:admin')->prefix('admin')->group(function () {
            // TODO: Implement admin routes when controllers are created
            Route::get('dashboard', function () {
                return response()->json([
                    'message' => 'Admin dashboard - Coming soon',
                    'data' => [
                        'total_orders' => 0,
                        'total_products' => 0,
                        'total_customers' => 0,
                        'total_revenue' => 0
                    ]
                ]);
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
            'database' => \DB::connection()->getPdo() ? 'OK' : 'FAILED',
            'cache' => \Cache::store()->getStore() ? 'OK' : 'FAILED',
            'storage' => \Storage::exists('app') ? 'OK' : 'FAILED',
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
