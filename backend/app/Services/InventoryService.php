<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function createInventory(int $productId, ?int $variantId, int $quantity, int $minimumStock = 0): Inventory
    {
        $inventory = Inventory::create([
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'quantity' => $quantity,
            'reserved_quantity' => 0,
            'minimum_stock' => $minimumStock,
        ]);

        if ($quantity > 0) {
            $this->logMovement($inventory, 'in', $quantity, 0, 'initial_stock');
        }

        return $inventory;
    }

    public function updateInventory(int $productId, ?int $variantId, int $newQuantity, string $reason = 'manual_adjustment'): Inventory
    {
        $inventory = $this->getInventory($productId, $variantId);
        
        if (!$inventory) {
            return $this->createInventory($productId, $variantId, $newQuantity);
        }

        return $this->adjustInventory($inventory, $newQuantity - $inventory->quantity, $reason);
    }

    public function adjustInventory(Inventory $inventory, int $adjustment, string $reason = 'manual_adjustment', ?int $referenceId = null): Inventory
    {
        return DB::transaction(function () use ($inventory, $adjustment, $reason, $referenceId) {
            $previousQuantity = $inventory->quantity;
            $inventory->increment('quantity', $adjustment);

            $this->logMovement(
                $inventory,
                $adjustment > 0 ? 'in' : 'out',
                abs($adjustment),
                $previousQuantity,
                $reason,
                $referenceId
            );

            return $inventory->fresh();
        });
    }

    public function reserveStock(int $productId, ?int $variantId, int $quantity): bool
    {
        $inventory = $this->getInventory($productId, $variantId);
        
        if (!$inventory || !$inventory->canFulfill($quantity)) {
            return false;
        }

        $inventory->increment('reserved_quantity', $quantity);
        return true;
    }

    public function releaseReservedStock(int $productId, ?int $variantId, int $quantity): bool
    {
        $inventory = $this->getInventory($productId, $variantId);
        
        if (!$inventory) {
            return false;
        }

        $releaseQuantity = min($quantity, $inventory->reserved_quantity);
        $inventory->decrement('reserved_quantity', $releaseQuantity);
        
        return true;
    }

    public function confirmReservation(int $productId, ?int $variantId, int $quantity, string $reason = 'order_confirmed', ?int $referenceId = null): bool
    {
        $inventory = $this->getInventory($productId, $variantId);
        
        if (!$inventory) {
            return false;
        }

        return DB::transaction(function () use ($inventory, $quantity, $reason, $referenceId) {
            $actualQuantity = min($quantity, $inventory->reserved_quantity);
            
            $inventory->decrement('reserved_quantity', $actualQuantity);
            $inventory->decrement('quantity', $actualQuantity);

            $this->logMovement(
                $inventory,
                'out',
                $actualQuantity,
                $inventory->quantity + $actualQuantity,
                $reason,
                $referenceId
            );

            return true;
        });
    }

    public function checkAvailability(int $productId, ?int $variantId, int $quantity): bool
    {
        $inventory = $this->getInventory($productId, $variantId);
        return $inventory && $inventory->canFulfill($quantity);
    }

    public function getLowStockItems(int $threshold = null): Collection
    {
        $query = Inventory::with(['product', 'variant']);
        
        if ($threshold) {
            $query->where('quantity', '<=', $threshold);
        } else {
            $query->whereRaw('quantity <= minimum_stock');
        }

        return $query->get();
    }

    public function getInventoryMovements(int $productId, ?int $variantId = null): Collection
    {
        $inventory = $this->getInventory($productId, $variantId);
        
        if (!$inventory) {
            return collect();
        }

        return $inventory->movements()
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function bulkUpdateInventory(array $updates): array
    {
        $results = [];

        DB::transaction(function () use ($updates, &$results) {
            foreach ($updates as $update) {
                try {
                    $inventory = $this->updateInventory(
                        $update['product_id'],
                        $update['variant_id'] ?? null,
                        $update['quantity'],
                        $update['reason'] ?? 'bulk_update'
                    );
                    
                    $results[] = [
                        'success' => true,
                        'product_id' => $update['product_id'],
                        'variant_id' => $update['variant_id'] ?? null,
                        'inventory' => $inventory
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'success' => false,
                        'product_id' => $update['product_id'],
                        'variant_id' => $update['variant_id'] ?? null,
                        'error' => $e->getMessage()
                    ];
                }
            }
        });

        return $results;
    }

    private function getInventory(int $productId, ?int $variantId): ?Inventory
    {
        return Inventory::where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->first();
    }

    private function logMovement(Inventory $inventory, string $type, int $quantity, int $previousQuantity, string $reason, ?int $referenceId = null): void
    {
        $inventory->movements()->create([
            'type' => $type,
            'quantity' => $type === 'out' ? -$quantity : $quantity,
            'previous_quantity' => $previousQuantity,
            'reason' => $reason,
            'reference_type' => $referenceId ? 'order' : null,
            'reference_id' => $referenceId,
            'created_by' => auth()->id(),
        ]);
    }
}