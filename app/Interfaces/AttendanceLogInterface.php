<?php

namespace App\Interfaces;

use App\Models\AttendanceLog;

interface AttendanceLogInterface
{
    public function getAll(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []);
    public function getById(int $id, array $fields = ['*'], array $relations = []): ?AttendanceLog;
    public function getByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = []): ?AttendanceLog;
    public function getDailyLogs(int $tenantId, string $date, array $fields = ['*'], array $relations = []): mixed;
    public function create(array $data): AttendanceLog;
    public function update(AttendanceLog $attendanceLog, array $data): AttendanceLog;
    public function delete(AttendanceLog $attendanceLog): void;
}
