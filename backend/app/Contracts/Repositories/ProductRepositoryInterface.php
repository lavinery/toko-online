<?php

namespace App\Contracts\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug): ?Product;
    
    public function findBySku(string $sku): ?Product;
    
    public function getFeatured(int $limit = 10): Collection;
    
    public function getActive(): Collection;
    
    public function getInStock(): Collection;
    
    public function searchByName(string $query): Collection;
    
    public function getByCategory(int $categoryId): Collection;
    
    public function getWithLowStock(int $threshold = 10): Collection;
    
    public function filterProducts(array $filters): LengthAwarePaginator;
}