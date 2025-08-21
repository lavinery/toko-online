<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => $this->subtotal,
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'sku' => $this->product->sku,
                'weight' => $this->product->weight,
                'image' => $this->product->primaryImage?->url,
            ],
            'variant' => $this->when($this->variant, [
                'id' => $this->variant?->id,
                'name' => $this->variant?->name,
                'sku' => $this->variant?->sku,
            ]),
            'is_available' => $this->isAvailable(),
            'available_stock' => $this->getCurrentInventory()?->available_quantity ?? 0,
        ];
    }
}
