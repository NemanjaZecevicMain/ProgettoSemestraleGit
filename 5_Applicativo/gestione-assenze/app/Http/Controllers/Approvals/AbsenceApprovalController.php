<?php

namespace App\Http\Controllers\Approvals;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbsenceApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $requiredRole = $this->requiredApproverRoleForUser($user);
        if (!$requiredRole) {
            abort(403);
        }

        $filters = [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'approval' => $request->input('approval', 'all'),
            'reason' => $request->input('reason', 'all'),
            'status' => $request->input('status', 'all'),
            'student' => trim((string) $request->input('student', '')),
        ];

        $query = Absence::query()
            ->with('student')
            ->with('approvedBy')
            ->orderByDesc('date_from')
            ->orderByDesc('id');

        $this->scopeByApproverRole($query, $requiredRole);

        if (!empty($filters['date_from'])) {
            $query->whereDate('date_from', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date_to', '<=', $filters['date_to']);
        }

        if ($filters['approval'] === 'pending') {
            $query->whereNull('is_approved');
        } elseif ($filters['approval'] === 'approved') {
            $query->where('is_approved', true);
        } elseif ($filters['approval'] === 'rejected') {
            $query->where('is_approved', false);
        }

        if ($filters['reason'] !== 'all' && $filters['reason'] !== '') {
            $query->where('reason', $filters['reason']);
        }

        if ($filters['status'] !== 'all' && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if ($filters['student'] !== '') {
            $query->whereHas('student', function ($studentQuery) use ($filters) {
                $studentQuery->where('name', 'like', '%' . $filters['student'] . '%');
            });
        }

        return view('approvals.absences.index', [
            'absences' => $query->paginate(15)->withQueryString(),
            'requiredRole' => $requiredRole,
            'filters' => $filters,
            'reasonOptions' => [
                'MALATTIA' => 'Malattia',
                'VISITA_MEDICA' => 'Visita medica',
                'IMPEGNO_FAMIGLIARE' => 'Impegno famigliare',
                'MOTIVI_PERSONALI' => 'Motivi personali',
                'ALTRO' => 'Altro',
            ],
            'statusOptions' => [
                'PENDING' => 'In attesa',
                'WAITING_CERT' => 'Attesa certificato',
                'WAITING_SIGNATURE' => 'Attesa firma',
                'JUSTIFIED' => 'Giustificata',
                'UNJUSTIFIED' => 'Non giustificata',
            ],
        ]);
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        return $this->decide($request, $id, true);
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        return $this->decide($request, $id, false);
    }

    private function decide(Request $request, int $id, bool $approved): RedirectResponse
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $requiredRole = $this->requiredApproverRoleForUser($user);
        if (!$requiredRole) {
            abort(403);
        }

        $absence = Absence::query()->with('student')->findOrFail($id);

        if ($absence->status !== 'PENDING' || $absence->is_approved !== null) {
            return redirect()
                ->route('approvals.absences.index')
                ->with('status', 'Richiesta non piu approvabile.');
        }

        if ($absence->requiredApproverRole() !== $requiredRole) {
            return redirect()
                ->route('approvals.absences.index')
                ->with('status', 'Non autorizzato: richiesta assegnata a un altro ruolo.');
        }

        $absence->is_approved = $approved;
        $absence->approved_by_user_id = $user->id;
        $absence->approved_at = now();
        $absence->status = $approved ? 'WAITING_SIGNATURE' : 'UNJUSTIFIED';
        $absence->save();

        return redirect()
            ->route('approvals.absences.index')
            ->with('status', $approved ? 'Richiesta approvata.' : 'Richiesta rifiutata.');
    }

    private function requiredApproverRoleForUser($user): ?string
    {
        if ($user->hasPermission('capolab.absence_approvals.access')) {
            return 'CAPOLAB';
        }

        if ($user->hasPermission('direzione.absence_approvals.access')) {
            return 'DIREZIONE';
        }

        return null;
    }

    private function scopeByApproverRole($query, string $requiredRole): void
    {
        if ($requiredRole === 'CAPOLAB') {
            $query->whereColumn('date_from', 'date_to');
            return;
        }

        $query->whereColumn('date_from', '<', 'date_to');
    }
}
