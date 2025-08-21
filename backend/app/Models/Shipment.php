<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'courier',
        'service',
        'cost',
        'weight',
        'tracking_number',
        'origin_address',
        'destination_address',
        'shipping_data',
        'status',
        'shipped_at',
        'estimated_delivery',
        'delivered_at'
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'shipping_data' => 'array',
        'shipped_at' => 'datetime',
        'estimated_delivery' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        // Update order status
        $this->order->update([
            'shipping_status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }
}
