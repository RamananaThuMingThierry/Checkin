<?php

namespace App\Services;

use App\Interfaces\BranchInterface;
use App\Interfaces\RoleInterface;
use App\Interfaces\TenantInterface;
use App\Interfaces\UserInterface;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class TenantAdminService
{
    public function __construct(
        private readonly UserInterface $userRepository,
        private readonly TenantInterface $tenantRepository,
        private readonly BranchInterface $branchRepository,
        private readonly RoleInterface $roleRepository,
    ) {
    }

    public function createTenantAdmin(array $data): User
    {
        $payload = Arr::only($data, ['tenant_id', 'name', 'email', 'password']);

        $tenant = $this->tenantRepository->getById((int) $payload['tenant_id']);

        if ($tenant === null) {
            throw ValidationException::withMessages([
                'tenant_id' => 'The selected tenant is invalid.',
            ]);
        }

        $mainBranch = $this->branchRepository->getByKeys(['tenant_id', 'is_main'], [(int) $payload['tenant_id'], true]);

        $payload['branch_id'] = $mainBranch?->id;
        $payload['tenant_id'] = (int) $payload['tenant_id'];
        $payload['is_super_admin'] = false;
        $payload['status'] = 'active';

        $user = $this->userRepository->create($payload);

        $role = $this->findOrCreateTenantAdminRole((int) $payload['tenant_id']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user->load('roles');
    }

    private function findOrCreateTenantAdminRole(int $tenantId): Role
    {
        $role = $this->roleRepository->getByKeys(['tenant_id', 'code'], [$tenantId, 'tenant-admin']);

        if ($role !== null) {
            return $role;
        }

        return $this->roleRepository->create([
            'tenant_id' => $tenantId,
            'name' => 'Tenant Admin',
            'code' => 'tenant-admin',
            'description' => 'Default tenant administration role.',
        ]);
    }
}
