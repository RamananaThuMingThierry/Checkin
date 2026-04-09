<?php

namespace App\Services;

use App\Interfaces\PermissionInterface;
use App\Interfaces\RoleInterface;
use App\Models\Permission;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class PermissionService
{
    public function __construct(
        private readonly PermissionInterface $permissionRepository,
        private readonly RoleInterface $roleRepository,
    ) {
    }

    public function createPermission(array $data): Permission
    {
        $payload = Arr::only($data, ['name', 'code', 'module_code']);

        $existingPermission = $this->permissionRepository->getByKeys('code', $payload['code']);

        if ($existingPermission !== null) {
            throw ValidationException::withMessages([
                'code' => 'A permission with this code already exists.',
            ]);
        }

        return $this->permissionRepository->create($payload);
    }

    public function assignPermissionToRole(int $permissionId, int $roleId): Permission
    {
        $permission = $this->permissionRepository->getById($permissionId);
        $role = $this->roleRepository->getById($roleId);

        if ($permission === null) {
            throw ValidationException::withMessages([
                'permission_id' => 'The selected permission is invalid.',
            ]);
        }

        if ($role === null) {
            throw ValidationException::withMessages([
                'role_id' => 'The selected role is invalid.',
            ]);
        }

        if ($role->tenant_id !== null) {
            throw ValidationException::withMessages([
                'role_id' => 'Only global roles can receive a base permission.',
            ]);
        }

        $this->permissionRepository->attachRole($permission, $role->id);

        return $permission->load('roles');
    }
}
