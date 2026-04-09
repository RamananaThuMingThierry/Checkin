<?php

namespace App\Interfaces;

use App\Models\Setting;

interface SettingInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Setting;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Setting;
    public function create(array $data): Setting;
    public function update(Setting $setting, array $data): Setting;
    public function delete(Setting $setting): void;
}
