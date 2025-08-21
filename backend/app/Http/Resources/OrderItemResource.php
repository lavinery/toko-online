<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'product_name' => $this->product_name,
            'variant_name' => $this->variant_name,
            'product_sku' => $this->product_sku,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
        ];
    }
}
