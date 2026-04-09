<?php

namespace App\Interfaces;

use App\Models\Device;

interface DeviceInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Device;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Device;
    public function create(array $data): Device;
    public function update(Device $device, array $data): Device;
    public function delete(Device $device): void;
}
