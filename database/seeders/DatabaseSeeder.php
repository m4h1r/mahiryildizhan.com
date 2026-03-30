<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DictionarySeeder::class,
        ]);

        User::query()->updateOrCreate(
            ['email' => 'admin@mahiryildizhan.com'],
            [
                'name' => 'Mahir Yıldızhan',
                'password' => bcrypt('Martoto.97026'),
                'is_admin' => true,
            ]
        );

        $this->call([
            CsvImportSeeder::class,
        ]);

    }
}
