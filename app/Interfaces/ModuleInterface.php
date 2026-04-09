<?php

namespace App\Interfaces;

use App\Models\Module;

interface ModuleInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Module;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Module;
    public function create(array $data): Module;
    public function update(Module $module, array $data): Module;
    public function delete(Module $module): void;
}
