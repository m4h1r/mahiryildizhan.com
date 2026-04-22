<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@mahiryildizhan.com'],
            [
                'name' => 'Mahir Yıldızhan',
                'password' => bcrypt('Martoto.97026'),
                'is_admin' => true,
            ]
        );
    }
}
