<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register(array $data){
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->sendEmailVerificationNotification();
        return compact('user');
    }

    public function login(array $credentials){
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        if (!$user->email_verified_at) {
            return 'not_verified';
        }
        
        $token = JWTAuth::fromUser($user);
        $user->current_jwt_token = $token;
        $user->save();

        return [
            'token' => $token,
            'user_id' => $user->id,
            'role' => $user->role,
        ];

    }

    public function getUser(){
        return auth()->user();
    }

    public function logout(){
        auth()->logout();
    }
}
