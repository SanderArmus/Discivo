<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $email = 'test@test.ee';
        $password = 'TestKasutaja123';

        User::query()->firstOrCreate(
            ['email' => $email],
            [
                'username' => 'test',
                'name' => 'Test Kasutaja',
                'password' => $password,
                'email_verified_at' => now(),
                'role' => null,
            ]
        );

        // Ensure username is present even if an existing row matched by email.
        User::query()
            ->where('email', $email)
            ->whereNull('username')
            ->update(['username' => 'test'.Str::random(4)]);
    }
}
