<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->buildQuery()->where('slug', $slug)->first();
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->buildQuery()->where('sku', $sku)->first();
    }

    public function getFeatured(int $limit = 10): Collection
    {
        return $this->buildQuery()
            ->where('is_featured', true)
            ->where('status', 'active')
            ->limit($limit)
            ->get();
    }

    public function getActive(): Collection
    {
        return $this->buildQuery()->where('status', 'active')->get();
    }

    public function getInStock(): Collection
    {
        return $this->buildQuery()
            ->whereHas('inventories', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->get();
    }

    public function searchByName(string $query): Collection
    {
        return $this->buildQuery()
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orWhere('sku', 'LIKE', "%{$query}%")
            ->get();
    }

    public function getByCategory(int $categoryId): Collection
    {
        return $this->buildQuery()
            ->whereHas('categories', function ($query) use ($categoryId) {
                $query->where('categories.id', $categoryId);
            })
            ->get();
    }

    public function getWithLowStock(int $threshold = 10): Collection
    {
        return $this->buildQuery()
            ->whereHas('inventories', function ($query) use ($threshold) {
                $query->where('quantity', '<=', $threshold);
            })
            ->get();
    }

    public function filterProducts(array $filters): LengthAwarePaginator
    {
        $query = $this->buildQuery()->where('status', 'active');

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        // Category filter
        if (!empty($filters['category'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('slug', $filters['category'])
                  ->orWhere('id', $filters['category']);
            });
        }

        // Price range filter
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Featured filter
        if (!empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        // Sorting
        $sortBy = $filters['sort'] ?? 'created_at';
        $sortOrder = $filters['order'] ?? 'desc';

        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'popular':
                $query->withCount('orderItems')
                      ->orderBy('order_items_count', 'desc');
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = min($filters['per_page'] ?? 12, 100);
        
        return $query->paginate($perPage);
    }
}