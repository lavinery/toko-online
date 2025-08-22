<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\VoucherResource;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    /**
     * Validate voucher code
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'cart_total' => 'required|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
        ]);

        try {
            $voucher = Voucher::where('code', $request->code)->first();

            if (!$voucher) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Kode voucher tidak ditemukan'
                ], 404);
            }

            $userId = Auth::id();
            $cartTotal = $request->cart_total;
            $shippingCost = $request->shipping_cost ?? 0;

            if (!$voucher->canBeUsedBy($userId, $cartTotal)) {
                $reasons = [];

                if (!$voucher->isValid()) {
                    if (!$voucher->is_active) {
                        $reasons[] = 'Voucher tidak aktif';
                    } elseif (now()->lt($voucher->starts_at)) {
                        $reasons[] = 'Voucher belum berlaku';
                    } elseif (now()->gt($voucher->expires_at)) {
                        $reasons[] = 'Voucher sudah kadaluarsa';
                    } elseif ($voucher->usage_limit && $voucher->used_count >= $voucher->usage_limit) {
                        $reasons[] = 'Voucher sudah habis digunakan';
                    }
                }

                if ($voucher->minimum_amount && $cartTotal < $voucher->minimum_amount) {
                    $reasons[] = "Minimum belanja Rp " . number_format($voucher->minimum_amount, 0, ',', '.');
                }

                return response()->json([
                    'valid' => false,
                    'message' => implode(', ', $reasons) ?: 'Voucher tidak dapat digunakan'
                ], 422);
            }

            $discountAmount = $voucher->calculateDiscount($cartTotal, $shippingCost);

            return response()->json([
                'valid' => true,
                'data' => [
                    'voucher' => new VoucherResource($voucher),
                    'discount_amount' => $discountAmount,
                    'discount_type' => $voucher->type,
                    'final_shipping_cost' => $voucher->type === 'free_shipping'
                        ? max(0, $shippingCost - $discountAmount)
                        : $shippingCost,
                    'final_total' => $cartTotal - ($voucher->type !== 'free_shipping' ? $discountAmount : 0) +
                        ($voucher->type === 'free_shipping' ? max(0, $shippingCost - $discountAmount) : $shippingCost)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Gagal memvalidasi voucher',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get available vouchers for user
     */
    public function available(Request $request): JsonResponse
    {
        try {
            $vouchers = Voucher::active()
                ->where(function ($query) {
                    $query->whereNull('usage_limit')
                        ->orWhereRaw('used_count < usage_limit');
                })
                ->orderBy('created_at', 'desc')
                ->get();

            $userId = Auth::id();
            $cartTotal = $request->get('cart_total', 0);

            $availableVouchers = $vouchers->filter(function ($voucher) use ($userId, $cartTotal) {
                return $voucher->canBeUsedBy($userId, $cartTotal);
            });

            return response()->json([
                'data' => VoucherResource::collection($availableVouchers)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch available vouchers',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
