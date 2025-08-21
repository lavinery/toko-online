<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'gateway',
        'event_type',
        'raw_payload',
        'signature',
        'is_verified',
        'status',
        'error_message'
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'is_verified' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
