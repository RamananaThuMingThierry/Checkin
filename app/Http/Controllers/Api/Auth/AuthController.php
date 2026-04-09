<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();
            $authenticated = $this->authService->login($data['email'], $data['password']);
            DB::commit();

            return response()->json([
                'data' => [
                    'token' => $authenticated['token'],
                    'user' => $authenticated['user'],
                ],
                'message' => 'Login successful.',
                'success' => true,
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function me(Request $request)
    {
        return response()->json([
            'data' => $request->user(),
            'success' => true,
        ], 200);
    }
}
