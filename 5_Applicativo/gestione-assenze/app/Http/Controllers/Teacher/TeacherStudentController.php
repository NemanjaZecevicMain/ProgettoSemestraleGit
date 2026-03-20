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

        $canViewAll = $user->hasGlobalInstituteVisibility();
        $filters = $request->only(['q', 'classroom_id', 'year', 'section', 'role']);
        if (!empty($filters['section'])) {
            $filters['section'] = strtoupper(trim($filters['section']));
        }
        if (!empty($filters['year'])) {
            $filters['year'] = (int) $filters['year'];
        }
        if (!empty($filters['role'])) {
            $filters['role'] = strtoupper(trim($filters['role']));
        }

        $studentsQuery = User::query()
            ->with('classroom');

        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $studentsQuery->where(function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%')
                    ->orWhere('role', 'like', '%' . strtoupper($q) . '%');
            });
        }

        if (!$canViewAll) {
            $classroomIds = $user->taughtClassrooms()->pluck('classroom.id');
            $studentsQuery
                ->where('role', 'STUDENT')
                ->whereIn('classroom_id', $classroomIds);
        } elseif (!empty($filters['role'])) {
            $studentsQuery->where('role', $filters['role']);
        }

        if (!empty($filters['classroom_id'])) {
            $studentsQuery->where('classroom_id', (int) $filters['classroom_id']);
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

        $classroomsQuery = $canViewAll
            ? Classroom::query()
            : $user->taughtClassrooms();

        $classrooms = $classroomsQuery
            ->orderBy('year')
            ->orderBy('section')
            ->orderBy('name')
            ->get();

        return view('teacher.students.index', [
            'students' => $students,
            'filters' => $filters,
            'classrooms' => $classrooms,
            'canViewAllUsers' => $canViewAll,
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('teacher.students.access')) {
            abort(403);
        }

        $studentQuery = User::query()
            ->where('id', $id)
            ->with('classroom', 'guardian')
            ->orderBy('name');

        if (!$user->hasGlobalInstituteVisibility()) {
            $classroomIds = $user->taughtClassrooms()->pluck('classroom.id');
            $studentQuery
                ->where('role', 'STUDENT')
                ->whereIn('classroom_id', $classroomIds);
        }

        $student = $studentQuery->firstOrFail();

        return view('teacher.students.show', [
            'student' => $student,
            'canViewAllUsers' => $user->hasGlobalInstituteVisibility(),
        ]);
    }
}
