<?php

namespace App\Services\ErpImport;

use App\Models\Faculty;
use App\Models\Program;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;

class CourseCatalogImporter
{
    /**
     * Header aliases (mendukung format BARU & LAMA sekaligus).
     */
    private array $aliases = [
        // Faculty
        'faculty_code' => ['ACAD_GROUP (kode fakultas)', 'ACAD_GROUP'],
        'faculty_name' => ['Fakultas', 'DESCR'],

        // Program
        'program_code' => ['Prodi_Code', 'ACAD_PROG'],
        'program_name' => ['PRODI', 'DESCR.1'],

        // Course
        'course_id'    => ['COURSE_ID', 'CRSE_ID'],
        'subject'      => ['SUBJECT'],
        'catalog_nbr'  => ['CATALOG_NBR'],
        'course_name'  => ['Nama Mata Kuliah', 'DESCR.2'],
        'course_code'  => ['Kode Mata Kuliah'], // ex: "ACC 93105"
    ];

    public function run(string $filePath): array
    {
        // Spatie SimpleExcel: index sheet mulai dari 1
        $reader = SimpleExcelReader::create($filePath)->fromSheet(1);

        $first = $reader->getRows()->first();
        if (!$first || !is_array($first)) {
            return [
                'rows_scanned' => 0,
                'faculties'    => 0,
                'programs'     => 0,
                'courses'      => 0,
                'skipped'      => [],
                'note'         => 'Sheet pertama kosong / header tidak terbaca.',
            ];
        }

        // Re-create reader untuk streaming
        $rows = SimpleExcelReader::create($filePath)->fromSheet(1)->getRows();

        $rowsScanned = 0;
        $facUp = 0; $progUp = 0; $courseUp = 0;
        $skipped = [];

        DB::transaction(function () use ($rows, &$rowsScanned, &$facUp, &$progUp, &$courseUp, &$skipped) {
            foreach ($rows as $row) {
                $rowsScanned++;

                $norm = $this->normalizeRow($row);

                // Ambil nilai mentah
                $facultyCodeRaw = $this->get($norm, 'faculty_code');
                $facultyNameRaw = $this->get($norm, 'faculty_name');
                $programCodeRaw = $this->get($norm, 'program_code');
                $programNameRaw = $this->get($norm, 'program_name');
                $courseIdRaw    = $this->get($norm, 'course_id');
                $subject        = $this->get($norm, 'subject');
                $catalogNbrRaw  = $this->get($norm, 'catalog_nbr');
                $courseNameRaw  = $this->get($norm, 'course_name');
                $courseCodeRaw  = $this->get($norm, 'course_code');

                // Minimal wajib untuk lanjut
                if (!$programCodeRaw || !$courseIdRaw) {
                    $skipped[] = [
                        'reason'       => 'missing_program_or_course_id',
                        'program_code' => $programCodeRaw,
                        'course_id'    => $courseIdRaw,
                    ];
                    continue;
                }

                // Normalisasi angka → string rapi (92112.0 → "92112")
                $facultyCode = $this->toScalarStr($facultyCodeRaw);
                $programCode = $this->toScalarStr($programCodeRaw);
                $catalogNbr  = $this->toScalarStr($catalogNbrRaw);
                $courseId    = $this->toScalarStr($courseIdRaw);

                // Normalisasi teks
                $facultyName = $this->norm($facultyNameRaw);
                $programName = $this->norm($programNameRaw);
                $courseName  = $this->norm($courseNameRaw);
                $courseCode  = $this->norm($courseCodeRaw);
                $subject     = $this->norm($subject);

                // ===== Resolve Faculty (smart) =====
                $strict = (bool) config('erp_import.strict_faculty_mapping', true);
                $faculty = $this->resolveFaculty($facultyCode, $facultyName, $strict);

                if (!$faculty) {
                    // strict dan gagal resolve: skip baris
                    $skipped[] = [
                        'reason'       => 'unmapped_faculty',
                        'faculty_code' => $facultyCode,
                        'faculty_name' => $facultyName,
                        'program_code' => $programCode,
                        'program_name' => $programName,
                    ];
                    continue;
                }
                $facUp++;

                // ===== Program (bisa override via config) =====
                $pmap = config('erp_import.program_map', []);
                $progTarget = $pmap[$programCode] ?? null;

                // faculty program bisa dioverride
                $programFaculty = $faculty;
                if ($progTarget && !empty($progTarget['faculty_code'])) {
                    $pf = Faculty::where('code', $this->norm($progTarget['faculty_code']))->first();
                    if ($pf) $programFaculty = $pf;
                }

                $canonicalProgramCode = $this->toScalarStr($progTarget['program_code'] ?? $programCode);
                $canonicalProgramName = $this->norm($progTarget['program_name'] ?? ($programName ?: $canonicalProgramCode));

                $program = Program::updateOrCreate(
                    ['faculty_id' => $programFaculty->id, 'code' => $canonicalProgramCode],
                    ['name' => $canonicalProgramName]
                );
                $progUp++;

                // ===== Course (unique: program_id + code) =====
                // Kode final: "Kode Mata Kuliah" | SUBJECT + CATALOG | COURSE_ID
                $finalCourseCode = $courseCode ?: trim(($subject ? $subject.' ' : '') . ($catalogNbr ?: $courseId));
                $finalCourseCode = $this->norm($finalCourseCode);

                // Fallback catalog
                $catalogNbrFinal = $catalogNbr;
                if (!$catalogNbrFinal && preg_match('/(\d+)/', $finalCourseCode, $m)) {
                    $catalogNbrFinal = $m[1];
                }
                if (!$catalogNbrFinal && $courseId) {
                    $catalogNbrFinal = $courseId;
                }

                // Fallback name
                $nameFinal = $courseName ?: $finalCourseCode;

                Course::updateOrCreate(
                    ['program_id' => $program->id, 'code' => $finalCourseCode],
                    [
                        'course_id'   => $courseId,
                        'catalog_nbr' => $catalogNbrFinal, // pastikan kolom ini nullable di DB
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
            'skipped'      => $skipped,
            'note'         => null,
        ];
    }

    /* ========================= Helpers ========================= */

    /**
     * Normalisasi row (buang "Unnamed: x", trim string).
     */
    private function normalizeRow(array $row): array
    {
        $norm = [];
        foreach ($row as $k => $v) {
            if (preg_match('/^Unnamed:/i', (string)$k)) {
                continue;
            }
            $norm[trim((string)$k)] = is_string($v) ? trim($v) : $v;
        }
        return $norm;
    }

    /**
     * Ambil nilai berdasarkan alias.
     */
    private function get(array $row, string $logical): ?string
    {
        foreach ($this->aliases[$logical] as $name) {
            if (array_key_exists($name, $row) && $row[$name] !== '' && $row[$name] !== null) {
                return (string)$row[$name];
            }
        }
        return null;
    }

    /**
     * Normalisasi teks → UPPER + single space.
     */
    private function norm(?string $s): ?string
    {
        if ($s === null || $s === '') return null;
        return strtoupper(preg_replace('/\s+/', ' ', trim($s)));
    }

    /**
     * Ubah float/integer ke string wajar (92112.0 → "92112").
     */
    private function toScalarStr($v): ?string
    {
        if ($v === null || $v === '') return null;
        if (is_float($v) && floor($v) == $v) return (string)(int)$v;
        return (string)$v;
    }

    /**
     * Resolve Faculty dengan beberapa tahap:
     * 1) Map composite "CODE|NAME"
     * 2) Map by CODE
     * 3) Map by NAME alias
     * 4) Fallback: jika sudah ada faculty dengan CODE sama di DB → pakai itu
     * 5) Non-strict: buat baru dari CODE/NAME
     */
    private function resolveFaculty(?string $code, ?string $name, bool $strict): ?Faculty
    {
        $code = $this->norm($code);
        $name = $this->norm($name);

        $fmap     = config('erp_import.faculty_map', []);
        $nameMap  = config('erp_import.faculty_name_map', []);

        // 1) composite
        if ($code && $name) {
            $key = $code.'|'.$name;
            if (isset($fmap[$key])) {
                if ($fac = Faculty::where('code', $this->norm($fmap[$key]))->first()) {
                    return $fac;
                }
            }
        }
        // 2) by code
        if ($code && isset($fmap[$code])) {
            if ($fac = Faculty::where('code', $this->norm($fmap[$code]))->first()) {
                return $fac;
            }
        }
        // 3) by name alias
        if ($name && isset($nameMap[$name])) {
            if ($fac = Faculty::where('code', $this->norm($nameMap[$name]))->first()) {
                return $fac;
            }
        }
        // 4) fallback: kalau DB sudah ada faculty dengan code yang sama → pakai itu
        if ($code) {
            if ($fac = Faculty::where('code', $code)->first()) {
                return $fac;
            }
        }
        // 5) non-strict: boleh create baru
        if (!$strict && $code) {
            return Faculty::firstOrCreate(
                ['code' => $code],
                ['name' => $name ?: $code]
            );
        }

        return null; // strict dan gagal resolve
    }
}
