<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Mail\MonthlyReportMail;
use App\Models\Absence;
use App\Models\Delay;
use App\Models\MonthlyReport;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\View\View;

class StudentReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user || $user->role !== 'STUDENT') {
            abort(403);
        }

        $reports = MonthlyReport::query()
            ->where('student_id', $user->id)
            ->orderByDesc('month')
            ->get();

        return view('student.reports.index', [
            'reports' => $reports,
        ]);
    }

    public function upload(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user || $user->role !== 'STUDENT') {
            abort(403);
        }

        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'report_file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'month.required' => 'Seleziona il mese.',
            'month.date_format' => 'Formato mese non valido.',
            'report_file.required' => 'Carica il PDF del report.',
            'report_file.mimes' => 'Il file deve essere un PDF.',
            'report_file.max' => 'Il file non puo superare 5MB.',
        ]);

        $month = $validated['month'];

        $existing = MonthlyReport::query()
            ->where('student_id', $user->id)
            ->where('month', $month)
            ->first();

        if ($existing && $existing->file_path) {
            Storage::disk('public')->delete($existing->file_path);
        }

        $path = $validated['report_file']
            ->store('reports/monthly/' . $user->id, 'public');

        $report = MonthlyReport::updateOrCreate(
            ['student_id' => $user->id, 'month' => $month],
            [
                'file_path' => $path,
                'generated_at' => now(),
            ]
        );

        $this->emailReportToTeachers($user, $report, Storage::disk('public')->path($path));

        return redirect()
            ->route('student.reports.index')
            ->with('status', 'Report caricato e inviato ai docenti.');
    }

    public function generate(Request $request): Response
    {
        $user = $request->user();
        if (!$user || $user->role !== 'STUDENT') {
            abort(403);
        }

        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ], [
            'month.required' => 'Seleziona il mese.',
            'month.date_format' => 'Formato mese non valido.',
        ]);

        $month = $validated['month'];
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $absences = Absence::query()
            ->where('student_id', $user->id)
            ->whereDate('date_from', '<=', $end)
            ->whereDate('date_to', '>=', $start)
            ->orderBy('date_from')
            ->get();

        $delays = Delay::query()
            ->where('student_id', $user->id)
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->orderBy('date')
            ->get();

        $pdf = Pdf::loadView('student.reports.monthly-pdf', [
            'student' => $user,
            'monthLabel' => $start->format('m/Y'),
            'absences' => $absences,
            'delays' => $delays,
            'summary' => [
                'absences_count' => $absences->count(),
                'delays_count' => $delays->count(),
                'delays_minutes' => $delays->sum('minutes'),
            ],
        ])->setPaper('a4');

        $fileName = 'report_' . $user->id . '_' . $month . '.pdf';
        $path = 'reports/monthly/' . $user->id . '/' . $fileName;

        Storage::disk('public')->put($path, $pdf->output());

        $report = MonthlyReport::updateOrCreate(
            ['student_id' => $user->id, 'month' => $month],
            [
                'file_path' => $path,
                'generated_at' => now(),
            ]
        );

        $this->emailReportToTeachers($user, $report, Storage::disk('public')->path($path));

        return response()->file(Storage::disk('public')->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function download(Request $request, int $id): Response
    {
        $user = $request->user();
        if (!$user || $user->role !== 'STUDENT') {
            abort(403);
        }

        $report = MonthlyReport::query()
            ->where('id', $id)
            ->where('student_id', $user->id)
            ->firstOrFail();

        $disk = Storage::disk('public');
        if (!$disk->exists($report->file_path)) {
            abort(404);
        }

        return response()->file($disk->path($report->file_path), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function emailReportToTeachers(User $student, MonthlyReport $report, string $absolutePath): void
    {
        $emails = User::query()
            ->where('role', 'TEACHER')
            ->pluck('email')
            ->filter()
            ->all();

        if (!$emails) {
            return;
        }

        Mail::to($emails)->send(new MonthlyReportMail($student, $report, $absolutePath));

        $report->sent_at = now();
        $report->save();
    }
}
