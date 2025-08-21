<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'value' => $this->value,
            'minimum_amount' => $this->minimum_amount,
            'maximum_discount' => $this->maximum_discount,
            'starts_at' => $this->starts_at,
            'expires_at' => $this->expires_at,
            'is_valid' => $this->isValid(),
            'usage_limit' => $this->usage_limit,
            'used_count' => $this->used_count,
            'usage_limit_per_customer' => $this->usage_limit_per_customer,
        ];
    }
}
