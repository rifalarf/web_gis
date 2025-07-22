<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@root.com'],
            [
                'name'              => 'Admin',
                'password'          => Hash::make('root'),
                'email_verified_at' => now(),
                'remember_token'    => Str::random(10),
            ]
        );
    }
}
