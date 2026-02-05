<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tạo tài khoản Admin cố định để test
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'), // Mật khẩu là: password
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 2. Tạo tài khoản Manager cố định
        User::create([
            'name' => 'Manager Kho',
            'email' => 'manager@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'is_active' => true,
        ]);

        // 3. Tạo thêm 5 nhân viên (Staff) ngẫu nhiên bằng Factory
        // Đảm bảo bạn đã có UserFactory mặc định của Laravel
        User::factory(5)->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->command->info('Da tao xong cac tai khoan User mau.');
    }
}
