@php
    $recipientName = $recipient->name ?? 'Utente';
@endphp

<p>Ciao {{ $recipientName }},</p>

<p>
    E stato richiesto di firmare un'assenza per lo studente {{ $student->name }}.
</p>

<p>
    Apri questo link per firmare:
    <br>
    <a href="{{ $signatureLink }}">{{ $signatureLink }}</a>
</p>

<p>Se non ti aspettavi questa email, puoi ignorarla.</p>
