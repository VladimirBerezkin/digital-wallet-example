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

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => UserResource::make($user),
            'token' => $token,
            'message' => 'Login successful',
        ]);
    }

    /**
     * Logout the authenticated user.
     */
    public function logout(): JsonResponse
    {
        $user = Auth::user();

        // Always delete all tokens for this user to ensure proper logout
        // This handles both normal operation and test environments
        $user->tokens()->delete();

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
