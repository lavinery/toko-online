<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'subtotal' => $this->subtotal,
            'shipping_cost' => $this->shipping_cost,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total' => $this->total,
            'payment_status' => $this->payment_status,
            'shipping_status' => $this->shipping_status,
            'payment_gateway' => $this->payment_gateway,
            'shipping_address' => $this->shipping_address,
            'notes' => $this->notes,
            'paid_at' => $this->paid_at,
            'shipped_at' => $this->shipped_at,
            'delivered_at' => $this->delivered_at,
            'created_at' => $this->created_at,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'vouchers' => OrderVoucherResource::collection($this->whenLoaded('vouchers')),
            'shipment' => new ShipmentResource($this->whenLoaded('shipment')),
            'can_be_cancelled' => $this->canBeCancelled(),
        ];
    }
}
