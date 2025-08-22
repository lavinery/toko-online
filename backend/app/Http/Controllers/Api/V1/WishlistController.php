<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist
     */
    public function index(): JsonResponse
    {
        try {
            $wishlistItems = Wishlist::where('user_id', Auth::id())
                ->with(['product.images', 'product.categories'])
                ->orderBy('created_at', 'desc')
                ->get();

            $products = $wishlistItems->map(function ($item) {
                return $item->product;
            })->filter();

            return response()->json([
                'data' => ProductResource::collection($products),
                'total' => $wishlistItems->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch wishlist',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Add product to wishlist
     */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        try {
            $product = Product::active()->findOrFail($request->product_id);
            $userId = Auth::id();

            // Check if already in wishlist
            $existingItem = Wishlist::where('user_id', $userId)
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                return response()->json([
                    'message' => 'Product already in wishlist'
                ], 409);
            }

            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $product->id
            ]);

            return response()->json([
                'message' => 'Product added to wishlist successfully',
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'in_wishlist' => true
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add product to wishlist',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove product from wishlist
     */
    public function remove(Product $product): JsonResponse
    {
        try {
            $userId = Auth::id();

            $wishlistItem = Wishlist::where('user_id', $userId)
                ->where('product_id', $product->id)
                ->first();

            if (!$wishlistItem) {
                return response()->json([
                    'message' => 'Product not found in wishlist'
                ], 404);
            }

            $wishlistItem->delete();

            return response()->json([
                'message' => 'Product removed from wishlist successfully',
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'in_wishlist' => false
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove product from wishlist',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Move product from wishlist to cart
     */
    public function moveToCart(Product $product, Request $request): JsonResponse
    {
        $request->validate([
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'integer|min:1|max:100'
        ]);

        try {
            $userId = Auth::id();
            $user = Auth::user();

            // Check if product is in wishlist
            $wishlistItem = Wishlist::where('user_id', $userId)
                ->where('product_id', $product->id)
                ->first();

            if (!$wishlistItem) {
                return response()->json([
                    'message' => 'Product not found in wishlist'
                ], 404);
            }

            // Add to cart
            $cart = $user->cart ?? $user->cart()->create();
            $quantity = $request->quantity ?? 1;

            // Check inventory
            $inventory = $product->inventories()
                ->where('product_variant_id', $request->product_variant_id)
                ->first();

            if (!$inventory || !$inventory->canFulfill($quantity)) {
                return response()->json([
                    'message' => 'Insufficient stock',
                    'available_stock' => $inventory->available_quantity ?? 0
                ], 422);
            }

            // Calculate price
            $variant = null;
            if ($request->product_variant_id) {
                $variant = $product->variants()->find($request->product_variant_id);
            }
            $price = $product->price + ($variant->price_adjustment ?? 0);

            // Add to cart
            $cart->addItem($product->id, $request->product_variant_id, $quantity);

            // Remove from wishlist
            $wishlistItem->delete();

            return response()->json([
                'message' => 'Product moved to cart successfully',
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'moved_to_cart' => true,
                    'cart_total_items' => $cart->fresh()->total_quantity
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to move product to cart',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}