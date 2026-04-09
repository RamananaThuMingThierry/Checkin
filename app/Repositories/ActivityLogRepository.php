<?php

namespace App\Repositories;

use App\Interfaces\ActivityLogInterface;
use App\Models\ActivityLog;

class ActivityLogRepository extends BaseRepository implements ActivityLogInterface
{

    public function getAll(string|array $keys, mixed $value, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false, ?int $paginate = null, array $orderBy = ['id' => 'desc'])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = ActivityLog::query();
        $query = $this->applyTrashed($query, $withTrashed, $onlyTrashed);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get($fields);
    }

    public function getById(int $id, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false) : ActivityLog|null
    {
        $fields = $this->withRequiredColumns($fields);

        $query = ActivityLog::query();
        $query = $this->applyTrashed($query, $withTrashed, $onlyTrashed);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first($fields);
    }

    public function getByKeys(string|array $keys, mixed $value, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false): ActivityLog|null
    {
        $fields = $this->withRequiredColumns($fields);

        $query = ActivityLog::query();
        $query = $this->applyTrashed($query, $withTrashed, $onlyTrashed);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first($fields);
    }

    public function create(array $data): ActivityLog
    {
        return ActivityLog::create($data);
    }

    public function delete(int $id): bool
    {
        $activityLog = $this->getById($id, ['*'], [], true);

        if (!$activityLog) {
            throw new \Exception('Activity log not found');
        }

        return $activityLog->delete();
    }

    public function restore(int $id): bool
    {
        $activityLog = $this->getById($id, ['*'], [], true, true);

        if (!$activityLog) {
            throw new \Exception('Activity log not found');
        }

        return $activityLog->restore();
    }

    public function forceDelete(int $id): bool
    {
        $activityLog = $this->getById($id, ['*'], [], true, true);

        if (!$activityLog) {
            throw new \Exception('Activity log not found');
        }

        return $activityLog->forceDelete();
    }
}
