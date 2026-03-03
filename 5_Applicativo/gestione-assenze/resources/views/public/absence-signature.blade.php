@php
    $status = $status ?? 'invalid';
    $absence = $confirmation->absence ?? null;
    $student = $absence?->student;
@endphp

<x-guest-layout>
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Firma assenza</h1>
            <p class="text-sm text-slate-500">Firma digitale per confermare l'assenza.</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($status === 'invalid')
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                Link firma non valido.
            </div>
        @elseif ($status === 'expired')
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Il link firma e scaduto. Richiedi un nuovo link.
            </div>
        @elseif ($status === 'signed')
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                Questa assenza e gia stata firmata.
            </div>
        @else
            @if ($absence)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="text-xs uppercase tracking-wider text-slate-500">Dettagli assenza</div>
                    <div class="mt-2 text-sm text-slate-700">
                        @if ($student)
                            <div><span class="font-medium text-slate-900">Studente:</span> {{ $student->name }}</div>
                        @endif
                        <div><span class="font-medium text-slate-900">Periodo:</span> {{ optional($absence->date_from)->format('d.m.Y') }} &rarr; {{ optional($absence->date_to)->format('d.m.Y') }}</div>
                        <div><span class="font-medium text-slate-900">Motivo:</span> {{ $absence->reason ?? '-' }}</div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('public.absences.signature.store', $token) }}" class="space-y-4">
                @csrf
                <div>
                    <label for="signer_name" class="text-xs uppercase tracking-wider text-slate-500">Nome di chi firma</label>
                    <input id="signer_name" name="signer_name" value="{{ old('signer_name') }}" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div>
                    <label for="signer_email" class="text-xs uppercase tracking-wider text-slate-500">Email (opzionale)</label>
                    <input id="signer_email" name="signer_email" type="email" value="{{ old('signer_email') }}" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <div class="text-xs uppercase tracking-wider text-slate-500">Firma</div>
                    <div class="mt-2 rounded-xl border border-slate-200 bg-white p-3">
                        <canvas id="signatureCanvas" class="h-48 w-full touch-none rounded-lg border border-slate-100 bg-slate-50"></canvas>
                        <input type="hidden" id="signature_data" name="signature_data">
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <button type="button" id="clearSignature" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:border-slate-300">
                                Cancella firma
                            </button>
                            <span class="text-xs text-slate-500">Usa il mouse o il dito per firmare.</span>
                        </div>
                    </div>
                </div>

                <button type="submit" id="submitSignature" class="inline-flex w-full items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
                    Conferma firma
                </button>
            </form>
        @endif
    </div>

    @if ($status === 'ready')
        <script>
            const canvas = document.getElementById('signatureCanvas');
            const ctx = canvas.getContext('2d');
            let drawing = false;
            let hasDrawn = false;

            const resizeCanvas = () => {
                const rect = canvas.getBoundingClientRect();
                const ratio = window.devicePixelRatio || 1;
                canvas.width = rect.width * ratio;
                canvas.height = rect.height * ratio;
                ctx.setTransform(1, 0, 0, 1, 0, 0);
                ctx.scale(ratio, ratio);
                ctx.lineWidth = 2;
                ctx.lineCap = 'round';
                ctx.strokeStyle = '#0f172a';
                ctx.clearRect(0, 0, rect.width, rect.height);
            };

            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);

            const getPoint = (event) => {
                const rect = canvas.getBoundingClientRect();
                const clientX = event.touches ? event.touches[0].clientX : event.clientX;
                const clientY = event.touches ? event.touches[0].clientY : event.clientY;
                return {
                    x: clientX - rect.left,
                    y: clientY - rect.top,
                };
            };

            const startDrawing = (event) => {
                event.preventDefault();
                drawing = true;
                const { x, y } = getPoint(event);
                ctx.beginPath();
                ctx.moveTo(x, y);
            };

            const draw = (event) => {
                if (!drawing) return;
                event.preventDefault();
                const { x, y } = getPoint(event);
                ctx.lineTo(x, y);
                ctx.stroke();
                hasDrawn = true;
            };

            const stopDrawing = (event) => {
                if (!drawing) return;
                event.preventDefault();
                drawing = false;
            };

            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseleave', stopDrawing);
            canvas.addEventListener('touchstart', startDrawing, { passive: false });
            canvas.addEventListener('touchmove', draw, { passive: false });
            canvas.addEventListener('touchend', stopDrawing);

            document.getElementById('clearSignature').addEventListener('click', () => {
                const rect = canvas.getBoundingClientRect();
                ctx.clearRect(0, 0, rect.width, rect.height);
                hasDrawn = false;
            });

            const form = document.querySelector('form');
            form.addEventListener('submit', (event) => {
                if (!hasDrawn) {
                    event.preventDefault();
                    alert('Inserisci la firma prima di inviare.');
                    return;
                }
                const dataUrl = canvas.toDataURL('image/png');
                document.getElementById('signature_data').value = dataUrl;
            });
        </script>
    @endif
</x-guest-layout>
