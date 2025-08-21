<?php
// app/Models/Voucher.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'minimum_amount',
        'maximum_discount',
        'usage_limit',
        'used_count',
        'usage_limit_per_customer',
        'starts_at',
        'expires_at',
        'is_active',
        'conditions'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'conditions' => 'array',
    ];

    public function orderVouchers()
    {
        return $this->hasMany(OrderVoucher::class);
    }

    // Validation Methods
    public function isValid()
    {
        return $this->is_active &&
            now()->between($this->starts_at, $this->expires_at) &&
            ($this->usage_limit === null || $this->used_count < $this->usage_limit);
    }

    public function canBeUsedBy($userId, $cartTotal = 0)
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check minimum amount
        if ($this->minimum_amount && $cartTotal < $this->minimum_amount) {
            return false;
        }

        // Check usage per customer
        if ($this->usage_limit_per_customer && $userId) {
            $userUsage = $this->orderVouchers()
                ->whereHas('order', function ($query) use ($userId) {
                    $query->where('user_id', $userId)->where('payment_status', 'paid');
                })
                ->count();

            if ($userUsage >= $this->usage_limit_per_customer) {
                return false;
            }
        }

        return true;
    }

    public function calculateDiscount($cartTotal, $shippingCost = 0)
    {
        if (!$this->canBeUsedBy(auth()->id(), $cartTotal)) {
            return 0;
        }

        switch ($this->type) {
            case 'percentage':
                $discount = ($cartTotal * $this->value) / 100;
                return $this->maximum_discount ?
                    min($discount, $this->maximum_discount) : $discount;

            case 'fixed_amount':
                return min($this->value, $cartTotal);

            case 'free_shipping':
                return min($shippingCost, $this->maximum_discount ?: $shippingCost);

            default:
                return 0;
        }
    }

    public function incrementUsage()
    {
        $this->increment('used_count');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now());
    }

    public function getRouteKeyName()
    {
        return 'code';
    }
}
