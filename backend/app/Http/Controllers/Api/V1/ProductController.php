<?php

// app/Http/Controllers/Api/V1/ProductController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Get paginated products with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['categories', 'images', 'variants.inventory'])
            ->active()
            ->inStock();

        // Search by name or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $category = $request->category;
            $query->whereHas('categories', function ($q) use ($category) {
                $q->where('slug', $category)
                    ->orWhere('id', $category);
            });
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by featured
        if ($request->boolean('featured')) {
            $query->featured();
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');

        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'popular':
                // Order by sales count (would need order_items relationship count)
                $query->withCount('orderItems')
                    ->orderBy('order_items_count', 'desc');
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = min($request->get('per_page', 12), 100); // Max 100 items
        $products = $query->paginate($perPage);

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
    }

    /**
     * Get product detail by slug
     */
    public function show(Product $product): JsonResponse
    {
        $product->load([
            'categories',
            'images' => function ($query) {
                $query->orderBy('sort_order');
            },
            'variants' => function ($query) {
                $query->with('inventory')->orderBy('sort_order');
            }
        ]);

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
    }

    /**
     * Get featured products
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 8), 20);

        $products = Product::with(['categories', 'images', 'variants.inventory'])
            ->active()
            ->featured()
            ->inStock()
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => ProductResource::collection($products)
        ]);
    }

    /**
     * Search products with suggestions
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100'
        ]);

        $query = $request->q;
        $limit = min($request->get('limit', 10), 50);

        // Main search
        $products = Product::with(['categories', 'images'])
            ->active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%")
                    ->orWhere('sku', 'LIKE', "%{$query}%");
            })
            ->limit($limit)
            ->get();

        // Search suggestions (categories)
        $categories = Category::active()
            ->where('name', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get(['id', 'name', 'slug']);

        return response()->json([
            'query' => $query,
            'products' => ProductResource::collection($products),
            'suggestions' => [
                'categories' => $categories,
            ],
            'total_found' => $products->count()
        ]);
    }

    /**
     * Get product variants
     */
    public function variants(Product $product): JsonResponse
    {
        $variants = $product->variants()
            ->with('inventory')
            ->orderBy('sort_order')
            ->get();

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
    }
}
