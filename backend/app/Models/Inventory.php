<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'quantity',
        'reserved_quantity',
        'minimum_stock'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function getAvailableQuantityAttribute()
    {
        return $this->quantity - $this->reserved_quantity;
    }

    public function isLowStock()
    {
        return $this->available_quantity <= $this->minimum_stock;
    }

    public function canFulfill($requestedQuantity)
    {
        return $this->available_quantity >= $requestedQuantity;
    }

    // Reserve stock untuk mencegah oversell
    public function reserve($quantity)
    {
        if (!$this->canFulfill($quantity)) {
            throw new \Exception('Insufficient stock to reserve');
        }

        $this->increment('reserved_quantity', $quantity);
        return $this;
    }

    // Release reserved stock
    public function release($quantity)
    {
        $this->decrement('reserved_quantity', min($quantity, $this->reserved_quantity));
        return $this;
    }

    // Confirm reserved stock (convert to actual sale)
    public function confirmReservation($quantity, $reason = 'order_confirmed', $referenceId = null)
    {
        $actualQuantity = min($quantity, $this->reserved_quantity);

        $this->decrement('reserved_quantity', $actualQuantity);
        $this->decrement('quantity', $actualQuantity);

        // Log movement
        $this->movements()->create([
            'type' => 'out',
            'quantity' => -$actualQuantity,
            'previous_quantity' => $this->quantity + $actualQuantity,
            'reason' => $reason,
            'reference_type' => 'order',
            'reference_id' => $referenceId,
        ]);

        return $this;
    }
}
