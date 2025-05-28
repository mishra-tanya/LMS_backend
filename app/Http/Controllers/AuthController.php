<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Events\Verified;

use App\Http\Requests\RegisterRequest;

use App\Services\AuthService;

use App\Helpers\ApiResponse;

use App\Models\User;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService){
        $this->authService = $authService;
    }

    // register
    public function register(RegisterRequest $request): JsonResponse{
        $data = $this->authService->register($request->validated());
        return ApiResponse::success('User registered successfully', $data, 201);
    }

    // login
    public function login(Request $request): JsonResponse{
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        $credentials = $request->only('email', 'password');
        $token = $this->authService->login($credentials);

        if ($token === 'not_verified') {
            return ApiResponse::error('Email not verified', null, 403);
        }

        if (!$token) {
            return ApiResponse::error('Invalid credentials', null, 401);
        }

        return ApiResponse::success('Login successful', ['token' => $token], 201);
    }

    // fetching user
    public function me(): JsonResponse{
        return ApiResponse::success('User details fetched successfully', $this->authService->getUser());
    }

    // logout
    public function logout(): JsonResponse{
       $this->authService->logout();
        return ApiResponse::success('Successfully logged out');
    }

    // email verify
    public function verifyEmail($id, $hash){
        $user = User::where('id', $id)->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        if ($user->markEmailAsVerified()) {
            if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
                event(new Verified($user));
            }
        }

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
}
