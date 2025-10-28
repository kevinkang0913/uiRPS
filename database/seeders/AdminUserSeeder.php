<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@uph.edu'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password123'), // ganti sesuai kebutuhan
                'role' => 'admin', // kalau kamu sudah punya kolom role
            ]
        );
    }
}
