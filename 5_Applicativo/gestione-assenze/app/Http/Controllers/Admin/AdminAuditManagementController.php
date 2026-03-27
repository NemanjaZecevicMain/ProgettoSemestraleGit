<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AuthorizesAdmin;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Controller responsabile della gestione dei log di audit nell'area amministrativa.
 * Permette agli amministratori di:
 * - visualizzare i log
 * - esportarli in formato CSV
 */
class AdminAuditManagementController extends Controller
{
    use AuthorizesAdmin;

    /**
     * Mostra la lista dei log di audit con eventuali filtri applicati.
     * I risultati sono paginati per migliorare le prestazioni della pagina.
     */
    public function index(Request $request): View
    {
        // Verifica che l'utente autenticato sia un amministratore
        $this->ensureAdmin($request->user());

        // Recupera i filtri inviati tramite request (form o query string)
        $filters = $this->filters($request);

        // Costruisce la query applicando i filtri
        $query = $this->buildQuery($filters);

        // Ritorna la view con:
        // - i log paginati
        // - i filtri attuali
        // - le opzioni disponibili per action ed entity_type
        return view('admin.audit.index', [
            'logs' => $query->paginate(25)->withQueryString(),
            'filters' => $filters,
            'actionOptions' => AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
            'entityTypeOptions' => AuditLog::query()->select('entity_type')->distinct()->orderBy('entity_type')->pluck('entity_type'),
        ]);
    }

    /**
     * Esporta i log di audit filtrati in un file CSV scaricabile.
     * Viene limitato a 5000 record per evitare esportazioni troppo pesanti.
     */
    public function export(Request $request): Response
    {
        $admin = $request->user();

        // Controllo che l'utente sia amministratore
        $this->ensureAdmin($admin);

        // Recupero dei filtri dalla request
        $filters = $this->filters($request);

        // Costruzione della query e recupero dei log
        $logs = $this->buildQuery($filters)->limit(5000)->get();

        // Generazione del nome del file CSV con timestamp
        $fileName = 'audit_export_' . now()->format('Ymd_His') . '.csv';

        // Headers HTTP per forzare il download del file
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        // Registrazione nel sistema di audit dell'operazione di export
        AuditLogger::log($admin, 'admin.audit.exported', 'audit_log', null, [
            'filters' => $filters,
            'rows' => $logs->count(),
        ]);

        // Stream della risposta per generare il CSV senza caricare tutto in memoria
        return response()->stream(function () use ($logs) {

            // Apertura dello stream di output
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // Inserisce il BOM UTF-8 per compatibilità con Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Intestazione delle colonne del CSV
            fputcsv($handle, ['id', 'created_at', 'actor', 'action', 'entity_type', 'entity_id', 'metadata']);

            // Scrittura di ogni log come riga CSV
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    optional($log->created_at)->format('Y-m-d H:i:s'),
                    $log->actor?->email ?? 'system',
                    $log->action,
                    $log->entity_type,
                    $log->entity_id,
                    json_encode($log->metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ]);
            }

            // Chiusura dello stream
            fclose($handle);

        }, 200, $headers);
    }

    /**
     * Recupera e normalizza i filtri dalla request.
     * Serve per centralizzare la gestione dei parametri di ricerca.
     */
    private function filters(Request $request): array
    {
        return [
            'action' => trim((string) $request->input('action', '')),
            'entity_type' => trim((string) $request->input('entity_type', '')),
            'actor' => trim((string) $request->input('actor', '')),
            'date_from' => (string) $request->input('date_from', ''),
            'date_to' => (string) $request->input('date_to', ''),
        ];
    }

    /**
     * Costruisce la query per recuperare i log applicando i filtri selezionati.
     */
    private function buildQuery(array $filters)
    {
        // Query base con relazione actor e ordinamento decrescente
        $query = AuditLog::query()
            ->with('actor')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        // Filtro per tipo di azione
        if ($filters['action'] !== '') {
            $query->where('action', $filters['action']);
        }

        // Filtro per tipo di entità
        if ($filters['entity_type'] !== '') {
            $query->where('entity_type', $filters['entity_type']);
        }

        // Filtro per attore (nome o email)
        if ($filters['actor'] !== '') {
            $query->whereHas('actor', function ($actorQuery) use ($filters) {
                $actorQuery->where('name', 'like', '%' . $filters['actor'] . '%')
                    ->orWhere('email', 'like', '%' . $filters['actor'] . '%');
            });
        }

        // Filtro per data iniziale
        if ($filters['date_from'] !== '') {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        // Filtro per data finale
        if ($filters['date_to'] !== '') {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }
}
