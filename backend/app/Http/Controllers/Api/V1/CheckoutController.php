<?php

// app/Http/Controllers/Api/V1/CheckoutController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\{Cart, Order, OrderItem, OrderVoucher, Voucher, UserAddress, Inventory, Shipment};
use App\Services\{OrderService, InventoryService};
use App\Services\Payment\MidtransService;
use App\Services\Shipping\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private ShippingService $shippingService,
        private InventoryService $inventoryService
    ) {}

    /**
     * Validate checkout data before processing
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
            'courier' => 'required|string',
            'service' => 'required|string',
            'voucher_code' => 'nullable|string|exists:vouchers,code',
        ]);

        try {
            $user = Auth::user();
            $cart = $user->cart()->with('items.product', 'items.variant')->first();

            if (!$cart || $cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => ['Keranjang kosong']
                ]);
            }

            // Validate address
            $address = UserAddress::where('id', $request->address_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // Validate stock availability
            foreach ($cart->items as $item) {
                if (!$this->inventoryService->checkAvailability(
                    $item->product_id,
                    $item->product_variant_id,
                    $item->quantity
                )) {
                    throw ValidationException::withMessages([
                        'stock' => ["Stok {$item->product->name} tidak mencukupi"]
                    ]);
                }
            }

            // Calculate shipping cost
            $shippingCost = $this->shippingService->calculateCost(
                $address->city_id,
                $request->courier,
                $request->service,
                $cart->total_weight
            );

            $subtotal = $cart->subtotal;
            $discountAmount = 0;

            // Validate and calculate voucher discount
            if ($request->voucher_code) {
                $voucher = Voucher::where('code', $request->voucher_code)->first();

                if (!$voucher || !$voucher->canBeUsedBy($user->id, $subtotal)) {
                    throw ValidationException::withMessages([
                        'voucher_code' => ['Voucher tidak valid atau tidak dapat digunakan']
                    ]);
                }

                $discountAmount = $voucher->calculateDiscount($subtotal, $shippingCost);
            }

            $finalShippingCost = max(0, $shippingCost - ($voucher && $voucher->type === 'free_shipping' ? $discountAmount : 0));
            $finalSubtotal = $subtotal - ($voucher && $voucher->type !== 'free_shipping' ? $discountAmount : 0);
            $total = $finalSubtotal + $finalShippingCost;

            return response()->json([
                'valid' => true,
                'data' => [
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'discount_amount' => $discountAmount,
                    'final_shipping_cost' => $finalShippingCost,
                    'total' => $total,
                    'address' => [
                        'name' => $address->name,
                        'phone' => $address->phone,
                        'full_address' => $address->full_address,
                    ],
                    'shipping' => [
                        'courier' => $request->courier,
                        'service' => $request->service,
                        'cost' => $shippingCost,
                        'estimated_delivery' => '2-3 hari kerja',
                    ]
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'valid' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Validation failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Process checkout and create order
     */
    public function process(Request $request): JsonResponse
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
            'courier' => 'required|string|in:jne,pos,tiki,jnt,sicepat,anteraja',
            'service' => 'required|string',
            'voucher_code' => 'nullable|string|exists:vouchers,code',
            'notes' => 'nullable|string|max:500',
            'payment_gateway' => 'required|string|in:midtrans,xendit',
            'idempotency_key' => 'required|string|unique:orders,idempotency_key',
        ]);

        try {
            $user = Auth::user();
            $cart = $user->cart()->with('items.product', 'items.variant')->first();

            if (!$cart || $cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => ['Keranjang kosong']
                ]);
            }

            // Get address
            $address = UserAddress::where('id', $request->address_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // Create order using service
            $order = $this->orderService->createOrder([
                'cart' => $cart,
                'user' => $user,
                'address' => $address,
                'courier' => $request->courier,
                'service' => $request->service,
                'voucher_code' => $request->voucher_code,
                'notes' => $request->notes,
                'payment_gateway' => $request->payment_gateway,
                'idempotency_key' => $request->idempotency_key,
            ]);

            return response()->json([
                'message' => 'Order berhasil dibuat',
                'data' => [
                    'order' => new OrderResource($order->load(['items', 'vouchers', 'shipment'])),
                    'payment' => [
                        'method' => $request->payment_gateway,
                        'redirect_url' => $order->payment_data['redirect_url'] ?? null,
                        'snap_token' => $order->payment_data['token'] ?? null,
                    ]
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Checkout failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
