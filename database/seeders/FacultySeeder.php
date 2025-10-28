<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faculty;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        Faculty::insert([
            
            ['code' => 'FE',  'name' => 'Fakultas Ekonomi'],
            ['code' => 'FK',  'name' => 'Fakultas Kedokteran'],
        ]);
    }
}
