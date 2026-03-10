<?php

namespace App\Http\Controllers\Audit;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user || !$this->canAccessAudit($user)) {
            abort(403);
        }

        $filters = [
            'action' => trim((string) $request->input('action', '')),
            'entity_type' => trim((string) $request->input('entity_type', '')),
            'actor' => trim((string) $request->input('actor', '')),
            'student' => trim((string) $request->input('student', '')),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $query = AuditLog::query()
            ->with('actor', 'absence.student')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($filters['action'] !== '') {
            $query->where('action', $filters['action']);
        }

        if ($filters['entity_type'] !== '') {
            $query->where('entity_type', $filters['entity_type']);
        }

        if ($filters['actor'] !== '') {
            $query->whereHas('actor', function ($actorQuery) use ($filters) {
                $actorQuery->where('name', 'like', '%' . $filters['actor'] . '%')
                    ->orWhere('email', 'like', '%' . $filters['actor'] . '%');
            });
        }

        if ($filters['student'] !== '') {
            $query->where('entity_type', 'absence')
                ->whereHas('absence.student', function ($studentQuery) use ($filters) {
                    $studentQuery->where('name', 'like', '%' . $filters['student'] . '%')
                        ->orWhere('email', 'like', '%' . $filters['student'] . '%');
                });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $actionOptions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $entityTypeOptions = AuditLog::query()
            ->select('entity_type')
            ->distinct()
            ->orderBy('entity_type')
            ->pluck('entity_type');

        return view('audit.logs.index', [
            'logs' => $query->paginate(25)->withQueryString(),
            'filters' => $filters,
            'actionOptions' => $actionOptions,
            'entityTypeOptions' => $entityTypeOptions,
        ]);
    }

    private function canAccessAudit($user): bool
    {
        return $user->hasPermission('teacher.classes.access')
            || $user->hasPermission('capolab.absence_approvals.access')
            || $user->hasPermission('direzione.absence_approvals.access');
    }
}
