<?php

namespace App\Interfaces;

use App\Models\Subscription;

interface SubscriptionInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Subscription;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Subscription;
    public function create(array $data): Subscription;
    public function update(Subscription $subscription, array $data): Subscription;
    public function delete(Subscription $subscription): void;
}
