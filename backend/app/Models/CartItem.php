<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'price'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->price;
    }

    public function getCurrentInventory()
    {
        return Inventory::where('product_id', $this->product_id)
            ->where('product_variant_id', $this->product_variant_id)
            ->first();
    }

    public function isAvailable()
    {
        $inventory = $this->getCurrentInventory();
        return $inventory && $inventory->canFulfill($this->quantity);
    }
}
