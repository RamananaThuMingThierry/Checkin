<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if ($token === null || $token === '') {
            return response()->json([
                'message' => 'Unauthenticated.',
                'success' => false,
            ], 401);
        }

        $hashedToken = hash('sha256', $token);
        $user = User::query()
            ->with(['roles', 'tenant', 'branch'])
            ->where('api_token', $hashedToken)
            ->where('status', 'active')
            ->first();

        if ($user === null) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'success' => false,
            ], 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
