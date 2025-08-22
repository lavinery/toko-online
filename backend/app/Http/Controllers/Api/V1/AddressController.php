<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    /**
     * Get user addresses
     */
    public function index(): JsonResponse
    {
        try {
            $addresses = Auth::user()->addresses()
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => AddressResource::collection($addresses)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch addresses',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Store new address
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'label' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'province' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'subdistrict' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'province_id' => 'required|integer',
            'city_id' => 'required|integer',
            'subdistrict_id' => 'nullable|integer',
            'is_default' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // If this is set as default, remove default from other addresses
            if ($request->is_default) {
                $user->addresses()->update(['is_default' => false]);
            }

            // If this is the first address, make it default
            $isFirstAddress = $user->addresses()->count() === 0;

            $address = $user->addresses()->create([
                'label' => $request->label,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'province' => $request->province,
                'city' => $request->city,
                'subdistrict' => $request->subdistrict,
                'postal_code' => $request->postal_code,
                'province_id' => $request->province_id,
                'city_id' => $request->city_id,
                'subdistrict_id' => $request->subdistrict_id,
                'is_default' => $request->is_default || $isFirstAddress,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Address created successfully',
                'data' => new AddressResource($address)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create address',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Show address
     */
    public function show(UserAddress $address): JsonResponse
    {
        try {
            // Ensure address belongs to authenticated user
            if ($address->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Address not found'
                ], 404);
            }

            return response()->json([
                'data' => new AddressResource($address)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch address',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update address
     */
    public function update(UserAddress $address, Request $request): JsonResponse
    {
        // Ensure address belongs to authenticated user
        if ($address->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Address not found'
            ], 404);
        }

        $request->validate([
            'label' => 'sometimes|required|string|max:50',
            'name' => 'sometimes|required|string|max:100',
            'phone' => 'sometimes|required|string|max:20',
            'address' => 'sometimes|required|string|max:500',
            'province' => 'sometimes|required|string|max:100',
            'city' => 'sometimes|required|string|max:100',
            'subdistrict' => 'sometimes|required|string|max:100',
            'postal_code' => 'sometimes|required|string|max:10',
            'province_id' => 'sometimes|required|integer',
            'city_id' => 'sometimes|required|integer',
            'subdistrict_id' => 'nullable|integer',
            'is_default' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // If this is set as default, remove default from other addresses
            if ($request->is_default && !$address->is_default) {
                Auth::user()->addresses()->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            $address->update($request->only([
                'label',
                'name',
                'phone',
                'address',
                'province',
                'city',
                'subdistrict',
                'postal_code',
                'province_id',
                'city_id',
                'subdistrict_id',
                'is_default'
            ]));

            DB::commit();

            return response()->json([
                'message' => 'Address updated successfully',
                'data' => new AddressResource($address)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update address',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete address
     */
    public function destroy(UserAddress $address): JsonResponse
    {
        try {
            // Ensure address belongs to authenticated user
            if ($address->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Address not found'
                ], 404);
            }

            $wasDefault = $address->is_default;
            $address->delete();

            // If deleted address was default, set another address as default
            if ($wasDefault) {
                $nextAddress = Auth::user()->addresses()->first();
                if ($nextAddress) {
                    $nextAddress->update(['is_default' => true]);
                }
            }

            return response()->json([
                'message' => 'Address deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete address',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Set address as default
     */
    public function setDefault(UserAddress $address): JsonResponse
    {
        try {
            // Ensure address belongs to authenticated user
            if ($address->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Address not found'
                ], 404);
            }

            DB::beginTransaction();

            // Remove default from all addresses
            Auth::user()->addresses()->update(['is_default' => false]);

            // Set this address as default
            $address->update(['is_default' => true]);

            DB::commit();

            return response()->json([
                'message' => 'Default address updated successfully',
                'data' => new AddressResource($address)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to set default address',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
