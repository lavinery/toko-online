<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'courier' => $this->courier,
            'service' => $this->service,
            'cost' => $this->cost,
            'weight' => $this->weight,
            'tracking_number' => $this->tracking_number,
            'status' => $this->status,
            'shipped_at' => $this->shipped_at,
            'estimated_delivery' => $this->estimated_delivery,
            'delivered_at' => $this->delivered_at,
        ];
    }
}
