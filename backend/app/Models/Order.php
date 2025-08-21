<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'subtotal',
        'shipping_cost',
        'tax_amount',
        'discount_amount',
        'total',
        'payment_status',
        'shipping_status',
        'payment_gateway',
        'payment_reference',
        'payment_data',
        'shipping_address',
        'notes',
        'idempotency_key',
        'paid_at',
        'shipped_at',
        'delivered_at'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'payment_data' => 'array',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function vouchers()
    {
        return $this->hasMany(OrderVoucher::class);
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    public function paymentLogs()
    {
        return $this->hasMany(PaymentLog::class);
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeShipped($query)
    {
        return $query->where('shipping_status', 'shipped');
    }

    // Status Methods
    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function isPending()
    {
        return $this->payment_status === 'pending';
    }

    public function isShipped()
    {
        return $this->shipping_status === 'shipped';
    }

    public function isDelivered()
    {
        return $this->shipping_status === 'delivered';
    }

    public function canBeCancelled()
    {
        return in_array($this->payment_status, ['pending', 'failed']) &&
            $this->shipping_status === 'pending';
    }

    // Status Updates
    public function markAsPaid($paymentReference = null)
    {
        $this->update([
            'payment_status' => 'paid',
            'payment_reference' => $paymentReference,
            'paid_at' => now(),
        ]);

        // Confirm inventory reservations
        foreach ($this->items as $item) {
            $inventory = Inventory::where('product_id', $item->product_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->first();

            if ($inventory) {
                $inventory->confirmReservation($item->quantity, 'order_paid', $this->id);
            }
        }

        return $this;
    }

    public function markAsShipped($trackingNumber = null)
    {
        $this->update([
            'shipping_status' => 'shipped',
            'shipped_at' => now(),
        ]);

        if ($this->shipment && $trackingNumber) {
            $this->shipment->update([
                'tracking_number' => $trackingNumber,
                'status' => 'picked_up',
                'shipped_at' => now(),
            ]);
        }

        return $this;
    }

    public function getRouteKeyName()
    {
        return 'code';
    }

    // Generate unique order code
    public static function generateOrderCode()
    {
        do {
            $code = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
