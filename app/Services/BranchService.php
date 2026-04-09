<?php

namespace App\Services;

use App\Models\Branch;
use App\Interfaces\BranchInterface;
use App\Interfaces\TenantInterface;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class BranchService
{
    public function __construct(
        private readonly BranchInterface $branchRepository,
        private readonly TenantInterface $tenantRepository,
    ) {
    }

    public function getAllBranch(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [], ?int $paginate = null, array $orderBy = []){
        return $this->branchRepository->getAll($keys, $value, $fields, $relations, $paginate, $orderBy);
    }

    public function getBranchById(int $id, array $fields = ['*'], array $relations = [])
    {
        return $this->branchRepository->getById($id, $fields, $relations);
    }

    public function getBranchByKeys(string|array|null $keys, mixed $value, array $fields = ['*'], array $relations = [])
    {
        return $this->branchRepository->getByKeys($keys, $value, $fields, $relations);
    }

    public function createBranch(array $data): Branch
    {
        $payload = Arr::only($data, [
            'tenant_id',
            'name',
            'code',
            'email',
            'phone',
            'address',
            'city',
            'country',
            'status',
        ]);

        $tenant = $this->tenantRepository->getById((int) $payload['tenant_id']);

        if ($tenant === null) {
            throw ValidationException::withMessages([
                'tenant_id' => 'The selected tenant is invalid.',
            ]);
        }

        $contraintExistingCode = [
            'tenant_id' => (int) $payload['tenant_id'],
            'code' => $payload['code']
        ];

        $existingCode = $this->branchRepository->getByKeys(
            array_keys($contraintExistingCode),
            array_values($contraintExistingCode)
        );

        if ($existingCode !== null) {
            throw ValidationException::withMessages([
                'code' => 'A branch with this code already exists for the tenant.',
            ]);
        }

        $contraintExistingMainBranch = [
            'tenant_id' => (int) $payload['tenant_id'],
            'is_main' => true
        ];

        $existingMainBranch = $this->branchRepository->getByKeys(
            array_keys($contraintExistingMainBranch),
            array_values($contraintExistingMainBranch)
        );

        if ($existingMainBranch !== null) {
            throw ValidationException::withMessages([
                'tenant_id' => 'A main branch already exists for this tenant.',
            ]);
        }

        $payload = array_merge([
            'status' => 'active',
            'is_main' => true,
        ], $payload);

        return $this->branchRepository->create($payload);
    }
}
