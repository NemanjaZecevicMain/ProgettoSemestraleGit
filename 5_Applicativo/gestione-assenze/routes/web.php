<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudentAbsenceController;
use App\Http\Controllers\Student\StudentDelayController;
use App\Http\Controllers\Student\StudentSignatureController;
use App\Http\Controllers\Student\StudentReportController;
use App\Http\Controllers\Student\StudentCertificatesController;
use App\Http\Controllers\Student\StudentAbsenceRequestController;
use App\Http\Controllers\Guardian\GuardianAbsenceController;
use App\Http\Controllers\SignatureConfirmationController;
use App\Http\Controllers\Teacher\TeacherStudentController;
use App\Http\Controllers\Teacher\TeacherClassController;
use App\Http\Controllers\Approvals\AbsenceApprovalController;
use App\Http\Controllers\Audit\AuditLogController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminRolePermissionController;
use App\Http\Controllers\Admin\AdminSystemSettingController;
use App\Http\Controllers\Admin\AdminAuditManagementController;
use App\Models\Absence;
use App\Models\Delay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (Request $request) {
    $user = $request->user();
    if ($user) {
        $user->loadMissing('classroom');
    }

    $studentStats = null;
    if ($user && $user->role === 'STUDENT') {
        $studentStats = [
            'absence_hours' => (int) Absence::query()
                ->where('student_id', $user->id)
                ->sum('hours_assigned'),
            'absences_count' => Absence::query()
                ->where('student_id', $user->id)
                ->count(),
            'delays_count' => Delay::query()
                ->where('student_id', $user->id)
                ->count(),
            'delay_minutes' => (int) Delay::query()
                ->where('student_id', $user->id)
                ->sum('minutes'),
        ];
    }

    return view('dashboard', [
        'user' => $user,
        'studentStats' => $studentStats,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/firma/assenza/{token}', [SignatureConfirmationController::class, 'show'])
    ->name('public.absences.signature.show');
Route::post('/firma/assenza/{token}', [SignatureConfirmationController::class, 'store'])
    ->name('public.absences.signature.store');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings/description', [SettingsController::class, 'updateDescription'])->name('settings.description.update');
    Route::get('/assenze', [StudentAbsenceController::class, 'index'])->name('student.absences.index');
    Route::get('/assenze/richieste', [StudentAbsenceRequestController::class, 'index'])->name('student.absence_requests.index');
    Route::get('/assenze/segnala', [StudentAbsenceController::class, 'create'])->name('student.absences.create');
    Route::post('/assenze', [StudentAbsenceController::class, 'store'])->name('student.absences.store');
    Route::get('/assenze/{id}', [StudentAbsenceController::class, 'show'])->name('student.absences.show');
    Route::post('/assenze/{id}/firma/link', [StudentAbsenceController::class, 'generateSignatureLink'])
        ->name('student.absences.signature.link');
    Route::get('/assenze/{id}/firma/pdf', [StudentAbsenceController::class, 'downloadSignature'])
        ->name('student.absences.signature.download');
    Route::post('/assenze/{id}/certificati/{slot}', [StudentAbsenceController::class, 'uploadCertificate'])
        ->name('student.absences.certificates.upload');
    Route::get('/assenze/{id}/certificati/{slot}', [StudentAbsenceController::class, 'downloadCertificate'])
        ->name('student.absences.certificates.download');
    Route::get('/ritardi', [StudentDelayController::class, 'index'])->name('student.delays.index');
    Route::get('/ritardi/{id}', [StudentDelayController::class, 'show'])->name('student.delays.show');
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

    Route::get('/tutore/assenze', [GuardianAbsenceController::class, 'index'])->name('guardian.absences.index');
    Route::get('/tutore/assenze/{id}', [GuardianAbsenceController::class, 'show'])->name('guardian.absences.show');
    Route::post('/tutore/assenze/{id}/firma/link', [GuardianAbsenceController::class, 'generateSignatureLink'])
        ->name('guardian.absences.signature.link');
    Route::get('/tutore/assenze/{id}/firma/pdf', [GuardianAbsenceController::class, 'downloadSignature'])
        ->name('guardian.absences.signature.download');
    Route::get('/tutore/assenze/{id}/certificati/{slot}', [GuardianAbsenceController::class, 'downloadCertificate'])
        ->name('guardian.absences.certificates.download');

    Route::get('/docente/studenti', [TeacherStudentController::class, 'index'])->name('teacher.students.index');
    Route::get('/docente/studenti/{id}', [TeacherStudentController::class, 'show'])->name('teacher.students.show');
    Route::get('/docente/classi', [TeacherClassController::class, 'index'])->name('teacher.classes.index');
    Route::post('/docente/classi/import', [TeacherClassController::class, 'importCsv'])->name('teacher.classes.import');
    Route::get('/docente/classi/{id}', [TeacherClassController::class, 'show'])->name('teacher.classes.show');

    Route::get('/approvazioni/assenze', [AbsenceApprovalController::class, 'index'])
        ->name('approvals.absences.index');
    Route::post('/approvazioni/assenze/{id}/approva', [AbsenceApprovalController::class, 'approve'])
        ->name('approvals.absences.approve');
    Route::post('/approvazioni/assenze/{id}/rifiuta', [AbsenceApprovalController::class, 'reject'])
        ->name('approvals.absences.reject');
    Route::get('/storico/audit', [AuditLogController::class, 'index'])
        ->name('audit.logs.index');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.users.index');
        })->name('index');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{id}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{id}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        Route::get('/roles-permissions', [AdminRolePermissionController::class, 'index'])->name('roles-permissions.index');
        Route::patch('/roles-permissions/{roleId}', [AdminRolePermissionController::class, 'update'])->name('roles-permissions.update');

        Route::get('/settings', [AdminSystemSettingController::class, 'index'])->name('settings.index');
        Route::patch('/settings', [AdminSystemSettingController::class, 'update'])->name('settings.update');

        Route::get('/audit', [AdminAuditManagementController::class, 'index'])->name('audit.index');
        Route::get('/audit/export', [AdminAuditManagementController::class, 'export'])->name('audit.export');
    });
});

require __DIR__.'/auth.php';
