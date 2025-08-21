<?php

namespace App\Services\Payment;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = config('services.midtrans.is_sanitized', true);
        Config::$is3ds = config('services.midtrans.is_3ds', true);
    }

    /**
     * Create Midtrans transaction
     */
    public function createTransaction(Order $order): array
    {
        try {
            $params = [
                'transaction_details' => [
                    'order_id' => $order->code,
                    'gross_amount' => (int) $order->total,
                ],
                'customer_details' => [
                    'first_name' => $order->customer_name,
                    'email' => $order->customer_email,
                    'phone' => $order->customer_phone,
                    'shipping_address' => [
                        'address' => $order->shipping_address,
                    ],
                ],
                'item_details' => $this->buildItemDetails($order),
                'callbacks' => [
                    'finish' => url("/orders/{$order->code}/success"),
                    'unfinish' => url("/orders/{$order->code}/pending"),
                    'error' => url("/orders/{$order->code}/failed"),
                ],
                'expiry' => [
                    'start_time' => date('Y-m-d H:i:s O'),
                    'unit' => 'minutes',
                    'duration' => 60, // 1 hour expiry
                ],
            ];

            $snapToken = Snap::getSnapToken($params);
            $redirectUrl = Snap::createTransaction($params)->redirect_url;

            return [
                'token' => $snapToken,
                'redirect_url' => $redirectUrl,
                'order_id' => $order->code,
            ];
        } catch (\Exception $e) {
            throw new \Exception("Midtrans transaction creation failed: " . $e->getMessage());
        }
    }

    /**
     * Handle Midtrans notification/webhook
     */
    public function handleNotification(array $payload): array
    {
        try {
            $notification = new Notification();

            $orderCode = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status ?? 'accept';
            $paymentType = $notification->payment_type;

            $order = Order::where('code', $orderCode)->first();

            if (!$order) {
                throw new \Exception("Order not found: {$orderCode}");
            }

            // Verify signature
            $signature = hash(
                'sha512',
                $orderCode .
                    $notification->status_code .
                    $notification->gross_amount .
                    Config::$serverKey
            );

            if ($signature !== $notification->signature_key) {
                throw new \Exception("Invalid signature");
            }

            $paymentStatus = $this->mapTransactionStatus($transactionStatus, $fraudStatus);

            // Update order status
            if ($paymentStatus === 'paid' && $order->payment_status !== 'paid') {
                $order->markAsPaid($notification->transaction_id);
            } elseif (in_array($paymentStatus, ['failed', 'expired'])) {
                $order->update(['payment_status' => $paymentStatus]);

                // Release reserved inventory
                foreach ($order->items as $item) {
                    $inventory = $item->product->inventories()
                        ->where('product_variant_id', $item->product_variant_id)
                        ->first();

                    if ($inventory) {
                        $inventory->release($item->quantity);
                    }
                }
            }

            return [
                'status' => 'success',
                'order_code' => $orderCode,
                'payment_status' => $paymentStatus,
            ];
        } catch (\Exception $e) {
            throw new \Exception("Webhook processing failed: " . $e->getMessage());
        }
    }

    /**
     * Verify payment signature
     */
    public function verifySignature(array $payload): bool
    {
        $signature = hash(
            'sha512',
            $payload['order_id'] .
                $payload['status_code'] .
                $payload['gross_amount'] .
                Config::$serverKey
        );

        return $signature === ($payload['signature_key'] ?? '');
    }

    /**
     * Build item details for Midtrans
     */
    private function buildItemDetails(Order $order): array
    {
        $items = [];

        // Add order items
        foreach ($order->items as $item) {
            $items[] = [
                'id' => $item->product_id . '_' . ($item->product_variant_id ?? '0'),
                'price' => (int) $item->price,
                'quantity' => $item->quantity,
                'name' => $item->product_name . ($item->variant_name ? " - {$item->variant_name}" : ''),
            ];
        }

        // Add shipping cost
        if ($order->shipping_cost > 0) {
            $items[] = [
                'id' => 'shipping',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ];
        }

        // Subtract discount
        if ($order->discount_amount > 0) {
            $items[] = [
                'id' => 'discount',
                'price' => -(int) $order->discount_amount,
                'quantity' => 1,
                'name' => 'Diskon',
            ];
        }

        return $items;
    }

    /**
     * Map Midtrans transaction status to our system
     */
    private function mapTransactionStatus(string $transactionStatus, string $fraudStatus): string
    {
        switch ($transactionStatus) {
            case 'capture':
                return $fraudStatus === 'accept' ? 'paid' : 'pending';
            case 'settlement':
                return 'paid';
            case 'pending':
                return 'pending';
            case 'deny':
            case 'cancel':
            case 'failure':
                return 'failed';
            case 'expire':
                return 'expired';
            default:
                return 'pending';
        }
    }
}
