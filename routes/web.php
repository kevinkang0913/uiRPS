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

use App\Http\Controllers\FacultyController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ClassSectionController;

use App\Http\Controllers\AcademicApiController;
use App\Http\Controllers\Admin\ImportController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

// Dashboard (login + verified)
Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware(['auth','verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Authenticated area
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |----------------------------------------------------------------------
    | Sidebar: Admin – Import & Reports
    |----------------------------------------------------------------------
    */
    Route::get('/admin/import/courses', [ImportController::class, 'showForm'])->name('admin.import.courses.form');
    Route::post('/admin/import/courses/import', [ImportController::class, 'import'])->name('admin.import.courses.real');
    Route::get('/admin/import/courses/report', [ImportController::class, 'downloadReport'])->name('admin.import.courses.report');

    // Placeholder export (agar link sidebar tidak error)
    Route::get('/admin/reports/export', fn () => 'Coming soon')->name('reports.export');

    /*
    |----------------------------------------------------------------------
    | Master Data (CRUD)
    |----------------------------------------------------------------------
    */
    Route::resource('faculties', FacultyController::class);
    Route::resource('programs', ProgramController::class);
    Route::resource('courses',  CourseController::class);
    Route::resource('class-sections', ClassSectionController::class);

    /*
    |----------------------------------------------------------------------
    | AJAX / API (untuk dependent dropdown dsb.) - dipisah prefix /api
    |----------------------------------------------------------------------
    */
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/faculties', [AcademicApiController::class, 'faculties'])->name('faculties');
        Route::get('/programs/{faculty}', [AcademicApiController::class, 'programsByFaculty'])->name('programs.byFaculty');
        Route::get('/courses/{program}', [AcademicApiController::class, 'coursesByProgram'])->name('courses.byProgram');
        Route::get('/sections/{course}', [AcademicApiController::class, 'sectionsByCourse'])->name('sections.byCourse');
    });
     Route::get('/admin/import/courses', [ImportController::class, 'showForm'])->name('admin.import.courses.form');
    Route::post('/admin/import/courses/import', [ImportController::class, 'import'])->name('admin.import.courses.real');
    /*
    |----------------------------------------------------------------------
    | RPS + Workflow (Reviews & Approvals)
    |----------------------------------------------------------------------
    */
    // Step-by-step routes (tambahan khusus, di luar resource)
    Route::get('/rps/create/step/{step}', [RpsController::class, 'createStep'])->name('rps.create.step');
    Route::post('/rps/create/step/{step}', [RpsController::class, 'storeStep'])->name('rps.store.step');

    // Resource utama RPS (cukup sekali, tanpa duplikasi)
    Route::resource('rps', RpsController::class);

    // Reviews (CTL)
    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::get('reviews/{rps}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::post('reviews/{rps}', [ReviewController::class, 'store'])->name('reviews.store');

    // Approvals (Kaprodi)
    Route::get('approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('approvals/{rps}/edit', [ApprovalController::class, 'edit'])->name('approvals.edit');
    Route::post('approvals/{rps}', [ApprovalController::class, 'store'])->name('approvals.store');

    /*
    |----------------------------------------------------------------------
    | Users & Roles
    |----------------------------------------------------------------------
    */
    Route::resource('users', UserController::class)->only(['index']);
    Route::resource('roles', RoleController::class)->except(['show']);

    // Assign Role ke User
    Route::get('users/{user}/roles', [UserRoleController::class, 'edit'])->name('users.roles.edit');
    Route::post('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');

    /*
    |----------------------------------------------------------------------
    | Activity Logs (view)
    |----------------------------------------------------------------------
    */
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
});

/*
|--------------------------------------------------------------------------
| Auth scaffolding (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
