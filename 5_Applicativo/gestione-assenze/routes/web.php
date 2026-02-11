<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudentAbsenceController;
use App\Http\Controllers\Student\StudentDelayController;
use App\Http\Controllers\Student\StudentSignatureController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings/description', [SettingsController::class, 'updateDescription'])->name('settings.description.update');
    Route::get('/assenze', [StudentAbsenceController::class, 'index'])->name('student.absences.index');
    Route::get('/assenze/{id}', [StudentAbsenceController::class, 'show'])->name('student.absences.show');
    Route::get('/ritardi', [StudentDelayController::class, 'index'])->name('student.delays.index');
    Route::get('/ritardi/{id}', [StudentDelayController::class, 'show'])->name('student.delays.show');
    Route::patch('/ritardi/{id}/firma', [StudentDelayController::class, 'sign'])->name('student.delays.sign');
    Route::get('/stato-firme', [StudentSignatureController::class, 'index'])->name('student.signatures.index');
});

require __DIR__.'/auth.php';
