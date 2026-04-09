<?php

namespace App\Services;

use App\Interfaces\RoleInterface;
use App\Interfaces\UserInterface;
use App\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class RoleService
{
    public function __construct(
        private readonly RoleInterface $roleRepository,
        private readonly UserInterface $userRepository,
    ) {
    }

    public function createGlobalRole(array $data): Role
    {
        $payload = Arr::only($data, ['name', 'code', 'description']);
        $payload['tenant_id'] = null;

        $existingRole = $this->roleRepository->getByKeys(['tenant_id', 'code'], [null, $payload['code']]);

        if ($existingRole !== null) {
            throw ValidationException::withMessages([
                'code' => 'A global role with this code already exists.',
            ]);
        }

        return $this->roleRepository->create($payload);
    }

    public function assignGlobalRoleToUser(int $roleId, int $userId): Role
    {
        $role = $this->roleRepository->getById($roleId);
        $user = $this->userRepository->getById($userId);

        if ($role === null) {
            throw ValidationException::withMessages([
                'role_id' => 'The selected role is invalid.',
            ]);
        }

        if ($user === null) {
            throw ValidationException::withMessages([
                'user_id' => 'The selected user is invalid.',
            ]);
        }

        if ($role->tenant_id !== null) {
            throw ValidationException::withMessages([
                'role_id' => 'Only global roles can be assigned from this endpoint.',
            ]);
        }

        if ($user->tenant_id !== null) {
            throw ValidationException::withMessages([
                'user_id' => 'Only global users can receive a global role.',
            ]);
        }

        $this->roleRepository->attachUser($role, $user->id);

        return $role->load('users');
    }
}
