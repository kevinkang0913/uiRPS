<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\Storage;

class ConvertRpsTemplate extends Command
{
    protected $signature = 'rps:convert-template';
    protected $description = 'Convert RPS Excel template into minimal Luckysheet JSON';

    public function handle()
    {
        $path = storage_path('app/templates/rps_template.xlsx');

        if (!file_exists($path)) {
            $this->error("❌ File Excel tidak ditemukan di: {$path}");
            return 1;
        }

        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();

        $rows = $worksheet->getHighestRow();
        $cols = Coordinate::columnIndexFromString($worksheet->getHighestColumn());

        $celldata = [];

        for ($row = 1; $row <= $rows; $row++) {
            for ($col = 1; $col <= $cols; $col++) {
                $cellAddress = Coordinate::stringFromColumnIndex($col) . $row;
                $value = $worksheet->getCell($cellAddress)->getValue();

                if ($value === null || $value === '') {
                    continue;
                }

                $celldata[] = [
                    'r' => $row - 1,
                    'c' => $col - 1,
                    'v' => ['v' => $value],
                ];
            }
        }

        $json = [[
            'name' => 'RPS Template',
            'index' => 0,
            'order' => 0,
            'status' => 1,
            'celldata' => $celldata,
            'config' => new \stdClass(),
        ]];

        // simpan ke public supaya bisa diakses browser
        $savePath = 'public/templates/rps_template.json';
        Storage::put($savePath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("✅ Template berhasil dikonversi (minimal) → storage/app/public/templates/rps_template.json");
        return 0;
    }
}
