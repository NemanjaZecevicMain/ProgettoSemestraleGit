<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherStudentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('teacher.students.access')) {
            abort(403);
        }

        $filters = $request->only(['q', 'classroom_id', 'year', 'section']);
        if (!empty($filters['section'])) {
            $filters['section'] = strtoupper(trim($filters['section']));
        }
        if (!empty($filters['year'])) {
            $filters['year'] = (int) $filters['year'];
        }

        $studentsQuery = User::query()
            ->where('role', 'STUDENT')
            ->with('classroom');

        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $studentsQuery->where(function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%');
            });
        }

        if (!empty($filters['classroom_id'])) {
            $studentsQuery->where('classroom_id', $filters['classroom_id']);
        }

        if (!empty($filters['year']) || !empty($filters['section'])) {
            $studentsQuery->whereHas('classroom', function ($query) use ($filters) {
                if (!empty($filters['year'])) {
                    $query->where('year', $filters['year']);
                }
                if (!empty($filters['section'])) {
                    $query->where('section', $filters['section']);
                }
            });
        }

        $students = $studentsQuery
            ->orderBy('name')
            ->get();

        $classrooms = Classroom::query()
            ->orderBy('year')
            ->orderBy('section')
            ->orderBy('name')
            ->get();

        return view('teacher.students.index', [
            'students' => $students,
            'filters' => $filters,
            'classrooms' => $classrooms,
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('teacher.students.access')) {
            abort(403);
        }

        $student = User::query()
            ->where('id', $id)
            ->where('role', 'STUDENT')
            ->with('classroom', 'guardian')
            ->firstOrFail();

        return view('teacher.students.show', [
            'student' => $student,
        ]);
    }
}
