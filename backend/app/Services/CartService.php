<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Get or create cart for user
     */
    public function getOrCreateCart(?User $user = null, ?string $sessionId = null): Cart
    {
        if ($user) {
            return $user->cart ?? Cart::create(['user_id' => $user->id]);
        }

        if ($sessionId) {
            return Cart::firstOrCreate(['session_id' => $sessionId]);
        }

        throw new \InvalidArgumentException('Either user or session ID must be provided');
    }

    /**
     * Add item to cart with validation
     */
    public function addItem(Cart $cart, int $productId, ?int $variantId, int $quantity): CartItem
    {
        return DB::transaction(function () use ($cart, $productId, $variantId, $quantity) {
            // Validate product
            $product = Product::active()->findOrFail($productId);

            // Validate variant if provided
            $variant = null;
            if ($variantId) {
                $variant = ProductVariant::where('product_id', $productId)
                    ->findOrFail($variantId);
            }

            // Check inventory
            $inventory = Inventory::where('product_id', $productId)
                ->where('product_variant_id', $variantId)
                ->first();

            if (!$inventory || !$inventory->canFulfill($quantity)) {
                throw new \Exception('Insufficient stock');
            }

            // Check existing item
            $existingItem = $cart->items()
                ->where('product_id', $productId)
                ->where('product_variant_id', $variantId)
                ->first();

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $quantity;

                if (!$inventory->canFulfill($newQuantity)) {
                    throw new \Exception('Total quantity exceeds available stock');
                }

                $existingItem->update(['quantity' => $newQuantity]);
                return $existingItem;
            }

            // Create new cart item
            $price = $product->price + ($variant->price_adjustment ?? 0);

            return $cart->items()->create([
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
                'price' => $price,
            ]);
        });
    }

    /**
     * Update cart item quantity
     */
    public function updateItemQuantity(CartItem $item, int $quantity): CartItem
    {
        return DB::transaction(function () use ($item, $quantity) {
            // Check inventory
            $inventory = $item->getCurrentInventory();

            if (!$inventory || !$inventory->canFulfill($quantity)) {
                throw new \Exception('Insufficient stock');
            }

            $item->update(['quantity' => $quantity]);
            return $item;
        });
    }

    /**
     * Remove item from cart
     */
    public function removeItem(CartItem $item): bool
    {
        return $item->delete();
    }

    /**
     * Clear entire cart
     */
    public function clearCart(Cart $cart): bool
    {
        return $cart->items()->delete();
    }

    /**
     * Merge guest cart with user cart
     */
    public function mergeGuestCart(Cart $guestCart, Cart $userCart): array
    {
        $merged = 0;
        $skipped = 0;

        return DB::transaction(function () use ($guestCart, $userCart, &$merged, &$skipped) {
            foreach ($guestCart->items as $guestItem) {
                try {
                    // Check if item exists in user cart
                    $existingItem = $userCart->items()
                        ->where('product_id', $guestItem->product_id)
                        ->where('product_variant_id', $guestItem->product_variant_id)
                        ->first();

                    $quantity = $guestItem->quantity;
                    if ($existingItem) {
                        $quantity += $existingItem->quantity;
                    }

                    // Check inventory
                    $inventory = $guestItem->getCurrentInventory();
                    if (!$inventory || !$inventory->canFulfill($quantity)) {
                        $skipped++;
                        continue;
                    }

                    if ($existingItem) {
                        $existingItem->update(['quantity' => $quantity]);
                    } else {
                        $userCart->items()->create([
                            'product_id' => $guestItem->product_id,
                            'product_variant_id' => $guestItem->product_variant_id,
                            'quantity' => $guestItem->quantity,
                            'price' => $guestItem->price,
                        ]);
                    }

                    $merged++;
                } catch (\Exception $e) {
                    $skipped++;
                }
            }

            // Clear guest cart
            $guestCart->items()->delete();
            $guestCart->delete();

            return [
                'merged' => $merged,
                'skipped' => $skipped,
                'total_items' => $userCart->fresh()->total_quantity
            ];
        });
    }

    /**
     * Calculate cart totals with shipping
     */
    public function calculateTotals(Cart $cart, float $shippingCost = 0, float $discountAmount = 0): array
    {
        $subtotal = $cart->subtotal;
        $totalWeight = $cart->total_weight;
        $finalShippingCost = max(0, $shippingCost - $discountAmount);
        $total = $subtotal - $discountAmount + $finalShippingCost;

        return [
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'discount_amount' => $discountAmount,
            'final_shipping_cost' => $finalShippingCost,
            'total' => $total,
            'total_weight' => $totalWeight,
            'items_count' => $cart->total_quantity,
        ];
    }

    /**
     * Validate cart for checkout
     */
    public function validateForCheckout(Cart $cart): array
    {
        $errors = [];
        $unavailableItems = [];

        if (!$cart || $cart->items->isEmpty()) {
            $errors[] = 'Cart is empty';
            return ['valid' => false, 'errors' => $errors];
        }

        foreach ($cart->items as $item) {
            $inventory = $item->getCurrentInventory();

            if (!$inventory || !$inventory->canFulfill($item->quantity)) {
                $unavailableItems[] = [
                    'product_name' => $item->product->name,
                    'variant_name' => $item->variant?->name,
                    'requested_quantity' => $item->quantity,
                    'available_quantity' => $inventory->available_quantity ?? 0
                ];
            }
        }

        if (!empty($unavailableItems)) {
            $errors[] = 'Some items are out of stock';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'unavailable_items' => $unavailableItems
        ];
    }
}
