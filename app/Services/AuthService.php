<?php

namespace App\Services;

use App\Interfaces\UserInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private readonly UserInterface $userRepository)
    {
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->getByKeys('email', $email, ['*'], ['roles']);

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are invalid.',
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => 'The user account is not active.',
            ]);
        }

        $plainToken = Str::random(80);
        $hashedToken = hash('sha256', $plainToken);

        $this->userRepository->update($user->id, [
            'api_token' => $hashedToken,
            'last_login_at' => Carbon::now(),
        ]);

        $refreshedUser = $this->userRepository->getById($user->id, ['*'], ['roles']);

        return [
            'token' => $plainToken,
            'user' => $refreshedUser,
        ];
    }
}
