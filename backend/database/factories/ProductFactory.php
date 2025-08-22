<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => 'PRD-' . strtoupper($this->faker->unique()->bothify('???###')),
            'description' => '<p>' . $this->faker->paragraphs(3, true) . '</p>',
            'short_description' => $this->faker->sentence(10),
            'price' => $this->faker->numberBetween(50000, 2000000),
            'compare_price' => $this->faker->optional(0.3)->numberBetween(60000, 2500000),
            'weight' => $this->faker->numberBetween(100, 2000),
            'dimensions' => $this->faker->numberBetween(10, 50) . 'x' .
                $this->faker->numberBetween(10, 50) . 'x' .
                $this->faker->numberBetween(1, 10),
            'status' => $this->faker->randomElement(['active', 'inactive', 'draft']),
            'is_featured' => $this->faker->boolean(20),
            'meta_data' => [
                'seo' => [
                    'title' => $name . ' - Premium Quality',
                    'description' => $this->faker->sentence(15),
                    'keywords' => implode(', ', $this->faker->words(5))
                ],
                'specs' => [
                    'material' => $this->faker->randomElement(['Cotton', 'Polyester', 'Leather', 'Plastic', 'Metal']),
                    'color' => $this->faker->colorName(),
                    'brand' => $this->faker->company(),
                ]
            ]
        ];
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
