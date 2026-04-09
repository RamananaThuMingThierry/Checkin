<?php

namespace App\Repositories;

use App\Interfaces\DeviceInterface;
use App\Models\Device;

class DeviceRepository extends BaseRepository implements DeviceInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = [])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Device::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get();
    }

    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Device
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Device::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first();
    }

    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Device
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Device::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first();
    }

    public function create(array $data): Device
    {
        return Device::query()->create($data);
    }

    public function update(Device $device, array $data): Device
    {
        $device->update($data);

        return $device->fresh();
    }

    public function delete(Device $device): void
    {
        $device->delete();
    }
}
