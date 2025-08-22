<?php

namespace App\Services;

use App\Models\{Order, OrderItem, OrderVoucher, Cart, Voucher, UserAddress, Shipment};
use App\Services\Payment\MidtransService;
use App\Services\Shipping\ShippingService;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private MidtransService $midtransService,
        private ShippingService $shippingService,
        private InventoryService $inventoryService
    ) {}

    /**
     * Create order from cart
     */
    public function createOrder(array $orderData): Order
    {
        return DB::transaction(function () use ($orderData) {
            $cart = $orderData['cart'];
            $user = $orderData['user'];
            $address = $orderData['address'];

            // Reserve inventory
            foreach ($cart->items as $item) {
                if (!$this->inventoryService->reserveStock(
                    $item->product_id,
                    $item->product_variant_id,
                    $item->quantity
                )) {
                    throw new \Exception("Insufficient stock for {$item->product->name}");
                }
            }

            // Calculate totals
            $subtotal = $cart->subtotal;
            $shippingCost = $this->shippingService->calculateCost(
                $address->city_id,
                $orderData['courier'],
                $orderData['service'],
                $cart->total_weight
            );

            $discountAmount = 0;
            $voucher = null;

            if (!empty($orderData['voucher_code'])) {
                $voucher = Voucher::where('code', $orderData['voucher_code'])->first();
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
                'tax_amount' => 0,
                'discount_amount' => $discountAmount,
                'total' => $total,
                'payment_status' => 'pending',
                'shipping_status' => 'pending',
                'payment_gateway' => $orderData['payment_gateway'],
                'shipping_address' => $address->full_address,
                'notes' => $orderData['notes'] ?? null,
                'idempotency_key' => $orderData['idempotency_key'],
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
                'courier' => $orderData['courier'],
                'service' => $orderData['service'],
                'cost' => $finalShippingCost,
                'weight' => $cart->total_weight,
                'destination_address' => $address->full_address,
                'status' => 'pending',
            ]);

            // Create payment
            $paymentData = $this->createPayment($order, $orderData['payment_gateway']);

            // Update order with payment data
            $order->update([
                'payment_reference' => $paymentData['token'] ?? null,
                'payment_data' => $paymentData,
            ]);

            // Clear cart
            $cart->items()->delete();

            return $order;
        });
    }

    /**
     * Create payment with gateway
     */
    private function createPayment(Order $order, string $gateway): array
    {
        switch ($gateway) {
            case 'midtrans':
                return $this->midtransService->createTransaction($order);

                // Add other payment gateways here
            default:
                throw new \Exception("Unsupported payment gateway: {$gateway}");
        }
    }

    /**
     * Cancel order
     */
    public function cancelOrder(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            if (!$order->canBeCancelled()) {
                throw new \Exception('Order cannot be cancelled');
            }

            // Release reserved inventory
            foreach ($order->items as $item) {
                $this->inventoryService->releaseReservedStock(
                    $item->product_id,
                    $item->product_variant_id,
                    $item->quantity
                );
            }

            // Update order status
            $order->update([
                'payment_status' => 'cancelled',
                'shipping_status' => 'cancelled'
            ]);

            return true;
        });
    }

    /**
     * Mark order as paid
     */
    public function markAsPaid(Order $order, ?string $paymentReference = null): bool
    {
        return DB::transaction(function () use ($order, $paymentReference) {
            $order->markAsPaid($paymentReference);

            // Confirm inventory reservations
            foreach ($order->items as $item) {
                $this->inventoryService->confirmReservation(
                    $item->product_id,
                    $item->product_variant_id,
                    $item->quantity,
                    'order_paid',
                    $order->id
                );
            }

            return true;
        });
    }

    /**
     * Update order shipping status
     */
    public function updateShippingStatus(Order $order, string $status, ?string $trackingNumber = null): bool
    {
        $order->update(['shipping_status' => $status]);

        if ($status === 'shipped' && $trackingNumber) {
            $order->markAsShipped($trackingNumber);
        }

        if ($status === 'delivered') {
            $order->update([
                'shipping_status' => 'delivered',
                'delivered_at' => now()
            ]);

            if ($order->shipment) {
                $order->shipment->markAsDelivered();
            }
        }

        return true;
    }
}