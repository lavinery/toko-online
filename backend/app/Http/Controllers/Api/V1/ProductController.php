<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Product\StoreProductRequest;
use App\Http\Requests\Api\V1\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use App\DTOs\ProductDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * Get paginated products with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'search', 'category', 'min_price', 'max_price', 
                'featured', 'sort', 'order', 'per_page'
            ]);
            
            $products = $this->productService->getAllProducts($filters);

            return response()->json([
                'data' => ProductResource::collection($products->items()),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
                'links' => [
                    'first' => $products->url(1),
                    'last' => $products->url($products->lastPage()),
                    'prev' => $products->previousPageUrl(),
                    'next' => $products->nextPageUrl(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch products',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get product detail by slug
     */
    public function show(string $slug): JsonResponse
    {
        try {
            $product = $this->productService->getProductBySlug($slug);

            if (!$product) {
                return response()->json([
                    'message' => 'Product not found'
                ], 404);
            }

            return response()->json([
                'data' => new ProductResource($product),
                'seo' => [
                    'title' => $product->meta_data['seo']['title'] ?? $product->name,
                    'description' => $product->meta_data['seo']['description'] ?? $product->short_description,
                    'keywords' => $product->meta_data['seo']['keywords'] ?? '',
                    'canonical' => url("/produk/{$product->slug}"),
                    'og_image' => $product->primaryImage?->url,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch product',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get featured products
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $limit = min($request->get('limit', 8), 20);
            $products = $this->productService->getFeaturedProducts($limit);

            return response()->json([
                'data' => ProductResource::collection($products)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch featured products',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Search products with suggestions
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100'
        ]);

        try {
            $query = $request->q;
            $limit = min($request->get('limit', 10), 50);
            
            $products = $this->productService->searchProducts($query, $limit);

            return response()->json([
                'query' => $query,
                'products' => ProductResource::collection($products),
                'total_found' => $products->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Search failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get product variants
     */
    public function variants(string $slug): JsonResponse
    {
        try {
            $product = $this->productService->getProductBySlug($slug);
            
            if (!$product) {
                return response()->json([
                    'message' => 'Product not found'
                ], 404);
            }
            
            $variants = $this->productService->getProductVariants($product);

            return response()->json([
                'data' => $variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'sku' => $variant->sku,
                        'price' => $variant->final_price,
                        'price_adjustment' => $variant->price_adjustment,
                        'stock' => $variant->available_stock,
                        'in_stock' => $variant->available_stock > 0,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch product variants',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
