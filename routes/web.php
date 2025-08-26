<?php

use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// RPS
Route::prefix('rps')->group(function () {
    Route::get('/', function () {
        return view('rps.index');
    })->name('rps.index');

    Route::get('/create', function () {
        return view('rps.create');
    })->name('rps.create');

    Route::post('/', function () {
        // nanti implementasi store
    })->name('rps.store');

    Route::get('/{id}', function ($id) {
        return view('rps.show', ['id' => $id]);
    })->name('rps.show');
});

// Review
Route::prefix('reviews')->group(function () {
    Route::get('/', function () {
        return view('reviews.index');
    })->name('reviews.index');

    Route::get('/{id}', function ($id) {
        return view('reviews.show', ['id' => $id]);
    })->name('reviews.show');
});

// Approval
Route::prefix('approvals')->group(function () {
    Route::get('/', function () {
        return view('approvals.index');
    })->name('approvals.index');

    Route::get('/{id}', function ($id) {
        return view('approvals.show', ['id' => $id]);
    })->name('approvals.show');
});
