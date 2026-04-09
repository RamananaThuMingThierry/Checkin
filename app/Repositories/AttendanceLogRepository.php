<?php

namespace App\Repositories;

use App\Interfaces\AttendanceLogInterface;
use App\Models\AttendanceLog;
use Illuminate\Support\Carbon;

class AttendanceLogRepository extends BaseRepository implements AttendanceLogInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = [])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = AttendanceLog::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get();
    }

    public function getById(int $id, array $fields = ['*'], array $relations = []): ?AttendanceLog
    {
        $fields = $this->withRequiredColumns($fields);

        $query = AttendanceLog::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first();
    }

    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?AttendanceLog
    {
        $fields = $this->withRequiredColumns($fields);

        $query = AttendanceLog::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first();
    }

    public function getDailyLogs(int $tenantId, string $date, array $fields = ['*'], array $relations = []): mixed
    {
        $fields = $this->withRequiredColumns($fields);
        $startOfDay = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        $endOfDay = $startOfDay->copy()->endOfDay();

        $query = AttendanceLog::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query->where('tenant_id', $tenantId);
        $query->whereBetween('scanned_at', [$startOfDay, $endOfDay]);
        $query->orderBy('scanned_at', 'asc');
        $query->orderBy('id', 'asc');

        return $query->get();
    }

    public function create(array $data): AttendanceLog
    {
        return AttendanceLog::query()->create($data);
    }

    public function update(AttendanceLog $attendanceLog, array $data): AttendanceLog
    {
        $attendanceLog->update($data);

        return $attendanceLog->fresh();
    }

    public function delete(AttendanceLog $attendanceLog): void
    {
        $attendanceLog->delete();
    }
}
