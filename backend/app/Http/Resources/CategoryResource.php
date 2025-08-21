<?php

// app/Http/Resources/CategoryResource.php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image,
            'parent_id' => $this->parent_id,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'parent' => new CategoryResource($this->whenLoaded('parent')),
            'products_count' => $this->when(isset($this->products_count), $this->products_count),
        ];
    }
}
