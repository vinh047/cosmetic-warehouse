<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Xử lý logic đăng nhập và cấp token
     */
    public function login(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        // 1. Kiểm tra User có tồn tại và sai mật khẩu không
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid login credentials.'],
            ]);
        }

        // 2. Chốt chặn bảo mật: Tài khoản bị khóa (nghỉ việc) thì không cho vào
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact the administrator.'],
            ]);
        }

        // Tạo Sanctum Token (Xóa token cũ nếu muốn giới hạn 1 thiết bị, ở đây mình cho phép nhiều thiết bị)
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token
        ];
    }

    public function logout($user)
    {
        return $user->currentAccessToken()->delete();
    }
}
