<?php

namespace App\Services;

use App\Interfaces\BranchInterface;
use App\Interfaces\DeviceInterface;
use App\Interfaces\TenantInterface;
use App\Models\Device;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class DeviceService
{
    public function __construct(
        private readonly DeviceInterface $deviceRepository,
        private readonly TenantInterface $tenantRepository,
        private readonly BranchInterface $branchRepository,
    ) {
    }

    public function createDevice(array $data): Device
    {
        $payload = Arr::only($data, [
            'tenant_id',
            'branch_id',
            'name',
            'code',
            'type',
            'serial_number',
            'status',
        ]);

        $payload['type'] ??= 'mobile';
        $payload['status'] ??= 'active';

        $tenantId = (int) $payload['tenant_id'];

        $this->assertTenantExists($tenantId);
        $this->assertBranchBelongsToTenant($tenantId, $payload['branch_id'] ?? null);
        $this->assertUniqueCode($tenantId, $payload['code']);

        return $this->deviceRepository->create($payload);
    }

    public function assignBranch(int $deviceId, int $branchId): Device
    {
        $device = $this->deviceRepository->getById($deviceId);

        if ($device === null) {
            throw ValidationException::withMessages([
                'device_id' => 'The selected device is invalid.',
            ]);
        }

        $this->assertBranchBelongsToTenant($device->tenant_id, $branchId);

        return $this->deviceRepository->update($device, [
            'branch_id' => $branchId,
        ]);
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
        $existing = $this->deviceRepository->getByKeys(['tenant_id', 'code'], [$tenantId, $code]);

        if ($existing !== null) {
            throw ValidationException::withMessages([
                'code' => 'A device with this code already exists for the tenant.',
            ]);
        }
    }
}
