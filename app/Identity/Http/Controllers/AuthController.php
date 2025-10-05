<?php

declare(strict_types=1);

namespace App\Identity\Http\Controllers;

use App\Identity\Http\Requests\LoginRequest;
use App\Identity\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class AuthController
{
    /**
     * Login an existing user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return response()->json([
            'user' => UserResource::make(Auth::user()),
            'message' => 'Login successful',
        ]);
    }

    /**
     * Logout the authenticated user.
     */
    public function logout(): JsonResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function user(): JsonResponse
    {
        return response()->json(UserResource::make(Auth::user()));
    }
}
