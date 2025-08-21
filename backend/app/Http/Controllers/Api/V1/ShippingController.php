<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Shipping\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShippingController extends Controller
{
    protected $shippingService;

    public function __construct(ShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    /**
     * Get provinces
     */
    public function provinces(): JsonResponse
    {
        $provinces = $this->shippingService->getProvinces();

        return response()->json([
            'data' => $provinces
        ]);
    }

    /**
     * Get cities
     */
    public function cities(Request $request): JsonResponse
    {
        $request->validate([
            'province_id' => 'nullable|integer'
        ]);

        $cities = $this->shippingService->getCities($request->province_id);

        return response()->json([
            'data' => $cities
        ]);
    }

    /**
     * Calculate shipping cost
     */
    public function calculateCost(Request $request): JsonResponse
    {
        $request->validate([
            'destination_city_id' => 'required|integer',
            'weight' => 'required|integer|min:1',
            'courier' => 'nullable|string|in:jne,pos,tiki',
            'service' => 'nullable|string'
        ]);

        try {
            if ($request->courier && $request->service) {
                // Calculate specific service cost
                $cost = $this->shippingService->calculateCost(
                    $request->destination_city_id,
                    $request->courier,
                    $request->service,
                    $request->weight
                );

                return response()->json([
                    'data' => [
                        'courier' => strtoupper($request->courier),
                        'service' => $request->service,
                        'cost' => $cost,
                        'weight' => $request->weight,
                    ]
                ]);
            } else {
                // Get all available services
                $services = $this->shippingService->getAvailableServices(
                    $request->destination_city_id,
                    $request->weight
                );

                return response()->json([
                    'data' => $services
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to calculate shipping cost',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
