<?php

namespace App\Services;

use App\Interfaces\ActivityLogInterface;

class ActivityLogService
{
    public function __construct(private readonly ActivityLogInterface $activityLogRepository){}

    public function getAllActivityLogs(string|array $keys, mixed $value, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false, ?int $paginate = null, array $orderBy = ['id' => 'desc'])
    {
        return $this->activityLogRepository->getAll($keys, $value, $fields, $relations, $withTrashed, $onlyTrashed, $paginate, $orderBy);
    }

    public function getActivityLogById(int $id, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false)
    {
        return $this->activityLogRepository->getById($id, $fields, $relations, $withTrashed, $onlyTrashed);
    }

    public function getActivityLogByKeys(string|array $keys, mixed $value, array $fields = ['*'], array $relations = [], bool $withTrashed = false, bool $onlyTrashed = false)
    {
        return $this->activityLogRepository->getByKeys($keys, $value, $fields, $relations, $withTrashed, $onlyTrashed);
    }

    public function createActivityLog(array $data)
    {
        return $this->activityLogRepository->create($data);
    }

    public function deleteActivityLog(int $id)
    {
        return $this->activityLogRepository->delete($id);
    }

    public function restoreActivityLog(int $id)
    {
        return $this->activityLogRepository->restore($id);
    }

    public function forceDeleteActivityLog(int $id)
    {
        return $this->activityLogRepository->forceDelete($id);
    }
}
