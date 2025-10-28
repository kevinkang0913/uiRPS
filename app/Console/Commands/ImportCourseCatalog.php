<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ErpImport\CourseCatalogImporter;

class ImportCourseCatalog extends Command
{
    protected $signature = 'erp:import:course-catalog 
                            {path : Path ke file .xlsx} 
                            {--dry-run : Hanya validasi & generate laporan anomali, tanpa insert}
                            {--strict : Mode ekstra ketat (fail on first major anomaly)}';

    protected $description = 'Import Faculty, Program, Course dari file Excel katalog';

    public function handle(CourseCatalogImporter $importer)
    {
        $path   = $this->argument('path');
        $dry    = (bool) $this->option('dry-run');
        $strict = (bool) $this->option('strict');

        $this->info("Importing: {$path}");
        $result = $importer->run($path, $dry, $strict);

        $this->table(['Metric','Value'], [
            ['rows_scanned', $result->rows_scanned],
            ['faculties_upserted', $result->faculties_upserted],
            ['programs_upserted', $result->programs_upserted],
            ['courses_upserted', $result->courses_upserted],
            ['anomalies_total', count($result->anomalies)],
            ['report_csv', $result->report_csv ?? '-'],
        ]);

        if (!empty($result->anomalies)) {
            $this->warn("Ada anomali. Cek file report di atas untuk detail.");
        }

        if ($result->failed) {
            $this->error("IMPORT GAGAL (strict mode atau error fatal).");
            return Command::FAILURE;
        }

        $this->info($dry ? 'Dry-run selesai tanpa menulis ke DB.' : 'Import selesai & tersimpan di DB.');
        return Command::SUCCESS;
    }
}
