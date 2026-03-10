@php
    $recipientName = $recipient->name ?? 'Utente';
@endphp

<p>Ciao {{ $recipientName }},</p>

<p>
    e stata creata una nuova richiesta di assenza da valutare.
</p>

<p>
    <strong>Studente:</strong> {{ $student->name }}<br>
    <strong>Periodo:</strong> {{ optional($absence->date_from)->format('d.m.Y') }} - {{ optional($absence->date_to)->format('d.m.Y') }}<br>
    <strong>Motivo:</strong> {{ $absence->reason }}<br>
    <strong>Destinatario ruolo:</strong> {{ $targetRole }}
</p>

<p>
    Apri il pannello approvazioni:
    <br>
    <a href="{{ $approvalsUrl }}">{{ $approvalsUrl }}</a>
</p>
