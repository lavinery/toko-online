<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    /**
     * Get authenticated user's cart
     */
    public function show(): JsonResponse
    {
        $user = Auth::user();
        $cart = $user->cart()->with([
            'items.product.images',
            'items.variant'
        ])->first();

        if (!$cart) {
            $cart = Cart::create(['user_id' => $user->id]);
        }

        return response()->json([
            'data' => new CartResource($cart)
        ]);
    }

    /**
     * Add item to cart
     */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $cart = $user->cart ?? Cart::create(['user_id' => $user->id]);

            // Validate product and variant
            $product = Product::active()->findOrFail($request->product_id);
            $variant = null;

            if ($request->product_variant_id) {
                $variant = ProductVariant::where('product_id', $product->id)
                    ->findOrFail($request->product_variant_id);
            }

            // Check inventory
            $inventory = Inventory::where('product_id', $product->id)
                ->where('product_variant_id', $request->product_variant_id)
                ->first();

            if (!$inventory || !$inventory->canFulfill($request->quantity)) {
                throw ValidationException::withMessages([
                    'quantity' => ['Stok tidak mencukupi. Stok tersedia: ' . ($inventory->available_quantity ?? 0)]
                ]);
            }

            // Check if item already exists in cart
            $existingItem = $cart->items()
                ->where('product_id', $request->product_id)
                ->where('product_variant_id', $request->product_variant_id)
                ->first();

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $request->quantity;

                if (!$inventory->canFulfill($newQuantity)) {
                    throw ValidationException::withMessages([
                        'quantity' => ['Total quantity melebihi stok. Stok tersedia: ' . $inventory->available_quantity]
                    ]);
                }

                $existingItem->update(['quantity' => $newQuantity]);
                $cartItem = $existingItem;
            } else {
                // Calculate current price
                $price = $product->price + ($variant->price_adjustment ?? 0);

                $cartItem = $cart->items()->create([
                    'product_id' => $request->product_id,
                    'product_variant_id' => $request->product_variant_id,
                    'quantity' => $request->quantity,
                    'price' => $price,
                ]);
            }

            DB::commit();

            $cartItem->load(['product.images', 'variant']);

            return response()->json([
                'message' => 'Item berhasil ditambahkan ke keranjang',
                'data' => [
                    'item' => [
                        'id' => $cartItem->id,
                        'product_id' => $cartItem->product_id,
                        'product_name' => $cartItem->product->name,
                        'variant_id' => $cartItem->product_variant_id,
                        'variant_name' => $cartItem->variant?->name,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->price,
                        'subtotal' => $cartItem->subtotal,
                        'image' => $cartItem->product->primaryImage?->url,
                    ],
                    'cart_summary' => [
                        'total_items' => $cart->fresh()->total_quantity,
                        'subtotal' => $cart->fresh()->subtotal,
                    ]
                ]
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to add item to cart',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(CartItem $item, Request $request): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        try {
            // Ensure item belongs to user's cart
            if ($item->cart->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Check inventory
            $inventory = Inventory::where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->first();

            if (!$inventory || !$inventory->canFulfill($request->quantity)) {
                throw ValidationException::withMessages([
                    'quantity' => ['Stok tidak mencukupi. Stok tersedia: ' . ($inventory->available_quantity ?? 0)]
                ]);
            }

            $item->update(['quantity' => $request->quantity]);

            return response()->json([
                'message' => 'Quantity berhasil diupdate',
                'data' => [
                    'item' => [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'subtotal' => $item->subtotal,
                    ],
                    'cart_summary' => [
                        'total_items' => $item->cart->fresh()->total_quantity,
                        'subtotal' => $item->cart->fresh()->subtotal,
                    ]
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update cart item',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function remove(CartItem $item): JsonResponse
    {
        try {
            // Ensure item belongs to user's cart
            if ($item->cart->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $cart = $item->cart;
            $item->delete();

            return response()->json([
                'message' => 'Item berhasil dihapus dari keranjang',
                'data' => [
                    'cart_summary' => [
                        'total_items' => $cart->fresh()->total_quantity,
                        'subtotal' => $cart->fresh()->subtotal,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove cart item',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Clear entire cart
     */
    public function clear(): JsonResponse
    {
        try {
            $user = Auth::user();
            $cart = $user->cart;

            if ($cart) {
                $cart->items()->delete();
            }

            return response()->json([
                'message' => 'Keranjang berhasil dikosongkan',
                'data' => [
                    'cart_summary' => [
                        'total_items' => 0,
                        'subtotal' => 0,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to clear cart',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Merge guest cart with user cart on login
     */
    public function mergeGuestCart(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1|max:100',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $cart = $user->cart ?? Cart::create(['user_id' => $user->id]);

            $mergedCount = 0;
            $skippedItems = [];

            foreach ($request->items as $guestItem) {
                // Check if item already exists in user cart
                $existingItem = $cart->items()
                    ->where('product_id', $guestItem['product_id'])
                    ->where('product_variant_id', $guestItem['product_variant_id'])
                    ->first();

                // Validate inventory
                $inventory = Inventory::where('product_id', $guestItem['product_id'])
                    ->where('product_variant_id', $guestItem['product_variant_id'])
                    ->first();

                $requestedQuantity = $guestItem['quantity'];
                if ($existingItem) {
                    $requestedQuantity += $existingItem->quantity;
                }

                if (!$inventory || !$inventory->canFulfill($requestedQuantity)) {
                    $skippedItems[] = [
                        'product_id' => $guestItem['product_id'],
                        'reason' => 'Insufficient stock'
                    ];
                    continue;
                }

                // Get product and variant for price calculation
                $product = Product::find($guestItem['product_id']);
                $variant = ProductVariant::find($guestItem['product_variant_id']);
                $price = $product->price + ($variant->price_adjustment ?? 0);

                if ($existingItem) {
                    $existingItem->update(['quantity' => $requestedQuantity]);
                } else {
                    $cart->items()->create([
                        'product_id' => $guestItem['product_id'],
                        'product_variant_id' => $guestItem['product_variant_id'],
                        'quantity' => $guestItem['quantity'],
                        'price' => $price,
                    ]);
                }

                $mergedCount++;
            }

            DB::commit();

            return response()->json([
                'message' => 'Guest cart merged successfully',
                'data' => [
                    'merged_items' => $mergedCount,
                    'skipped_items' => count($skippedItems),
                    'cart_summary' => [
                        'total_items' => $cart->fresh()->total_quantity,
                        'subtotal' => $cart->fresh()->subtotal,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to merge guest cart',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get cart summary with shipping calculation
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $cart = $user->cart;

            if (!$cart || $cart->items->isEmpty()) {
                return response()->json([
                    'data' => [
                        'items_count' => 0,
                        'subtotal' => 0,
                        'total_weight' => 0,
                        'shipping_cost' => 0,
                        'total' => 0,
                    ]
                ]);
            }

            $subtotal = $cart->subtotal;
            $totalWeight = $cart->total_weight;
            $shippingCost = 0;

            // Calculate shipping if destination provided
            if ($request->filled('destination_city_id') && $request->filled('courier')) {
                // This would integrate with shipping service
                // For now, return placeholder
                $shippingCost = 15000; // placeholder
            }

            return response()->json([
                'data' => [
                    'items_count' => $cart->total_quantity,
                    'subtotal' => $subtotal,
                    'total_weight' => $totalWeight,
                    'shipping_cost' => $shippingCost,
                    'total' => $subtotal + $shippingCost,
                    'items' => $cart->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product->name,
                            'variant_name' => $item->variant?->name,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'subtotal' => $item->subtotal,
                            'weight' => $item->product->weight * $item->quantity,
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get cart summary',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    // ==========================================
    // GUEST CART METHODS (Session-based)
    // ==========================================

    /**
     * Get guest cart
     */
    public function guestShow(Request $request): JsonResponse
    {
        $sessionId = $request->header('X-Session-ID') ?? session()->getId();

        $cart = Cart::where('session_id', $sessionId)
            ->with(['items.product.images', 'items.variant'])
            ->first();

        if (!$cart) {
            return response()->json([
                'data' => [
                    'items' => [],
                    'total_quantity' => 0,
                    'subtotal' => 0,
                ]
            ]);
        }

        return response()->json([
            'data' => new CartResource($cart)
        ]);
    }

    /**
     * Add item to guest cart
     */
    public function guestAdd(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        try {
            DB::beginTransaction();

            $sessionId = $request->header('X-Session-ID') ?? session()->getId();
            $cart = Cart::firstOrCreate(['session_id' => $sessionId]);

            // Same logic as authenticated add, but for guest cart
            $product = Product::active()->findOrFail($request->product_id);
            $variant = null;

            if ($request->product_variant_id) {
                $variant = ProductVariant::where('product_id', $product->id)
                    ->findOrFail($request->product_variant_id);
            }

            $inventory = Inventory::where('product_id', $product->id)
                ->where('product_variant_id', $request->product_variant_id)
                ->first();

            if (!$inventory || !$inventory->canFulfill($request->quantity)) {
                throw ValidationException::withMessages([
                    'quantity' => ['Stok tidak mencukupi. Stok tersedia: ' . ($inventory->available_quantity ?? 0)]
                ]);
            }

            $existingItem = $cart->items()
                ->where('product_id', $request->product_id)
                ->where('product_variant_id', $request->product_variant_id)
                ->first();

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $request->quantity;

                if (!$inventory->canFulfill($newQuantity)) {
                    throw ValidationException::withMessages([
                        'quantity' => ['Total quantity melebihi stok. Stok tersedia: ' . $inventory->available_quantity]
                    ]);
                }

                $existingItem->update(['quantity' => $newQuantity]);
                $cartItem = $existingItem;
            } else {
                $price = $product->price + ($variant->price_adjustment ?? 0);

                $cartItem = $cart->items()->create([
                    'product_id' => $request->product_id,
                    'product_variant_id' => $request->product_variant_id,
                    'quantity' => $request->quantity,
                    'price' => $price,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Item berhasil ditambahkan ke keranjang',
                'data' => [
                    'session_id' => $sessionId,
                    'cart_summary' => [
                        'total_items' => $cart->fresh()->total_quantity,
                        'subtotal' => $cart->fresh()->subtotal,
                    ]
                ]
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to add item to guest cart',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update guest cart item
     */
    public function guestUpdate(Request $request, string $itemId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        try {
            $sessionId = $request->header('X-Session-ID') ?? session()->getId();
            $cart = Cart::where('session_id', $sessionId)->first();

            if (!$cart) {
                return response()->json([
                    'message' => 'Cart not found'
                ], 404);
            }

            $item = $cart->items()->findOrFail($itemId);

            // Check inventory
            $inventory = $item->getCurrentInventory();
            if (!$inventory || !$inventory->canFulfill($request->quantity)) {
                return response()->json([
                    'message' => 'Insufficient stock',
                    'available_stock' => $inventory->available_quantity ?? 0
                ], 422);
            }

            $item->update(['quantity' => $request->quantity]);

            return response()->json([
                'message' => 'Cart item updated successfully',
                'data' => [
                    'cart_summary' => [
                        'total_items' => $cart->fresh()->total_quantity,
                        'subtotal' => $cart->fresh()->subtotal,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update cart item',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove guest cart item
     */
    public function guestRemove(Request $request, string $itemId): JsonResponse
    {
        try {
            $sessionId = $request->header('X-Session-ID') ?? session()->getId();
            $cart = Cart::where('session_id', $sessionId)->first();

            if (!$cart) {
                return response()->json([
                    'message' => 'Cart not found'
                ], 404);
            }

            $item = $cart->items()->findOrFail($itemId);
            $item->delete();

            return response()->json([
                'message' => 'Item removed from cart successfully',
                'data' => [
                    'cart_summary' => [
                        'total_items' => $cart->fresh()->total_quantity,
                        'subtotal' => $cart->fresh()->subtotal,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove cart item',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Clear guest cart
     */
    public function guestClear(Request $request): JsonResponse
    {
        try {
            $sessionId = $request->header('X-Session-ID') ?? session()->getId();
            $cart = Cart::where('session_id', $sessionId)->first();

            if ($cart) {
                $cart->items()->delete();
            }

            return response()->json([
                'message' => 'Cart cleared successfully',
                'data' => [
                    'cart_summary' => [
                        'total_items' => 0,
                        'subtotal' => 0,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to clear cart',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
