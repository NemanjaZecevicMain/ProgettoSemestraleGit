<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentAbsenceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user || $user->role !== 'STUDENT') {
            abort(403);
        }

        $status = $request->input('status', 'all');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Absence::query()
            ->where('student_id', $user->id)
            ->orderByDesc('date_from');

        if ($status !== 'all' && $status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($dateFrom) {
            $query->whereDate('date_from', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('date_to', '<=', $dateTo);
        }

        $absences = $query->paginate(10)->withQueryString();

        $statusOptions = [
            'PENDING' => 'In attesa',
            'WAITING_CERT' => 'Attesa certificato',
            'WAITING_SIGNATURE' => 'Attesa firma',
            'JUSTIFIED' => 'Giustificata',
            'UNJUSTIFIED' => 'Non giustificata',
        ];

        return view('student.absences.index', [
            'absences' => $absences,
            'statusOptions' => $statusOptions,
            'filters' => [
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $user = $request->user();
        if (!$user || $user->role !== 'STUDENT') {
            abort(403);
        }

        $absence = Absence::query()
            ->where('id', $id)
            ->where('student_id', $user->id)
            ->firstOrFail();

        return view('student.absences.show', [
            'absence' => $absence,
        ]);
    }
}
