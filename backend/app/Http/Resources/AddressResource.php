<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'province' => $this->province,
            'city' => $this->city,
            'subdistrict' => $this->subdistrict,
            'postal_code' => $this->postal_code,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'subdistrict_id' => $this->subdistrict_id,
            'is_default' => $this->is_default,
            'full_address' => $this->full_address,
        ];
    }
}
