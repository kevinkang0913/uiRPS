<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LecturerSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Dosen Test',
            'email' => 'dosen@uph.edu',
            'password' => Hash::make('password123'),
            'role' => 'dosen', // pastikan field role ada di tabel users
        ]);
    }
}
