<?php

namespace App\Interfaces;

use App\Models\Payment;

interface PaymentInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Payment;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Payment;
    public function create(array $data): Payment;
    public function update(Payment $payment, array $data): Payment;
    public function delete(Payment $payment): void;
}
