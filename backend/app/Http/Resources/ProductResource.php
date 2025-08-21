<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->when($request->routeIs('*.show'), $this->description),
            'short_description' => $this->short_description,
            'price' => $this->price,
            'compare_price' => $this->compare_price,
            'display_price' => $this->display_price,
            'weight' => $this->weight,
            'dimensions' => $this->dimensions,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'total_stock' => $this->total_stock,
            'available_stock' => $this->available_stock,
            'has_variants' => $this->hasVariants(),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'primary_image' => new ProductImageResource($this->whenLoaded('primaryImage')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'specifications' => $this->when(
                $request->routeIs('*.show'),
                $this->meta_data['specs'] ?? []
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
