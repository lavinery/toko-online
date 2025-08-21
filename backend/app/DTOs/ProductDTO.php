<?php

namespace App\DTOs;

class ProductDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $sku,
        public readonly ?string $description,
        public readonly ?string $shortDescription,
        public readonly float $price,
        public readonly ?float $comparePrice,
        public readonly int $weight,
        public readonly ?string $dimensions,
        public readonly string $status,
        public readonly bool $isFeatured,
        public readonly ?array $metaData,
        public readonly array $categoryIds = [],
        public readonly array $variants = [],
        public readonly array $images = []
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'] ?? \Str::slug($data['name']),
            sku: $data['sku'],
            description: $data['description'] ?? null,
            shortDescription: $data['short_description'] ?? null,
            price: (float) $data['price'],
            comparePrice: isset($data['compare_price']) ? (float) $data['compare_price'] : null,
            weight: (int) ($data['weight'] ?? 0),
            dimensions: $data['dimensions'] ?? null,
            status: $data['status'] ?? 'active',
            isFeatured: (bool) ($data['is_featured'] ?? false),
            metaData: $data['meta_data'] ?? null,
            categoryIds: $data['category_ids'] ?? [],
            variants: $data['variants'] ?? [],
            images: $data['images'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'short_description' => $this->shortDescription,
            'price' => $this->price,
            'compare_price' => $this->comparePrice,
            'weight' => $this->weight,
            'dimensions' => $this->dimensions,
            'status' => $this->status,
            'is_featured' => $this->isFeatured,
            'meta_data' => $this->metaData,
        ];
    }
}