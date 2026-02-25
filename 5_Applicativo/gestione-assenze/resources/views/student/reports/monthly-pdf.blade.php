<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Report Mensile</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 20px; margin: 0 0 8px; }
        h2 { font-size: 14px; margin: 18px 0 6px; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
        .summary { margin-top: 8px; }
    </style>
</head>
<body>
    <h1>Report mensile assenze e ritardi</h1>
    <div class="muted">Studente: {{ $student->name }}</div>
    <div class="muted">Mese: {{ $monthLabel }}</div>

    <div class="summary">
        Totale assenze: {{ $summary['absences_count'] }} |
        Totale ritardi: {{ $summary['delays_count'] }} |
        Minuti di ritardo: {{ $summary['delays_minutes'] }}
    </div>

    <h2>Assenze</h2>
    @if ($absences->isEmpty())
        <div class="muted">Nessuna assenza nel periodo.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Periodo</th>
                    <th>Motivo</th>
                    <th>Ore</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($absences as $absence)
                    <tr>
                        <td>{{ optional($absence->date_from)->format('d.m.Y') }} - {{ optional($absence->date_to)->format('d.m.Y') }}</td>
                        <td>{{ $absence->reason }}</td>
                        <td>{{ $absence->hours_assigned ?? '-' }}</td>
                        <td>{{ $absence->note ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Ritardi</h2>
    @if ($delays->isEmpty())
        <div class="muted">Nessun ritardo nel periodo.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Minuti</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($delays as $delay)
                    <tr>
                        <td>{{ optional($delay->date)->format('d.m.Y') }}</td>
                        <td>{{ $delay->minutes }}</td>
                        <td>{{ $delay->note ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
