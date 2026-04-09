<?php

namespace App\Interfaces;

use App\Models\Holiday;

interface HolidayInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Holiday;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Holiday;
    public function create(array $data): Holiday;
    public function update(Holiday $holiday, array $data): Holiday;
    public function delete(Holiday $holiday): void;
}
