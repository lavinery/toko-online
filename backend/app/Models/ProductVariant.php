<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price_adjustment',
        'sort_order'
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFinalPriceAttribute()
    {
        return $this->product->price + $this->price_adjustment;
    }

    public function getAvailableStockAttribute()
    {
        $inventory = $this->inventory;
        return $inventory ? $inventory->quantity - $inventory->reserved_quantity : 0;
    }
}
