<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_can_get_products_list(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'price',
                            'images',
                            'categories'
                        ]
                    ],
                    'meta',
                    'links'
                ]);
    }

    public function test_can_get_product_by_slug(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->slug}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'price',
                        'images',
                        'categories',
                        'variants'
                    ],
                    'seo'
                ]);
    }

    public function test_can_search_products(): void
    {
        $product = Product::factory()->create(['name' => 'Test Product']);

        $response = $this->getJson('/api/v1/products/search?q=Test');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'query',
                    'products',
                    'total_found'
                ]);
    }

    public function test_can_get_featured_products(): void
    {
        Product::factory()->create(['is_featured' => true]);

        $response = $this->getJson('/api/v1/products/featured');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'price',
                            'is_featured'
                        ]
                    ]
                ]);
    }

    public function test_returns_404_for_non_existent_product(): void
    {
        $response = $this->getJson('/api/v1/products/non-existent-slug');

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'Product not found'
                ]);
    }

    public function test_can_filter_products_by_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $product->categories()->attach($category);

        $response = $this->getJson("/api/v1/products?category={$category->slug}");

        $response->assertStatus(200);
    }

    public function test_can_filter_products_by_price_range(): void
    {
        Product::factory()->create(['price' => 100000]);
        Product::factory()->create(['price' => 200000]);

        $response = $this->getJson('/api/v1/products?min_price=150000&max_price=250000');

        $response->assertStatus(200);
    }

    public function test_search_requires_minimum_query_length(): void
    {
        $response = $this->getJson('/api/v1/products/search?q=a');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['q']);
    }
}