<?php

namespace Tests\Feature\Api\V1;

use App\Models\{User, Product, Cart, CartItem, Inventory};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_authenticated_user_can_view_cart(): void
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/cart');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'items',
                    'total_quantity',
                    'subtotal'
                ]
            ]);
    }

    public function test_user_can_add_item_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->active()->create();

        // Create inventory
        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        $token = auth('api')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'item',
                    'cart_summary'
                ]
            ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_user_cannot_add_out_of_stock_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->active()->create();

        // Create inventory with 0 stock
        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 0,
            'reserved_quantity' => 0,
        ]);

        $token = auth('api')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_user_can_update_cart_item_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->active()->create();

        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        $cart = Cart::create(['user_id' => $user->id]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
        ]);

        $token = auth('api')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson("/api/v1/cart/items/{$cartItem->id}", [
            'quantity' => 3,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 3,
        ]);
    }

    public function test_user_can_remove_cart_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->active()->create();

        $cart = Cart::create(['user_id' => $user->id]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
        ]);

        $token = auth('api')->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/v1/cart/items/{$cartItem->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_guest_can_manage_cart(): void
    {
        $product = Product::factory()->active()->create();

        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        $sessionId = 'test-session-123';

        // Add item to guest cart
        $response = $this->withHeaders([
            'X-Session-ID' => $sessionId,
        ])->postJson('/api/v1/guest/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201);

        // View guest cart
        $response = $this->withHeaders([
            'X-Session-ID' => $sessionId,
        ])->getJson('/api/v1/guest/cart');

        $response->assertStatus(200)
            ->assertJsonPath('data.total_quantity', 2);
    }
}
