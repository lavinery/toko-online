<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * Get all categories with hierarchy
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $categories = Category::active()
                ->with(['children' => function ($query) {
                    $query->active()->orderBy('sort_order');
                }])
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get();

            return response()->json([
                'data' => CategoryResource::collection($categories)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch categories',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get category details
     */
    public function show(Category $category): JsonResponse
    {
        try {
            if (!$category->is_active) {
                return response()->json([
                    'message' => 'Category not found'
                ], 404);
            }

            $category->load(['children.children', 'parent']);

            return response()->json([
                'data' => new CategoryResource($category),
                'seo' => [
                    'title' => $category->name,
                    'description' => $category->description ?? "Produk kategori {$category->name}",
                    'canonical' => url("/kategori/{$category->slug}"),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch category',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get products in category
     */
    public function products(Category $category, Request $request): JsonResponse
    {
        try {
            if (!$category->is_active) {
                return response()->json([
                    'message' => 'Category not found'
                ], 404);
            }

            $filters = $request->only([
                'search',
                'min_price',
                'max_price',
                'sort',
                'order',
                'per_page'
            ]);
            $filters['category'] = $category->slug;

            $products = $this->productService->getAllProducts($filters);

            return response()->json([
                'data' => ProductResource::collection($products->items()),
                'category' => new CategoryResource($category),
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
                'message' => 'Failed to fetch category products',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
