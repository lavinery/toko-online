<?php



namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderVoucherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'voucher_code' => $this->voucher_code,
            'discount_amount' => $this->discount_amount,
        ];
    }
}
