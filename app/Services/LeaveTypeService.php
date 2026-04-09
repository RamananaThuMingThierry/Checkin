<?php

namespace App\Services;

use App\Interfaces\LeaveTypeInterface;
use App\Interfaces\TenantInterface;
use App\Models\LeaveType;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class LeaveTypeService
{
    public function __construct(
        private readonly LeaveTypeInterface $leaveTypeRepository,
        private readonly TenantInterface $tenantRepository,
    ) {
    }

    public function listLeaveTypes(int $tenantId)
    {
        return $this->leaveTypeRepository->getAll(
            keys: 'tenant_id',
            value: $tenantId,
            orderBy: ['id' => 'asc'],
        );
    }

    public function createLeaveType(array $data): LeaveType
    {
        $payload = Arr::only($data, ['tenant_id', 'name', 'code', 'is_paid', 'description']);
        $tenantId = (int) $payload['tenant_id'];

        if ($this->tenantRepository->getById($tenantId) === null) {
            throw ValidationException::withMessages([
                'tenant_id' => 'The selected tenant is invalid.',
            ]);
        }

        $existing = $this->leaveTypeRepository->getByKeys(['tenant_id', 'code'], [$tenantId, $payload['code']]);

        if ($existing !== null) {
            throw ValidationException::withMessages([
                'code' => 'A leave type with this code already exists for the tenant.',
            ]);
        }

        $payload['is_paid'] = $payload['is_paid'] ?? true;

        return $this->leaveTypeRepository->create($payload);
    }
}
