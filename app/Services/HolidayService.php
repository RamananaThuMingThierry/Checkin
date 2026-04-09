<?php

namespace App\Services;

use App\Interfaces\BranchInterface;
use App\Interfaces\HolidayInterface;
use App\Interfaces\TenantInterface;
use App\Models\Holiday;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class HolidayService
{
    public function __construct(
        private readonly HolidayInterface $holidayRepository,
        private readonly TenantInterface $tenantRepository,
        private readonly BranchInterface $branchRepository,
    ) {
    }

    public function listHolidays(string $tenantEncryptedId)
    {
        $tenantId = decrypt_to_int_or_null($tenantEncryptedId);

        if ($tenantId === null || $this->tenantRepository->getById($tenantId) === null) {
            throw ValidationException::withMessages([
                'tenant_id' => 'The selected tenant is invalid.',
            ]);
        }

        return $this->holidayRepository->getAll(
            keys: 'tenant_id',
            value: $tenantId,
            relations: ['branch'],
            orderBy: ['holiday_date' => 'asc', 'id' => 'asc'],
        );
    }

    public function createHoliday(array $data): Holiday
    {
        $payload = Arr::only($data, ['tenant_id', 'branch_id', 'name', 'holiday_date', 'is_recurring']);

        $tenantId = decrypt_to_int_or_null($payload['tenant_id']);
        if ($tenantId === null || $this->tenantRepository->getById($tenantId) === null) {
            throw ValidationException::withMessages([
                'tenant_id' => 'The selected tenant is invalid.',
            ]);
        }

        $branchId = decrypt_to_int_or_null($payload['branch_id'] ?? null);
        if ($payload['branch_id'] ?? null) {
            $branch = $branchId !== null ? $this->branchRepository->getById($branchId) : null;

            if ($branch === null || (int) $branch->tenant_id !== $tenantId) {
                throw ValidationException::withMessages([
                    'branch_id' => 'The selected branch is invalid for the tenant.',
                ]);
            }
        }

        $existing = $this->holidayRepository->getByKeys(
            ['tenant_id', 'holiday_date'],
            [$tenantId, $payload['holiday_date']],
        );

        if ($existing !== null) {
            throw ValidationException::withMessages([
                'holiday_date' => 'A holiday already exists for this date and tenant.',
            ]);
        }

        return $this->holidayRepository->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'name' => $payload['name'],
            'holiday_date' => $payload['holiday_date'],
            'is_recurring' => $payload['is_recurring'] ?? false,
        ]);
    }
}
