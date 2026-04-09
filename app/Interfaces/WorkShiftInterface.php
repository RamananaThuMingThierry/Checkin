<?php

namespace App\Interfaces;

use App\Models\WorkShift;

interface WorkShiftInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?WorkShift;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?WorkShift;
    public function create(array $data): WorkShift;
    public function update(WorkShift $workShift, array $data): WorkShift;
    public function delete(WorkShift $workShift): void;
}
