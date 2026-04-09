<?php

namespace App\Repositories;

use App\Interfaces\LeaveRequestInterface;
use App\Models\LeaveRequest;

class LeaveRequestRepository extends BaseRepository implements LeaveRequestInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = [])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = LeaveRequest::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get();
    }

    public function getById(int $id, array $fields = ['*'], array $relations = []): ?LeaveRequest
    {
        $fields = $this->withRequiredColumns($fields);

        $query = LeaveRequest::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first();
    }

    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?LeaveRequest
    {
        $fields = $this->withRequiredColumns($fields);

        $query = LeaveRequest::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first();
    }

    public function create(array $data): LeaveRequest
    {
        return LeaveRequest::query()->create($data);
    }

    public function update(LeaveRequest $leaveRequest, array $data): LeaveRequest
    {
        $leaveRequest->update($data);

        return $leaveRequest->fresh();
    }

    public function delete(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->delete();
    }
}
