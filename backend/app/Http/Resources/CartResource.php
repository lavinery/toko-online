<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'session_id' => $this->session_id,
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'total_quantity' => $this->total_quantity,
            'subtotal' => $this->subtotal,
            'total_weight' => $this->total_weight,
            'updated_at' => $this->updated_at,
        ];
    }
}
