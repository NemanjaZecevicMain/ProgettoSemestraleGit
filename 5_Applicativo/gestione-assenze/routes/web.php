<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudentAbsenceController;
use App\Http\Controllers\Student\StudentDelayController;
use App\Http\Controllers\Student\StudentSignatureController;
use App\Http\Controllers\Student\StudentReportController;
use App\Http\Controllers\Student\StudentCertificatesController;
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
    Route::get('/assenze/segnala', [StudentAbsenceController::class, 'create'])->name('student.absences.create');
    Route::post('/assenze', [StudentAbsenceController::class, 'store'])->name('student.absences.store');
    Route::get('/assenze/{id}', [StudentAbsenceController::class, 'show'])->name('student.absences.show');
    Route::patch('/assenze/{id}/firma', [StudentAbsenceController::class, 'sign'])->name('student.absences.sign');
    Route::get('/assenze/{id}/firma/pdf', [StudentAbsenceController::class, 'downloadSignature'])
        ->name('student.absences.signature.download');
    Route::post('/assenze/{id}/certificati/{slot}', [StudentAbsenceController::class, 'uploadCertificate'])
        ->name('student.absences.certificates.upload');
    Route::get('/assenze/{id}/certificati/{slot}', [StudentAbsenceController::class, 'downloadCertificate'])
        ->name('student.absences.certificates.download');
    Route::get('/ritardi', [StudentDelayController::class, 'index'])->name('student.delays.index');
    Route::get('/ritardi/{id}', [StudentDelayController::class, 'show'])->name('student.delays.show');
    Route::patch('/ritardi/{id}/firma', [StudentDelayController::class, 'sign'])->name('student.delays.sign');
    Route::get('/ritardi/{id}/firma/pdf', [StudentDelayController::class, 'downloadSignature'])
        ->name('student.delays.signature.download');
    Route::get('/certificati', [StudentCertificatesController::class, 'index'])
        ->name('student.certificates.index');
    Route::get('/report-mensili', [StudentReportController::class, 'index'])
        ->name('student.reports.index');
    Route::post('/report-mensili/genera', [StudentReportController::class, 'generate'])
        ->name('student.reports.generate');
    Route::post('/report-mensili/carica', [StudentReportController::class, 'upload'])
        ->name('student.reports.upload');
    Route::get('/report-mensili/{id}/pdf', [StudentReportController::class, 'download'])
        ->name('student.reports.download');
    Route::get('/stato-firme', [StudentSignatureController::class, 'index'])->name('student.signatures.index');
});

require __DIR__.'/auth.php';
