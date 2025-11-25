<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssessmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code'=>'PAR','name'=>'Partisipasi/Attendance','order_no'=>1],
            ['code'=>'PRO','name'=>'Proyek Akhir','order_no'=>2],
            ['code'=>'TG','name'=>'Tugas','order_no'=>3],
            ['code'=>'QZ','name'=>'Kuis','order_no'=>4],
            ['code'=>'UTS','name'=>'Ujian Tengah Semester','order_no'=>5],
            ['code'=>'UAS','name'=>'Ujian Akhir Semester','order_no'=>6],
        ];

        foreach ($rows as $r) {
            DB::table('rps_assessment_categories')->updateOrInsert(
                ['code' => $r['code']],
                [
                    'name'       => $r['name'],
                    'order_no'   => $r['order_no'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
