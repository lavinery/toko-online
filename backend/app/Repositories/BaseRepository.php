<?php

namespace App\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;
    protected array $with = [];
    protected array $whereHas = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->buildQuery()->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->buildQuery()->paginate($perPage, $columns);
    }

    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->buildQuery()->find($id, $columns);
    }

    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->buildQuery()->findOrFail($id, $columns);
    }

    public function findBy(string $field, $value, array $columns = ['*']): ?Model
    {
        return $this->buildQuery()->where($field, $value)->first($columns);
    }

    public function findWhere(array $where, array $columns = ['*']): Collection
    {
        $query = $this->buildQuery();
        
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
        
        return $query->get($columns);
    }

    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    public function update(Model $model, array $attributes): bool
    {
        return $model->update($attributes);
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    public function deleteById(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }

    public function count(): int
    {
        return $this->buildQuery()->count();
    }

    public function with(array $relations): self
    {
        $this->with = array_merge($this->with, $relations);
        return $this;
    }

    public function whereHas(string $relation, callable $callback = null): self
    {
        $this->whereHas[] = [$relation, $callback];
        return $this;
    }

    protected function buildQuery()
    {
        $query = $this->model->newQuery();

        if (!empty($this->with)) {
            $query->with($this->with);
        }

        foreach ($this->whereHas as [$relation, $callback]) {
            $query->whereHas($relation, $callback);
        }

        return $query;
    }

    protected function resetQuery(): void
    {
        $this->with = [];
        $this->whereHas = [];
    }
}