<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSlug;
use App\Traits\Searchable;

class Product extends Model
{
    use HasFactory, HasSlug, Searchable;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'short_description',
        'price',
        'compare_price',
        'weight',
        'dimensions',
        'status',
        'is_featured',
        'meta_data'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'meta_data' => 'array', // IMPORTANT: Cast JSON field to array
    ];

    protected $searchable = ['name', 'description', 'sku'];

    // Rest of your model relationships and methods...
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->whereHas('inventories', function ($q) {
            $q->where('quantity', '>', 0);
        });
    }

    // Helpers
    public function getTotalStockAttribute()
    {
        return $this->inventories->sum('quantity');
    }

    public function getAvailableStockAttribute()
    {
        return $this->inventories->sum(function ($inventory) {
            return $inventory->quantity - $inventory->reserved_quantity;
        });
    }

    public function hasVariants()
    {
        return $this->variants()->count() > 0;
    }

    public function getDisplayPriceAttribute()
    {
        if ($this->hasVariants()) {
            $minPrice = $this->variants->min(function ($variant) {
                return $this->price + $variant->price_adjustment;
            });
            $maxPrice = $this->variants->max(function ($variant) {
                return $this->price + $variant->price_adjustment;
            });

            return $minPrice == $maxPrice ?
                "Rp " . number_format($minPrice, 0, ',', '.') :
                "Rp " . number_format($minPrice, 0, ',', '.') . " - " . number_format($maxPrice, 0, ',', '.');
        }

        return "Rp " . number_format($this->price, 0, ',', '.');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
