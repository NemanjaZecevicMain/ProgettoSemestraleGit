<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentCertificatesController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user || $user->role !== 'STUDENT') {
            abort(403);
        }

        $absences = Absence::query()
            ->with('certificates')
            ->where('student_id', $user->id)
            ->orderByDesc('date_from')
            ->paginate(10);

        $statusOptions = [
            'PENDING' => 'In attesa',
            'WAITING_CERT' => 'Attesa certificato',
            'WAITING_SIGNATURE' => 'Attesa firma',
            'JUSTIFIED' => 'Giustificata',
            'UNJUSTIFIED' => 'Non giustificata',
        ];

        return view('student.certificates.index', [
            'absences' => $absences,
            'statusOptions' => $statusOptions,
        ]);
    }
}
