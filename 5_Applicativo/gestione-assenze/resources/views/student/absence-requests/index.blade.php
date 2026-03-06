<x-app-sidebar>
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Le mie richieste di assenza</h1>
            <p class="text-sm text-slate-500">Stato approvazione delle richieste inviate.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @if ($requests->isEmpty())
                <div class="text-sm text-slate-500">Nessuna richiesta presente.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Periodo</th>
                                <th class="px-4 py-3 text-left font-medium">Motivazione</th>
                                <th class="px-4 py-3 text-left font-medium">Da approvare da</th>
                                <th class="px-4 py-3 text-left font-medium">Esito</th>
                                <th class="px-4 py-3 text-left font-medium">Decisione</th>
                                <th class="px-4 py-3 text-right font-medium">Dettaglio</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach ($requests as $requestRow)
                                @php
                                    $expectedApprover = $requestRow->requiredApproverRole() === 'CAPOLAB' ? 'Capolaboratorio' : 'Direzione';
                                    $decision = $requestRow->is_approved === null
                                        ? 'In valutazione'
                                        : ($requestRow->is_approved ? 'Approvata' : 'Rifiutata');
                                    $decisionClass = $requestRow->is_approved === null
                                        ? 'border-slate-200 bg-slate-100 text-slate-700'
                                        : ($requestRow->is_approved ? 'border-emerald-200 bg-emerald-100 text-emerald-800' : 'border-rose-200 bg-rose-100 text-rose-800');
                                    $decisionBy = $requestRow->approvedBy?->name
                                        ? $requestRow->approvedBy->name . ' - ' . optional($requestRow->approved_at)->format('d.m.Y H:i')
                                        : '-';
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-slate-900">
                                        {{ optional($requestRow->date_from)->format('d.m.Y') }} - {{ optional($requestRow->date_to)->format('d.m.Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">{{ $requestRow->reason }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $expectedApprover }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium {{ $decisionClass }}">
                                            {{ $decision }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">{{ $decisionBy }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('student.absences.show', $requestRow->id) }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
                                            Apri
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-sidebar>

