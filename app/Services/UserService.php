<?php

namespace App\Services;

use App\Interfaces\UserInterface;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(private readonly UserInterface $userRepository)
    {
    }

    public function createSuperAdmin(array $data): User
    {
        if ($this->userRepository->superAdminExists()) {
            throw ValidationException::withMessages([
                'super_admin' => 'A super-admin already exists.',
            ]);
        }

        $payload = Arr::only($data, ['name', 'email', 'password']);
        $payload['tenant_id'] = null;
        $payload['branch_id'] = null;
        $payload['is_super_admin'] = true;
        $payload['status'] = 'active';

        return $this->userRepository->create($payload);
    }
}
