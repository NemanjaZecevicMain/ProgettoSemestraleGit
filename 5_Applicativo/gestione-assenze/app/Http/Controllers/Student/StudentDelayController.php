<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Delay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDelayController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user || $user->role !== 'STUDENT') {
            abort(403);
        }

        $signed = $request->input('firmato', 'all');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Delay::query()
            ->where('student_id', $user->id)
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($signed === 'firmati') {
            $query->where('is_signed', true);
        } elseif ($signed === 'da_firmare') {
            $query->where('is_signed', false);
        } else {
            $signed = 'all';
        }

        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }

        $delays = $query->paginate(10)->withQueryString();

        return view('student.delays.index', [
            'delays' => $delays,
            'filters' => [
                'firmato' => $signed,
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

        $delay = Delay::query()
            ->where('id', $id)
            ->where('student_id', $user->id)
            ->firstOrFail();

        return view('student.delays.show', [
            'delay' => $delay,
        ]);
    }

    public function sign(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$user || $user->role !== 'STUDENT') {
            abort(403);
        }

        $delay = Delay::query()
            ->where('id', $id)
            ->where('student_id', $user->id)
            ->firstOrFail();

        if (!$delay->is_signed) {
            $delay->is_signed = true;
            $delay->signed_at = now();
            $delay->signed_by_user_id = $user->id;
            $delay->save();
        }

        return redirect()
            ->route('student.delays.index', $request->only(['firmato', 'date_from', 'date_to', 'page']))
            ->with('status', 'Ritardo firmato con successo.');
    }
}
