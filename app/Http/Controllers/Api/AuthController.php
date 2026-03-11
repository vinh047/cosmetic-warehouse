<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $authService;

    // Inject Service vào qua Constructor
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * API Đăng nhập & Cấp Token
     */
    public function login(LoginRequest $request)
    {

        $result = $this->authService->login($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'access_token' => $result['token'],
                'token_type' => 'Bearer',
                'user'         => $result['user']
            ]
        ]);
    }

    /**
     * API Lấy thông tin User đang đăng nhập (Profile)
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    /**
     * API Đăng xuất & Thu hồi Token
     */
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Logout successfull'
        ]);
    }
}
