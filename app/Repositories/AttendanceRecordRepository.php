<?php

namespace App\Repositories;

use App\Interfaces\AttendanceRecordInterface;
use App\Models\AttendanceRecord;

class AttendanceRecordRepository extends BaseRepository implements AttendanceRecordInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = [])
    {
        $fields = $this->withRequiredColumns($fields);

        $query = AttendanceRecord::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);
        $query = $this->applyOrderBy($query, $orderBy);

        return $paginate ? $query->paginate($paginate, $fields) : $query->get();
    }

    public function getById(int $id, array $fields = ['*'], array $relations = []): ?AttendanceRecord
    {
        $fields = $this->withRequiredColumns($fields);

        $query = AttendanceRecord::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query->where('id', $id);

        return $query->first();
    }

    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?AttendanceRecord
    {
        $fields = $this->withRequiredColumns($fields);

        $query = AttendanceRecord::query()->select($fields);
        $query = $this->applyRelation($query, $relations);
        $query = $this->applyFilter($query, $keys, $value);

        return $query->first();
    }

    public function create(array $data): AttendanceRecord
    {
        return AttendanceRecord::query()->create($data);
    }

    public function update(AttendanceRecord $attendanceRecord, array $data): AttendanceRecord
    {
        $attendanceRecord->update($data);

        return $attendanceRecord->fresh();
    }

    public function delete(AttendanceRecord $attendanceRecord): void
    {
        $attendanceRecord->delete();
    }
}
