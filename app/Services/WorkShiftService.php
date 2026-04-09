<?php

namespace App\Services;

use App\Interfaces\BranchInterface;
use App\Interfaces\TenantInterface;
use App\Interfaces\WorkShiftInterface;
use App\Models\WorkShift;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class WorkShiftService
{
    public function __construct(
        private readonly WorkShiftInterface $workShiftRepository,
        private readonly TenantInterface $tenantRepository,
        private readonly BranchInterface $branchRepository,
    ) {
    }

    public function createWorkShift(array $data): WorkShift
    {
        $payload = Arr::only($data, [
            'tenant_id',
            'branch_id',
            'name',
            'code',
            'start_time',
            'end_time',
            'break_duration_minutes',
            'late_tolerance_minutes',
            'is_night_shift',
        ]);

        $payload['break_duration_minutes'] ??= 0;
        $payload['late_tolerance_minutes'] ??= 0;
        $payload['is_night_shift'] ??= false;

        $tenantId = (int) $payload['tenant_id'];

        $this->assertTenantExists($tenantId);
        $this->assertBranchBelongsToTenant($tenantId, $payload['branch_id'] ?? null);
        $this->assertUniqueCode($tenantId, $payload['code']);
        $this->assertValidTimes($payload['start_time'], $payload['end_time'], (bool) $payload['is_night_shift']);

        return $this->workShiftRepository->create($payload);
    }

    private function assertTenantExists(int $tenantId): void
    {
        if ($this->tenantRepository->getById($tenantId) === null) {
            throw ValidationException::withMessages([
                'tenant_id' => 'The selected tenant is invalid.',
            ]);
        }
    }

    private function assertBranchBelongsToTenant(int $tenantId, ?int $branchId): void
    {
        if ($branchId === null) {
            return;
        }

        $branch = $this->branchRepository->getById($branchId);

        if ($branch === null || $branch->tenant_id !== $tenantId) {
            throw ValidationException::withMessages([
                'branch_id' => 'The selected branch is invalid for the tenant.',
            ]);
        }
    }

    private function assertUniqueCode(int $tenantId, string $code): void
    {
        $existing = $this->workShiftRepository->getByKeys(['tenant_id', 'code'], [$tenantId, $code]);

        if ($existing !== null) {
            throw ValidationException::withMessages([
                'code' => 'A work shift with this code already exists for the tenant.',
            ]);
        }
    }

    private function assertValidTimes(string $startTime, string $endTime, bool $isNightShift): void
    {
        if ($startTime === $endTime) {
            throw ValidationException::withMessages([
                'end_time' => 'The end time must differ from the start time.',
            ]);
        }

        if (! $isNightShift && $endTime < $startTime) {
            throw ValidationException::withMessages([
                'end_time' => 'The end time must be after the start time unless the shift is marked as night shift.',
            ]);
        }
    }
}
