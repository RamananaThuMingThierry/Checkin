<?php

namespace App\Interfaces;

use App\Models\AttendanceRecord;

interface AttendanceRecordInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?AttendanceRecord;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?AttendanceRecord;
    public function create(array $data): AttendanceRecord;
    public function update(AttendanceRecord $attendanceRecord, array $data): AttendanceRecord;
    public function delete(AttendanceRecord $attendanceRecord): void;
}
