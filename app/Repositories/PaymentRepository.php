<?php

namespace App\Repositories;

use App\Interfaces\PaymentInterface;
use App\Models\Payment;

class PaymentRepository extends BaseRepository implements PaymentInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = [])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Payment::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get();
    }

    public function getById(int $id, array $fields = ['*'], array $relations = []): ?Payment
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Payment::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first();
    }

    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?Payment
    {
        $fields = $this->withRequiredColumns($fields);

        $query = Payment::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first();
    }

    public function create(array $data): Payment
    {
        return Payment::query()->create($data);
    }

    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);

        return $payment->fresh();
    }

    public function delete(Payment $payment): void
    {
        $payment->delete();
    }
}
