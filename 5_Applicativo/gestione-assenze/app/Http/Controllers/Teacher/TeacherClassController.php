<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherClassController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('teacher.classes.access')) {
            abort(403);
        }

        $classrooms = $user->taughtClassrooms()
            ->withCount([
                'users as students_count' => fn ($query) => $query->where('role', 'STUDENT'),
            ])
            ->orderBy('year')
            ->orderBy('section')
            ->get();

        return view('teacher.classes.index', [
            'classrooms' => $classrooms,
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('teacher.classes.access')) {
            abort(403);
        }

        $classroom = $user->taughtClassrooms()
            ->where('classroom.id', $id)
            ->firstOrFail();

        $students = $classroom->users()
            ->where('role', 'STUDENT')
            ->orderBy('name')
            ->get();

        return view('teacher.classes.show', [
            'classroom' => $classroom,
            'students' => $students,
        ]);
    }
}
