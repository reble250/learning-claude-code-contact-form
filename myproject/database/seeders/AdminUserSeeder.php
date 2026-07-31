<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * 管理者アカウントを作成（.envのADMIN_EMAIL/ADMIN_PASSWORDを使用）
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => '管理者',
                'password' => env('ADMIN_PASSWORD', 'password'),
            ]
        );
    }
}
