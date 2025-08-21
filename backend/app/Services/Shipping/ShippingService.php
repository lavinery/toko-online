<?php

// app/Services/Shipping/ShippingService.php
namespace App\Services\Shipping;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ShippingService
{
    protected $apiKey;
    protected $originCityId;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.key');
        $this->originCityId = config('services.rajaongkir.origin_city_id', 501); // Default Jakarta Selatan
    }

    /**
     * Get provinces from RajaOngkir
     */
    public function getProvinces(): array
    {
        return Cache::remember('rajaongkir_provinces', 86400, function () {
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey
                ])->get('https://api.rajaongkir.com/starter/province');

                if ($response->successful()) {
                    return $response->json()['rajaongkir']['results'] ?? [];
                }

                return [];
            } catch (\Exception $e) {
                return [];
            }
        });
    }

    /**
     * Get cities by province ID
     */
    public function getCities(int $provinceId = null): array
    {
        $cacheKey = $provinceId ? "rajaongkir_cities_{$provinceId}" : 'rajaongkir_cities_all';

        return Cache::remember($cacheKey, 86400, function () use ($provinceId) {
            try {
                $url = 'https://api.rajaongkir.com/starter/city';
                $params = $provinceId ? ['province' => $provinceId] : [];

                $response = Http::withHeaders([
                    'key' => $this->apiKey
                ])->get($url, $params);

                if ($response->successful()) {
                    return $response->json()['rajaongkir']['results'] ?? [];
                }

                return [];
            } catch (\Exception $e) {
                return [];
            }
        });
    }

    /**
     * Calculate shipping cost
     */
    public function calculateCost(int $destinationCityId, string $courier, string $service, int $weight): float
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
                'content-type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post('https://api.rajaongkir.com/starter/cost', [
                'origin' => $this->originCityId,
                'destination' => $destinationCityId,
                'weight' => $weight,
                'courier' => $courier
            ]);

            if ($response->successful()) {
                $results = $response->json()['rajaongkir']['results'][0]['costs'] ?? [];

                // Find the specific service
                foreach ($results as $cost) {
                    if (strtolower($cost['service']) === strtolower($service)) {
                        return (float) $cost['cost'][0]['value'];
                    }
                }
            }

            // Fallback to default shipping cost if API fails
            return $this->getDefaultShippingCost($weight);
        } catch (\Exception $e) {
            return $this->getDefaultShippingCost($weight);
        }
    }

    /**
     * Get available shipping services
     */
    public function getAvailableServices(int $destinationCityId, int $weight): array
    {
        $cacheKey = "shipping_services_{$destinationCityId}_{$weight}";

        return Cache::remember($cacheKey, 3600, function () use ($destinationCityId, $weight) {
            $services = [];
            $couriers = ['jne', 'pos', 'tiki'];

            foreach ($couriers as $courier) {
                try {
                    $response = Http::withHeaders([
                        'key' => $this->apiKey,
                        'content-type' => 'application/x-www-form-urlencoded'
                    ])->asForm()->post('https://api.rajaongkir.com/starter/cost', [
                        'origin' => $this->originCityId,
                        'destination' => $destinationCityId,
                        'weight' => $weight,
                        'courier' => $courier
                    ]);

                    if ($response->successful()) {
                        $results = $response->json()['rajaongkir']['results'][0] ?? null;

                        if ($results) {
                            foreach ($results['costs'] as $cost) {
                                $services[] = [
                                    'courier' => strtoupper($courier),
                                    'service' => $cost['service'],
                                    'description' => $cost['description'],
                                    'cost' => $cost['cost'][0]['value'],
                                    'etd' => $cost['cost'][0]['etd'],
                                ];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Continue with other couriers
                    continue;
                }
            }

            return $services;
        });
    }

    /**
     * Get default shipping cost if API fails
     */
    private function getDefaultShippingCost(int $weight): float
    {
        // Simple weight-based calculation
        $baseRate = 15000; // Base rate for first kg
        $additionalRate = 5000; // Per additional kg

        $kg = ceil($weight / 1000);

        return $baseRate + (($kg - 1) * $additionalRate);
    }
}
