<?php

namespace App\Services\ErpImport;

use App\Models\Faculty;
use App\Models\Program;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;

class CourseCatalogImporter
{
    private array $aliases = [
        'faculty_code' => ['ACAD_GROUP'],
        'faculty_name' => ['DESCR'],
        'program_code' => ['Prodi_Code'],
        'program_name' => ['DESCR.1'],
        'course_id'    => ['CRSE_ID'],
        'subject'      => ['SUBJECT'],
        'catalog_nbr'  => ['CATALOG_NBR'],
        'course_name'  => ['DESCR.2'],
        'course_code'  => ['Kode Mata Kuliah'],
    ];

    public function run(string $filePath): array
    {
        // Spatie SimpleExcel: sheet index mulai dari 1
        $reader = SimpleExcelReader::create($filePath)->fromSheet(1);

        $first = $reader->getRows()->first();
        if (!$first || !is_array($first)) {
            return [
                'rows_scanned' => 0,
                'faculties' => 0,
                'programs'  => 0,
                'courses'   => 0,
                'note'      => 'Sheet pertama kosong atau tidak ditemukan data. Pastikan sheet "SQL Results" berada paling pertama.',
            ];
        }

        // streaming iterasi
        $rows = SimpleExcelReader::create($filePath)->fromSheet(1)->getRows();

        $rowsScanned = 0;
        $facUp = 0; $progUp = 0; $courseUp = 0;

        DB::transaction(function () use ($rows, &$rowsScanned, &$facUp, &$progUp, &$courseUp) {
            foreach ($rows as $row) {
                $rowsScanned++;

                $norm = $this->normalizeRow($row);

                $facultyCode = $this->get($norm, 'faculty_code');
                $facultyName = $this->get($norm, 'faculty_name');
                $programCode = $this->get($norm, 'program_code');
                $programName = $this->get($norm, 'program_name');
                $courseId    = $this->get($norm, 'course_id');
                $subject     = $this->get($norm, 'subject');
                $catalogNbr  = $this->get($norm, 'catalog_nbr');
                $courseName  = $this->get($norm, 'course_name');
                $courseCode  = $this->get($norm, 'course_code');

                // minimal kolom wajib
                if (!$facultyCode || !$programCode || !$courseId) continue;

                // 1) Faculty
                $faculty = Faculty::updateOrCreate(
                    ['code' => (string)$facultyCode],
                    ['name' => $facultyName ?: (string)$facultyCode]
                );
                $facUp++;

                // 2) Program (unique: faculty_id + code)
                $program = Program::updateOrCreate(
                    ['faculty_id' => $faculty->id, 'code' => (string)$programCode],
                    ['name' => $programName ?: (string)$programCode]
                );
                $progUp++;

                // 3) Course (unique: program_id + code)
                // Tentukan kode final: "Kode Mata Kuliah" atau SUBJECT + CATALOG_NBR atau CRSE_ID
                $finalCourseCode = $courseCode ?: trim(($subject ? $subject.' ' : '').(string)$catalogNbr ?: (string)$courseId);
                // Normalisasi (hindari duplikat karena spasi/kasus)
                $finalCourseCode = strtoupper(preg_replace('/\s+/', ' ', trim($finalCourseCode)));

                // Fallback catalog_nbr (kalau null)
                $catalogNbrFinal = $catalogNbr;
                if (!$catalogNbrFinal && preg_match('/(\d+)/', $finalCourseCode, $m)) {
                    $catalogNbrFinal = $m[1]; // contoh: "ACC 92112" -> "92112"
                }
                if (!$catalogNbrFinal && $courseId) {
                    $catalogNbrFinal = (string) $courseId;
                }

                // Fallback name
                $nameFinal = $courseName ?: $finalCourseCode;

                Course::updateOrCreate(
                    ['program_id' => $program->id, 'code' => $finalCourseCode],
                    [
                        // simpan CRSE_ID kalau kolomnya ada
                        'course_id'   => (string)$courseId,
                        'catalog_nbr' => (string)$catalogNbrFinal,
                        'name'        => $nameFinal,
                    ]
                );
                $courseUp++;
            }
        });

        return [
            'rows_scanned' => $rowsScanned,
            'faculties'    => $facUp,
            'programs'     => $progUp,
            'courses'      => $courseUp,
            'note'         => null,
        ];
    }

    private function normalizeRow(array $row): array
    {
        $norm = [];
        foreach ($row as $k => $v) {
            $norm[trim((string)$k)] = is_string($v) ? trim($v) : $v;
        }
        return $norm;
    }

    private function get(array $row, string $logical): ?string
    {
        foreach ($this->aliases[$logical] as $name) {
            if (array_key_exists($name, $row) && $row[$name] !== '' && $row[$name] !== null) {
                return (string)$row[$name];
            }
        }
        return null;
    }
}
