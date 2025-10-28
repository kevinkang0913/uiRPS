<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Program::insert([
    
    ['faculty_id' => 1, 'code' => 'IF', 'name' => 'Informatika'],
    ['faculty_id' => 2, 'code' => 'MNJ', 'name' => 'Manajemen'],
]);

    }
}
