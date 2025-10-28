<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ErpImport\CourseCatalogImporter;

class ImportController extends Controller
{
    public function showForm()
    {
        $summary = session('import_summary');
        return view('admin.imports.course_catalog', compact('summary'));
    }

    public function import(Request $request, CourseCatalogImporter $importer)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
        ]);

        $path = $request->file('file')->store('imports');
        $result = $importer->run(Storage::path($path));

$summary = [
    'path'         => $path,
    'rows_scanned' => $result['rows_scanned'],
    'faculties'    => $result['faculties'],
    'programs'     => $result['programs'],
    'courses'      => $result['courses'],
    'note'         => $result['note'] ?? null,
];


        return redirect()
            ->route('admin.import.courses.form')
            ->with('import_summary', $summary)
            ->with('success', 'Import berhasil dijalankan.');
    }
}
