<?php

namespace App\Interfaces;

use App\Models\Branch;

interface BranchInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Branch;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Branch;
    public function create(array $data): Branch;
    public function update(Branch $branch, array $data): Branch;
    public function delete(Branch $branch): void;
}
