<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Delay;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentSignatureController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user || $user->role !== 'STUDENT') {
            abort(403);
        }

        $delaysQuery = Delay::query()->where('student_id', $user->id);
        $absencesQuery = Absence::query()->where('student_id', $user->id);

        $summary = [
            'delays_unsigned' => (clone $delaysQuery)->where('is_signed', false)->count(),
            'delays_signed' => (clone $delaysQuery)->where('is_signed', true)->count(),
            'absences_waiting_signature' => (clone $absencesQuery)->where('status', 'WAITING_SIGNATURE')->count(),
        ];

        $delays = (clone $delaysQuery)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->take(10)
            ->get();

        $absences = (clone $absencesQuery)
            ->orderByDesc('date_from')
            ->take(10)
            ->get();

        $statusOptions = [
            'PENDING' => 'In attesa',
            'WAITING_CERT' => 'Attesa certificato',
            'WAITING_SIGNATURE' => 'Attesa firma',
            'JUSTIFIED' => 'Giustificata',
            'UNJUSTIFIED' => 'Non giustificata',
        ];

        return view('student.signatures.index', [
            'delays' => $delays,
            'absences' => $absences,
            'statusOptions' => $statusOptions,
            'summary' => $summary,
        ]);
    }
}
