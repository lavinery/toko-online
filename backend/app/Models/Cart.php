<?php

namespace App\Models;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'session_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function getTotalQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }

    public function getSubtotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });
    }

    public function getTotalWeightAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->product->weight;
        });
    }

    public function addItem($productId, $variantId = null, $quantity = 1)
    {
        $existingItem = $this->items()
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $quantity);
            return $existingItem;
        }

        return $this->items()->create([
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'quantity' => $quantity,
            'price' => $this->getCurrentPrice($productId, $variantId),
        ]);
    }

    private function getCurrentPrice($productId, $variantId = null)
    {
        $product = Product::find($productId);
        $basePrice = $product->price;

        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            $basePrice += $variant->price_adjustment;
        }

        return $basePrice;
    }
}
