<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    /**
     * Get user's orders
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $orders = Order::where('user_id', $user->id)
                ->with(['items.product', 'vouchers', 'shipment'])
                ->when($request->status, function ($query, $status) {
                    if ($status === 'pending') {
                        $query->where('payment_status', 'pending');
                    } elseif ($status === 'paid') {
                        $query->where('payment_status', 'paid');
                    } elseif ($status === 'shipped') {
                        $query->where('shipping_status', 'shipped');
                    } elseif ($status === 'delivered') {
                        $query->where('shipping_status', 'delivered');
                    }
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'data' => OrderResource::collection($orders->items()),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch orders',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get order details
     */
    public function show(Order $order): JsonResponse
    {
        try {
            // Ensure order belongs to authenticated user
            if ($order->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Order not found'
                ], 404);
            }

            $order->load(['items.product.images', 'vouchers.voucher', 'shipment']);

            return response()->json([
                'data' => new OrderResource($order)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch order',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Cancel order
     */
    public function cancel(Order $order): JsonResponse
    {
        try {
            // Ensure order belongs to authenticated user
            if ($order->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Order not found'
                ], 404);
            }

            if (!$order->canBeCancelled()) {
                return response()->json([
                    'message' => 'Order cannot be cancelled'
                ], 422);
            }

            $order->update([
                'payment_status' => 'cancelled',
                'shipping_status' => 'cancelled'
            ]);

            // Release reserved inventory
            foreach ($order->items as $item) {
                $inventory = $item->product->inventories()
                    ->where('product_variant_id', $item->product_variant_id)
                    ->first();

                if ($inventory) {
                    $inventory->release($item->quantity);
                }
            }

            return response()->json([
                'message' => 'Order cancelled successfully',
                'data' => new OrderResource($order->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel order',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Confirm delivery
     */
    public function confirmDelivery(Order $order): JsonResponse
    {
        try {
            // Ensure order belongs to authenticated user
            if ($order->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Order not found'
                ], 404);
            }

            if ($order->shipping_status !== 'shipped') {
                return response()->json([
                    'message' => 'Order is not in shipped status'
                ], 422);
            }

            $order->update([
                'shipping_status' => 'delivered',
                'delivered_at' => now()
            ]);

            if ($order->shipment) {
                $order->shipment->markAsDelivered();
            }

            return response()->json([
                'message' => 'Delivery confirmed successfully',
                'data' => new OrderResource($order->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to confirm delivery',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Download invoice
     */
    public function downloadInvoice(Order $order): JsonResponse
    {
        try {
            // Ensure order belongs to authenticated user
            if ($order->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Order not found'
                ], 404);
            }

            if (!$order->isPaid()) {
                return response()->json([
                    'message' => 'Invoice not available for unpaid orders'
                ], 422);
            }

            // Generate invoice URL or return invoice data
            $invoiceUrl = url("/api/v1/orders/{$order->code}/invoice/download");

            return response()->json([
                'data' => [
                    'invoice_url' => $invoiceUrl,
                    'order_code' => $order->code,
                    'download_expires_at' => now()->addHours(24)->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate invoice',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Track shipment
     */
    public function trackShipment(Order $order): JsonResponse
    {
        try {
            // Ensure order belongs to authenticated user
            if ($order->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Order not found'
                ], 404);
            }

            if (!$order->shipment || !$order->shipment->tracking_number) {
                return response()->json([
                    'message' => 'Tracking information not available'
                ], 404);
            }

            $shipment = $order->shipment;

            return response()->json([
                'data' => [
                    'tracking_number' => $shipment->tracking_number,
                    'courier' => $shipment->courier,
                    'service' => $shipment->service,
                    'status' => $shipment->status,
                    'shipped_at' => $shipment->shipped_at,
                    'estimated_delivery' => $shipment->estimated_delivery,
                    'delivered_at' => $shipment->delivered_at,
                    'tracking_url' => $this->getTrackingUrl($shipment->courier, $shipment->tracking_number)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get tracking information',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get tracking URL for courier
     */
    private function getTrackingUrl(string $courier, string $trackingNumber): ?string
    {
        $urls = [
            'jne' => "https://www.jne.co.id/id/tracking/trace/{$trackingNumber}",
            'pos' => "https://www.posindonesia.co.id/id/tracking/{$trackingNumber}",
            'tiki' => "https://www.tiki.id/id/tracking?awb={$trackingNumber}",
            'jnt' => "https://www.jet.co.id/track/{$trackingNumber}",
            'sicepat' => "https://www.sicepat.com/checkAwb/{$trackingNumber}",
            'anteraja' => "https://www.anteraja.id/tracking/{$trackingNumber}",
        ];

        return $urls[strtolower($courier)] ?? null;
    }
}
