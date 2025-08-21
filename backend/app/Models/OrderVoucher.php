<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'voucher_id',
        'voucher_code',
        'discount_amount'
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
