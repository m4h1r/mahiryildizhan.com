<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class AliceTokenSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('is_admin', true)->first();

        if (! $admin) {
            $this->command->warn('Admin kullanıcı bulunamadı. Önce bir admin oluşturun.');

            return;
        }

        Artisan::call('alice:rotate-token');
        $this->command->info(Artisan::output());
    }
}
