<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*']): Collection;
    
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;
    
    public function find(int $id, array $columns = ['*']): ?Model;
    
    public function findOrFail(int $id, array $columns = ['*']): Model;
    
    public function findBy(string $field, $value, array $columns = ['*']): ?Model;
    
    public function findWhere(array $where, array $columns = ['*']): Collection;
    
    public function create(array $attributes): Model;
    
    public function update(Model $model, array $attributes): bool;
    
    public function delete(Model $model): bool;
    
    public function deleteById(int $id): bool;
    
    public function count(): int;
    
    public function with(array $relations): self;
    
    public function whereHas(string $relation, callable $callback = null): self;
}