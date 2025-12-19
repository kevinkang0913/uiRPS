<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RpsController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserScopeController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ClassSectionController;
use App\Http\Controllers\AcademicApiController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportDosenController;
/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('welcome'));

// Dashboard (login + verified)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/pending-role', function () {
        return view('auth.pending-role');
    })->name('pending-role');
});

/*
|--------------------------------------------------------------------------
| Authenticated area
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile — semua user yang login
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/import/dosen', [ImportDosenController::class, 'form'])
        ->name('import.dosen.form');

    Route::post('/import/dosen/preview', [ImportDosenController::class, 'preview'])
        ->name('import.dosen.preview');

    Route::post('/import/dosen/process', [ImportDosenController::class, 'process'])
        ->name('import.dosen.process');
    /*
    |--------------------------------------------------------------------------
    | RPS INDEX + SHOW
    |   - INDEX: daftar RPS (scoping per role di controller)
    |   - SHOW : detail RPS — boleh dilihat semua user login
    |--------------------------------------------------------------------------
    */
    Route::get('/rps', [RpsController::class, 'index'])->name('rps.index');

    Route::get('/rps/{rps}/show', [RpsController::class, 'show'])
        ->name('rps.show');
    Route::get('/rps/{rps}/clone', [RpsController::class, 'cloneForm'])->name('rps.clone.form');
Route::post('/rps/{rps}/clone', [RpsController::class, 'cloneStore'])->name('rps.clone.store');

    /*
    |--------------------------------------------------------------------------
    | RPS Wizard (Dosen & Super Admin)
    |   → hanya mereka yang boleh create / edit RPS
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Dosen,Super Admin, Admin')->group(function () {

        // Mulai RPS baru (reset wizard)
        Route::get('/rps/create', [RpsController::class, 'startNew'])
            ->name('rps.start');

        // Mapping CPL–CPMK manual (fitur terpisah dari wizard utama)
        Route::get('/rps/{rps}/cpl-cpmk', [RpsController::class, 'editCplCpmk'])
            ->name('rps.cpl_cpmk.edit');
        Route::post('/rps/{rps}/cpl-cpmk', [RpsController::class, 'updateCplCpmk'])
            ->name('rps.cpl_cpmk.update');

        // Lanjutkan wizard dari RPS tertentu
        Route::get('/rps/{rps}/resume/{step?}', [RpsController::class, 'resume'])
            ->name('rps.resume');

        // Auto-resume ke step yang sesuai (mis. dari list RPS)
        Route::get('/rps/{rps}/resume-auto', [RpsController::class, 'resumeAuto'])
            ->name('rps.resume.auto');

        /*
        |--------------------------------------------------------------------------
        | RPS Wizard Step-by-step (LETakkan di atas resource RPS)
        |--------------------------------------------------------------------------
        */
        Route::get('/rps/create/step/{step}', [RpsController::class, 'createStep'])
            ->whereNumber('step')
            ->name('rps.create.step');

        Route::post('/rps/create/step/{step}', [RpsController::class, 'storeStep'])
            ->whereNumber('step')
            ->name('rps.store.step');

        /*
        |--------------------------------------------------------------------------
        | Resource utama RPS:
        |   - TANPA index (sudah didefinisikan bebas di atas)
        |   - TANPA create/store/show (pakai wizard & /rps/{rps}/show)
        |--------------------------------------------------------------------------
        */
        Route::resource('rps', RpsController::class)
            ->except(['index', 'create', 'store', 'show']);
    });

    /*
    |--------------------------------------------------------------------------
    | AJAX / API Akademik — dipakai di RPS wizard
    |   (boleh untuk semua user login)
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/faculties', [AcademicApiController::class, 'faculties'])
            ->name('faculties');
        Route::get('/programs/{faculty}', [AcademicApiController::class, 'programsByFaculty'])
            ->name('programs.byFaculty');
        Route::get('/courses/{program}', [AcademicApiController::class, 'coursesByProgram'])
            ->name('courses.byProgram');
        Route::get('/sections/{course}', [AcademicApiController::class, 'sectionsByCourse'])
            ->name('sections.byCourse');
    });

    /*
    |--------------------------------------------------------------------------
    | Workflow Review & Approval
    |--------------------------------------------------------------------------
    */

    // CTL — Review RPS (+ Super Admin)
    Route::middleware('role:CTL,Super Admin')->group(function () {
        Route::get('reviews',        [ReviewController::class, 'index'])->name('reviews.index');
        Route::get('reviews/{rps}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
        Route::post('reviews/{rps}', [ReviewController::class, 'store'])->name('reviews.store');
    });

    // Kaprodi — Approval RPS (+ Super Admin)
    Route::middleware('role:Kaprodi,Super Admin')->group(function () {
        Route::get('approvals',        [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('approvals/{rps}/edit', [ApprovalController::class, 'edit'])->name('approvals.edit');
        Route::post('approvals/{rps}', [ApprovalController::class, 'store'])->name('approvals.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin / Super Admin — Import, Master Data, Users & Roles, Activity Logs
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Admin,Super Admin')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Admin – Import & Reports
        |--------------------------------------------------------------------------
        */
        Route::get('/admin/import/courses', [ImportController::class, 'showForm'])
            ->name('admin.import.courses.form');
        Route::post('/admin/import/courses/import', [ImportController::class, 'import'])
            ->name('admin.import.courses.real');
        Route::get('/admin/import/courses/report', [ImportController::class, 'downloadReport'])
            ->name('admin.import.courses.report');

        Route::get('/admin/reports/export', fn () => 'Coming soon')
            ->name('reports.export');

        /*
        |--------------------------------------------------------------------------
        | Master Data (CRUD)
        |--------------------------------------------------------------------------
        */
        Route::resource('faculties', FacultyController::class);
        Route::resource('programs', ProgramController::class);
        Route::resource('courses', CourseController::class);
        Route::get('courses/{course}/lecturers', [CourseController::class, 'lecturers'])
            ->name('courses.lecturers');
        Route::post('courses/{course}/lecturers', [CourseController::class, 'storeLecturer'])
            ->name('courses.lecturers.store');
        Route::delete('courses/{course}/lecturers/{user}', [CourseController::class, 'removeLecturer'])
            ->name('courses.lecturers.destroy');
        Route::resource('class-sections', ClassSectionController::class);

        /*
        |--------------------------------------------------------------------------
        | Users & Roles
        |--------------------------------------------------------------------------
        */
        Route::resource('users', UserController::class)->only(['index']);
        Route::resource('roles', RoleController::class)->except(['show']);

        // Assign roles
        Route::get('users/{user}/roles', [UserRoleController::class, 'edit'])
            ->name('users.roles.edit');
        Route::post('users/{user}/roles', [UserRoleController::class, 'update'])
            ->name('users.roles.update');

        // Assign fakultas/prodi (scope)
        Route::get('users/{user}/scope', [UserScopeController::class, 'edit'])
            ->name('users.scope.edit');
        Route::post('users/{user}/scope', [UserScopeController::class, 'update'])
            ->name('users.scope.update');

        /*
        |--------------------------------------------------------------------------
        | Activity Logs
        |--------------------------------------------------------------------------
        */
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])
            ->name('activity-logs.index');
    });
});

/*
|--------------------------------------------------------------------------
| Auth scaffolding (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
