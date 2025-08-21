<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\{Order, PaymentLog, Webhook};
use App\Services\Payment\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Handle Midtrans webhook notification
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        try {
            // Log webhook for debugging
            Webhook::create([
                'source' => 'midtrans',
                'event_type' => $payload['transaction_status'] ?? 'unknown',
                'payload' => $payload,
                'status' => 'processing',
            ]);

            // Find order
            $orderCode = $payload['order_id'] ?? null;
            $order = Order::where('code', $orderCode)->first();

            if (!$order) {
                Log::warning("Midtrans webhook: Order not found", ['order_code' => $orderCode]);
                return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
            }

            // Log payment
            $paymentLog = PaymentLog::create([
                'order_id' => $order->id,
                'gateway' => 'midtrans',
                'event_type' => $payload['transaction_status'] ?? 'unknown',
                'raw_payload' => $payload,
                'signature' => $payload['signature_key'] ?? null,
                'is_verified' => false,
                'status' => 'processing',
            ]);

            // Verify signature
            if (!$this->midtransService->verifySignature($payload)) {
                $paymentLog->update([
                    'status' => 'failed',
                    'error_message' => 'Invalid signature',
                ]);

                Log::error("Midtrans webhook: Invalid signature", ['payload' => $payload]);
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
            }

            // Process payment
            $result = $this->midtransService->handleNotification($payload);

            $paymentLog->update([
                'is_verified' => true,
                'status' => 'success',
            ]);

            Log::info("Midtrans webhook processed successfully", $result);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            if (isset($paymentLog)) {
                $paymentLog->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            Log::error("Midtrans webhook processing failed", [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }
}
