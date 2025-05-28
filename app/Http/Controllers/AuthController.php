<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

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
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->register($request->validated());
            return ApiResponse::success('User registered successfully', $data, 201);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) {
                return ApiResponse::clientError('Validation error: ' . $th->getMessage(), $th->errors(), 422);
            } elseif ($th instanceof QueryException) {
                Log::error('Database error during registration: ' . $th->getMessage());
                return ApiResponse::serverError('Database error during registration', null, 500);
            } else {
                Log::error('Registration error: ' . $th->getMessage());
                return ApiResponse::serverError('Failed to register user: ' . $th->getMessage(), null, 500);
            }
        }
    }

    // login
    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            $token = $this->authService->login($credentials);

            if ($token === 'not_verified') {
                return ApiResponse::error('Email not verified', null, 403);
            }

            if (!$token) {
                return ApiResponse::error('Invalid credentials', null, 401);
            }

            return ApiResponse::success('Login successful', ['token' => $token], 200);
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) {
                return ApiResponse::clientError('Validation error: ' . $th->getMessage(), $th->errors(), 422);
            } elseif ($th instanceof \Illuminate\Auth\AuthenticationException) {
                return ApiResponse::clientError('Authentication error: ' . $th->getMessage(), null, 401);
            } else {
                Log::error('Login error: ' . $th->getMessage());
                return ApiResponse::serverError('Failed to login: ' . $th->getMessage(), null, 500);
            }
        }
    }

    // fetching user
    public function me(): JsonResponse
    {
        try {
            $user = $this->authService->getUser();
            
            if (!$user) {
                return ApiResponse::clientError('User not authenticated', null, 401);
            }
            
            return ApiResponse::success('User details fetched successfully', $user);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Auth\AuthenticationException) {
                return ApiResponse::clientError('Authentication error: ' . $th->getMessage(), null, 401);
            } else {
                Log::error('Error fetching user details: ' . $th->getMessage());
                return ApiResponse::serverError('Failed to fetch user details: ' . $th->getMessage(), null, 500);
            }
        }
    }

    // logout
    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();
            return ApiResponse::success('Successfully logged out');
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Auth\AuthenticationException) {
                return ApiResponse::clientError('Authentication error: ' . $th->getMessage(), null, 401);
            } else {
                Log::error('Logout error: ' . $th->getMessage());
                return ApiResponse::serverError('Failed to logout: ' . $th->getMessage(), null, 500);
            }
        }
    }

    // email verify
    public function verifyEmail($id, $hash)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email already verified.'], 200);
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            return response()->json(['message' => 'Email verified successfully.'], 200);
        } catch (\Throwable $th) {
            if ($th instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::clientError('User not found', null, 404);
            } elseif ($th instanceof QueryException) {
                Log::error('Database error during email verification: ' . $th->getMessage());
                return ApiResponse::serverError('Database error during email verification', null, 500);
            } else {
                Log::error('Email verification error: ' . $th->getMessage());
                return ApiResponse::serverError('Failed to verify email: ' . $th->getMessage(), null, 500);
            }
        }
    }
}
