<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

return new class extends \Illuminate\Database\Seeder {
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@root.com'],
            [
                'name'              => 'Administrator',
                'password'          => Hash::make('root'),
                'email_verified_at' => now(),
                'remember_token'    => Str::random(10),
            ]
        );
    }
};

