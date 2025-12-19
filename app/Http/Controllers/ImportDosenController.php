<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportDosenController extends Controller
{
    private const SESSION_KEY = 'import_dosen_file_path';

    private function authorizeSuperAdmin(): void
    {
        abort_unless(
            auth()->check() && auth()->user()->hasRole('Super Admin'),
            403,
            'Unauthorized'
        );
    }

    public function form()
    {
        $this->authorizeSuperAdmin();
        return view('import.dosen');
    }

    /**
     * PREVIEW (stabil, ada stats lengkap)
     */
    public function preview(Request $request)
    {
        $this->authorizeSuperAdmin();
        @set_time_limit(0);

        $request->validate([
            'file' => ['required','file','mimes:csv,txt'],
        ]);

        Storage::makeDirectory('imports');
        $storedPath = $request->file('file')->store('imports');
        session([self::SESSION_KEY => $storedPath]);

        [$delimiter, $idxName, $idxEmail, $fh] = $this->openFile($storedPath);

        $previewLimit = 200;
        $preview = [];

        $stats = [
            'total'       => 0,
            'will_import' => 0,
            'invalid'     => 0,
            'exists'      => 0,
            'duplicates'  => 0,
            'skipped'     => 0,
        ];

        $seen = [];
        $batchEmails = [];
        $batchPreviewRows = [];
        $batchSize = 1000;

        while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {

            $name  = trim($row[$idxName] ?? '');
            $email = trim($row[$idxEmail] ?? '');

            if ($name === '' && $email === '') continue;

            $stats['total']++;

            if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $stats['invalid']++;
                $stats['skipped']++;
                if (count($preview) < $previewLimit) {
                    $preview[] = ['name'=>$name,'email'=>$email,'status'=>'invalid'];
                }
                continue;
            }

            $emailKey = strtolower($email);

            if (isset($seen[$emailKey])) {
                $stats['duplicates']++;
                $stats['skipped']++;
                if (count($preview) < $previewLimit) {
                    $preview[] = ['name'=>$name,'email'=>$email,'status'=>'duplicate'];
                }
                continue;
            }
            $seen[$emailKey] = true;

            $batchEmails[] = $emailKey;

            if (count($preview) < $previewLimit) {
                $batchPreviewRows[] = ['name'=>$name,'email'=>$email,'emailKey'=>$emailKey];
            }

            if (count($batchEmails) >= $batchSize) {
                $this->finalizePreviewBatch($batchEmails, $batchPreviewRows, $preview, $stats);
                $batchEmails = [];
                $batchPreviewRows = [];
            }
        }

        if ($batchEmails) {
            $this->finalizePreviewBatch($batchEmails, $batchPreviewRows, $preview, $stats);
        }

        fclose($fh);

        return view('import.dosen', compact('preview','stats'));
    }

    /**
     * PROCESS IMPORT (pasti jalan)
     */
    public function process(Request $request)
    {
        $this->authorizeSuperAdmin();
        @set_time_limit(0);

        $storedPath = session(self::SESSION_KEY);
        if (!$storedPath || !Storage::exists($storedPath)) {
            return back()->withErrors('File import tidak ditemukan. Silakan upload ulang.');
        }

        [$delimiter, $idxName, $idxEmail, $fh] = $this->openFile($storedPath);

        $now = now();
        $seen = [];
        $batch = [];
        $batchEmails = [];
        $batchSize = 500;

        $imported = 0;
        $skipped  = 0;

        while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {

            $name  = trim($row[$idxName] ?? '');
            $email = trim($row[$idxEmail] ?? '');

            if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            $emailKey = strtolower($email);
            if (isset($seen[$emailKey])) {
                $skipped++;
                continue;
            }
            $seen[$emailKey] = true;

            $batch[] = [
                'name'       => $name,
                'email'      => $email,
                'role'       => 'dosen',
                'password'   => Hash::make(Str::random(40)),
                'is_active'  => 1,
                'created_at'=> $now,
                'updated_at'=> $now,
            ];
            $batchEmails[] = $emailKey;

            if (count($batch) >= $batchSize) {
                [$i,$s] = $this->insertBatch($batch,$batchEmails);
                $imported += $i;
                $skipped  += $s;
                $batch = [];
                $batchEmails = [];
            }
        }

        if ($batch) {
            [$i,$s] = $this->insertBatch($batch,$batchEmails);
            $imported += $i;
            $skipped  += $s;
        }

        fclose($fh);

        return redirect()
            ->route('import.dosen.form')
            ->with('success',"Import selesai. Imported: {$imported}, Skipped: {$skipped}");
    }

    /* ================= HELPERS ================= */

    private function openFile(string $storedPath): array
    {
        $fh = Storage::readStream($storedPath);
        if ($fh === false) {
            abort(500,'Gagal membuka file import.');
        }

        $firstLine = fgets($fh);
        $delimiter = (substr_count($firstLine,';') > substr_count($firstLine,',')) ? ';' : ',';
        rewind($fh);

        $header = fgetcsv($fh,0,$delimiter);
        $header = array_map(fn($h)=>strtoupper(trim(preg_replace('/^\xEF\xBB\xBF/','',$h))),$header);

        $idxName  = array_search('NAME_DISPLAY',$header);
        $idxEmail = array_search('EMAIL',$header);

        if ($idxName===false || $idxEmail===false) {
            abort(422,'Header wajib: NAME_DISPLAY dan Email');
        }

        return [$delimiter,$idxName,$idxEmail,$fh];
    }

    private function finalizePreviewBatch(array $emails,array $rows,array &$preview,array &$stats): void
    {
        $existing = User::whereIn('email',$emails)->pluck('email')->all();
        $existingSet = array_flip(array_map('strtolower',$existing));

        foreach ($emails as $e) {
            if (isset($existingSet[$e])) {
                $stats['exists']++;
                $stats['skipped']++;
            } else {
                $stats['will_import']++;
            }
        }

        foreach ($rows as $r) {
            if (count($preview)>=200) break;
            $status = isset($existingSet[$r['emailKey']]) ? 'exists' : 'import';
            $preview[] = ['name'=>$r['name'],'email'=>$r['email'],'status'=>$status];
        }
    }

    private function insertBatch(array $batch,array $emails): array
    {
        $existing = User::whereIn('email',$emails)->pluck('email')->all();
        $existingSet = array_flip(array_map('strtolower',$existing));

        $toInsert = [];
        $skipped  = 0;

        foreach ($batch as $row) {
            $k = strtolower($row['email']);
            if (isset($existingSet[$k])) {
                $skipped++;
            } else {
                $toInsert[] = $row;
                $existingSet[$k]=true;
            }
        }

        if ($toInsert) {
            DB::table('users')->insertOrIgnore($toInsert);
        }

        return [count($toInsert),$skipped];
    }
}
