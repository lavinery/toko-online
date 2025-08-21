<?php

// app/Http/Controllers/Api/V1/CheckoutController.php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\{Cart, Order, OrderItem, OrderVoucher, Voucher, UserAddress, Inventory, Shipment};
use App\Services\Payment\MidtransService;
use App\Services\Shipping\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    protected $midtransService;
    protected $shippingService;

    public function __construct(MidtransService $midtransService, ShippingService $shippingService)
    {
        $this->midtransService = $midtransService;
        $this->shippingService = $shippingService;
    }

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
                $inventory = $item->getCurrentInventory();
                if (!$inventory || !$inventory->canFulfill($item->quantity)) {
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
                        'estimated_delivery' => '2-3 hari kerja', // From shipping service
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
            DB::beginTransaction();

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

            // Reserve inventory
            foreach ($cart->items as $item) {
                $inventory = $item->getCurrentInventory();
                if (!$inventory || !$inventory->canFulfill($item->quantity)) {
                    throw ValidationException::withMessages([
                        'stock' => ["Stok {$item->product->name} tidak mencukupi"]
                    ]);
                }
                $inventory->reserve($item->quantity);
            }

            // Calculate totals
            $subtotal = $cart->subtotal;
            $shippingCost = $this->shippingService->calculateCost(
                $address->city_id,
                $request->courier,
                $request->service,
                $cart->total_weight
            );

            $discountAmount = 0;
            $voucher = null;

            if ($request->voucher_code) {
                $voucher = Voucher::where('code', $request->voucher_code)->first();
                if ($voucher && $voucher->canBeUsedBy($user->id, $subtotal)) {
                    $discountAmount = $voucher->calculateDiscount($subtotal, $shippingCost);
                }
            }

            $finalShippingCost = $voucher && $voucher->type === 'free_shipping'
                ? max(0, $shippingCost - $discountAmount)
                : $shippingCost;

            $finalSubtotal = $voucher && $voucher->type !== 'free_shipping'
                ? $subtotal - $discountAmount
                : $subtotal;

            $total = $finalSubtotal + $finalShippingCost;

            // Create order
            $order = Order::create([
                'code' => Order::generateOrderCode(),
                'user_id' => $user->id,
                'customer_name' => $address->name,
                'customer_email' => $user->email,
                'customer_phone' => $address->phone,
                'subtotal' => $subtotal,
                'shipping_cost' => $finalShippingCost,
                'tax_amount' => 0, // Add tax calculation if needed
                'discount_amount' => $discountAmount,
                'total' => $total,
                'payment_status' => 'pending',
                'shipping_status' => 'pending',
                'payment_gateway' => $request->payment_gateway,
                'shipping_address' => $address->full_address,
                'notes' => $request->notes,
                'idempotency_key' => $request->idempotency_key,
            ]);

            // Create order items
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'variant_name' => $item->variant?->name,
                    'product_sku' => $item->variant?->sku ?? $item->product->sku,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ]);
            }

            // Create voucher usage record
            if ($voucher) {
                OrderVoucher::create([
                    'order_id' => $order->id,
                    'voucher_id' => $voucher->id,
                    'voucher_code' => $voucher->code,
                    'discount_amount' => $discountAmount,
                ]);
                $voucher->incrementUsage();
            }

            // Create shipment record
            Shipment::create([
                'order_id' => $order->id,
                'courier' => $request->courier,
                'service' => $request->service,
                'cost' => $finalShippingCost,
                'weight' => $cart->total_weight,
                'destination_address' => $address->full_address,
                'status' => 'pending',
            ]);

            // Create payment with gateway
            $paymentData = null;
            switch ($request->payment_gateway) {
                case 'midtrans':
                    $paymentData = $this->midtransService->createTransaction($order);
                    break;
                    // Add other payment gateways here
            }

            // Update order with payment data
            $order->update([
                'payment_reference' => $paymentData['token'] ?? null,
                'payment_data' => $paymentData,
            ]);

            // Clear cart after successful order creation
            $cart->items()->delete();

            DB::commit();

            return response()->json([
                'message' => 'Order berhasil dibuat',
                'data' => [
                    'order' => new OrderResource($order->load(['items', 'vouchers', 'shipment'])),
                    'payment' => [
                        'method' => $request->payment_gateway,
                        'redirect_url' => $paymentData['redirect_url'] ?? null,
                        'snap_token' => $paymentData['token'] ?? null,
                    ]
                ]
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            // Release reserved inventory on failure
            if (isset($cart)) {
                foreach ($cart->items as $item) {
                    $inventory = $item->getCurrentInventory();
                    if ($inventory) {
                        $inventory->release($item->quantity);
                    }
                }
            }

            return response()->json([
                'message' => 'Checkout failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
