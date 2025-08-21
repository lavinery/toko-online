<?php

namespace App\Services;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\DTOs\ProductDTO;
use App\Models\Product;
use App\Services\ImageService;
use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private ImageService $imageService,
        private InventoryService $inventoryService
    ) {}

    public function getAllProducts(array $filters = []): LengthAwarePaginator
    {
        return $this->productRepository
            ->with(['categories', 'images', 'variants.inventory'])
            ->filterProducts($filters);
    }

    public function getProductBySlug(string $slug): ?Product
    {
        return $this->productRepository
            ->with([
                'categories',
                'images' => function ($query) {
                    $query->orderBy('sort_order');
                },
                'variants' => function ($query) {
                    $query->with('inventory')->orderBy('sort_order');
                }
            ])
            ->findBySlug($slug);
    }

    public function getFeaturedProducts(int $limit = 8): Collection
    {
        return $this->productRepository
            ->with(['categories', 'images', 'variants.inventory'])
            ->getFeatured($limit);
    }

    public function searchProducts(string $query, int $limit = 10): Collection
    {
        return $this->productRepository
            ->with(['categories', 'images'])
            ->searchByName($query)
            ->take($limit);
    }

    public function createProduct(ProductDTO $productDTO): Product
    {
        return DB::transaction(function () use ($productDTO) {
            // Create product
            $product = $this->productRepository->create($productDTO->toArray());

            // Attach categories
            if (!empty($productDTO->categoryIds)) {
                $product->categories()->attach($productDTO->categoryIds);
            }

            // Handle images
            if (!empty($productDTO->images)) {
                $this->imageService->attachImagesToProduct($product, $productDTO->images);
            }

            // Handle variants and inventory
            if (!empty($productDTO->variants)) {
                $this->createProductVariants($product, $productDTO->variants);
            } else {
                // Create default inventory for product without variants
                $this->inventoryService->createInventory($product->id, null, 0);
            }

            return $product->load(['categories', 'images', 'variants.inventory']);
        });
    }

    public function updateProduct(Product $product, ProductDTO $productDTO): Product
    {
        return DB::transaction(function () use ($product, $productDTO) {
            // Update product
            $this->productRepository->update($product, $productDTO->toArray());

            // Update categories
            if (!empty($productDTO->categoryIds)) {
                $product->categories()->sync($productDTO->categoryIds);
            }

            // Handle images if provided
            if (!empty($productDTO->images)) {
                $this->imageService->updateProductImages($product, $productDTO->images);
            }

            // Handle variants if provided
            if (!empty($productDTO->variants)) {
                $this->updateProductVariants($product, $productDTO->variants);
            }

            return $product->fresh(['categories', 'images', 'variants.inventory']);
        });
    }

    public function deleteProduct(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            // Delete associated images
            $this->imageService->deleteProductImages($product);

            // Delete product (cascading will handle variants and inventory)
            return $this->productRepository->delete($product);
        });
    }

    public function getProductVariants(Product $product): Collection
    {
        return $product->variants()
            ->with('inventory')
            ->orderBy('sort_order')
            ->get();
    }

    public function checkProductAvailability(Product $product, ?int $variantId = null, int $quantity = 1): bool
    {
        return $this->inventoryService->checkAvailability($product->id, $variantId, $quantity);
    }

    private function createProductVariants(Product $product, array $variants): void
    {
        foreach ($variants as $variantData) {
            $variant = $product->variants()->create([
                'name' => $variantData['name'],
                'sku' => $variantData['sku'],
                'price_adjustment' => $variantData['price_adjustment'] ?? 0,
                'sort_order' => $variantData['sort_order'] ?? 0,
            ]);

            // Create inventory for variant
            $this->inventoryService->createInventory(
                $product->id,
                $variant->id,
                $variantData['stock'] ?? 0
            );
        }
    }

    private function updateProductVariants(Product $product, array $variants): void
    {
        $existingVariantIds = $product->variants()->pluck('id')->toArray();
        $updatedVariantIds = [];

        foreach ($variants as $variantData) {
            if (isset($variantData['id']) && in_array($variantData['id'], $existingVariantIds)) {
                // Update existing variant
                $variant = $product->variants()->find($variantData['id']);
                $variant->update([
                    'name' => $variantData['name'],
                    'sku' => $variantData['sku'],
                    'price_adjustment' => $variantData['price_adjustment'] ?? 0,
                    'sort_order' => $variantData['sort_order'] ?? 0,
                ]);
                $updatedVariantIds[] = $variantData['id'];

                // Update inventory
                if (isset($variantData['stock'])) {
                    $this->inventoryService->updateInventory(
                        $product->id,
                        $variant->id,
                        $variantData['stock']
                    );
                }
            } else {
                // Create new variant
                $variant = $product->variants()->create([
                    'name' => $variantData['name'],
                    'sku' => $variantData['sku'],
                    'price_adjustment' => $variantData['price_adjustment'] ?? 0,
                    'sort_order' => $variantData['sort_order'] ?? 0,
                ]);
                $updatedVariantIds[] = $variant->id;

                // Create inventory for new variant
                $this->inventoryService->createInventory(
                    $product->id,
                    $variant->id,
                    $variantData['stock'] ?? 0
                );
            }
        }

        // Delete variants that weren't updated
        $variantsToDelete = array_diff($existingVariantIds, $updatedVariantIds);
        if (!empty($variantsToDelete)) {
            $product->variants()->whereIn('id', $variantsToDelete)->delete();
        }
    }
}