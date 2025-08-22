<?php

// app/Providers/RepositoryServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Repositories\ProductRepository;
use App\Services\{
    CartService,
    OrderService,
    ProductService,
    ImageService,
    InventoryService
};
use App\Services\Payment\MidtransService;
use App\Services\Shipping\ShippingService;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);

        // Register services as singletons
        $this->app->singleton(CartService::class, function ($app) {
            return new CartService();
        });

        $this->app->singleton(OrderService::class, function ($app) {
            return new OrderService(
                $app->make(MidtransService::class),
                $app->make(ShippingService::class),
                $app->make(InventoryService::class)
            );
        });

        $this->app->singleton(ProductService::class, function ($app) {
            return new ProductService(
                $app->make(ProductRepositoryInterface::class),
                $app->make(ImageService::class),
                $app->make(InventoryService::class)
            );
        });

        $this->app->singleton(ImageService::class, function ($app) {
            return new ImageService();
        });

        $this->app->singleton(InventoryService::class, function ($app) {
            return new InventoryService();
        });

        $this->app->singleton(MidtransService::class, function ($app) {
            return new MidtransService();
        });

        $this->app->singleton(ShippingService::class, function ($app) {
            return new ShippingService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
