<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use ZipArchive;

class TeacherClassController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('teacher.classes.access')) {
            abort(403);
        }

        $classroomsQuery = $user->hasGlobalInstituteVisibility()
            ? Classroom::query()
            : $user->taughtClassrooms();

        $classrooms = $classroomsQuery
            ->withCount([
                'users as students_count' => fn ($query) => $query->where('role', 'STUDENT'),
            ])
            ->orderBy('year')
            ->orderBy('section')
            ->orderBy('name')
            ->get();

        return view('teacher.classes.index', [
            'classrooms' => $classrooms,
            'canViewAll' => $user->hasGlobalInstituteVisibility(),
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('teacher.classes.access')) {
            abort(403);
        }

        if ($user->hasGlobalInstituteVisibility()) {
            $classroom = Classroom::query()
                ->where('id', $id)
                ->firstOrFail();
        } else {
            $classroom = $user->taughtClassrooms()
                ->where('classroom.id', $id)
                ->firstOrFail();
        }

        $students = $classroom->users()
            ->where('role', 'STUDENT')
            ->orderBy('name')
            ->get();

        return view('teacher.classes.show', [
            'classroom' => $classroom,
            'students' => $students,
        ]);
    }

    public function importCsv(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user || !$user->hasPermission('teacher.classes.access')) {
            abort(403);
        }

        if (!in_array($user->role, ['CAPOLAB', 'ADMIN'], true)) {
            abort(403);
        }

        $validated = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:2048'],
        ], [
            'csv_file.required' => 'Carica un file CSV o Excel.',
            'csv_file.file' => 'Il file caricato non è valido.',
            'csv_file.mimes' => 'Il file deve essere in formato CSV, TXT o XLSX.',
            'csv_file.max' => 'Il file non può superare 2MB.',
        ]);

        $filePath = $validated['csv_file']->getRealPath();
        if (!$filePath) {
            return redirect()
                ->route('teacher.classes.index')
                ->with('status', 'Import non riuscito: file temporaneo non disponibile.');
        }

        $extension = strtolower((string) $validated['csv_file']->getClientOriginalExtension());
        $rows = [];

        try {
            if ($extension === 'xlsx') {
                $rows = $this->readXlsxRows($filePath);
            } else {
                $delimiter = $this->detectCsvDelimiter($filePath);
                $rows = $this->readCsvRows($filePath, $delimiter);
            }
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('teacher.classes.index')
                ->with('status', 'Import non riuscito: file non leggibile o formato non supportato.');
        }

        $created = 0;
        $skipped = 0;
        $errors = [];
        $templateSectionColumn = null;

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $lineNumber = $index + 1;
                if (count(array_filter($row, static fn ($value) => $value !== '')) === 0) {
                    continue;
                }

                if ($lineNumber === 1) {
                    if ($this->isHeaderRow($row)) {
                        continue;
                    }

                    $templateSectionColumn = $this->findTemplateSectionColumn($row);
                    if ($templateSectionColumn !== null) {
                        continue;
                    }
                }

                $year = null;
                $name = null;
                $section = null;

                if ($templateSectionColumn !== null) {
                    $classCode = strtoupper(trim((string) ($row[$templateSectionColumn] ?? '')));
                    if ($classCode === '') {
                        $errors[] = "Riga {$lineNumber}: sezione mancante.";
                        continue;
                    }

                    $parsed = $this->parseClassCode($classCode);
                    if ($parsed === null) {
                        $errors[] = "Riga {$lineNumber}: sezione non valida ({$classCode}).";
                        continue;
                    }

                    [$year, $name, $section] = $parsed;
                } else {
                    if (count($row) < 3) {
                        $errors[] = "Riga {$lineNumber}: servono 3 colonne (year,name,section).";
                        continue;
                    }

                    $yearRaw = $row[0];
                    $name = strtoupper($row[1]);
                    $section = strtoupper($row[2]);

                    if (!ctype_digit($yearRaw)) {
                        $errors[] = "Riga {$lineNumber}: anno non valido.";
                        continue;
                    }

                    $year = (int) $yearRaw;
                    if ($year < 1 || $year > 20) {
                        $errors[] = "Riga {$lineNumber}: anno fuori intervallo (1-20).";
                        continue;
                    }
                }

                if ($name === '' || $section === '') {
                    $errors[] = "Riga {$lineNumber}: nome e sezione obbligatori.";
                    continue;
                }

                $existing = Classroom::query()
                    ->where('year', $year)
                    ->where('name', $name)
                    ->where('section', $section)
                    ->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                Classroom::create([
                    'year' => $year,
                    'name' => $name,
                    'section' => $section,
                ]);
                $created++;
            }
            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            report($exception);

            return redirect()
                ->route('teacher.classes.index')
                ->with('status', 'Import non riuscito: errore durante la lettura del CSV.');
        }

        $status = "Import completato: {$created} classi create, {$skipped} già esistenti.";
        if (count($errors) > 0) {
            $status .= ' Alcune righe sono state ignorate.';
        }

        return redirect()
            ->route('teacher.classes.index')
            ->with('status', $status)
            ->with('import_errors', $errors);
    }

    private function detectCsvDelimiter(string $filePath): string
    {
        $sample = file_get_contents($filePath, false, null, 0, 1024) ?: '';
        $semicolonCount = substr_count($sample, ';');
        $commaCount = substr_count($sample, ',');

        return $semicolonCount > $commaCount ? ';' : ',';
    }

    private function readCsvRows(string $filePath, string $delimiter): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Impossibile aprire il file CSV.');
        }

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = array_map(static fn ($value) => trim((string) $value), $row);
        }

        fclose($handle);
        return $rows;
    }

    private function readXlsxRows(string $filePath): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new \RuntimeException('Estensione ZIP non disponibile.');
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException('Impossibile aprire il file XLSX.');
        }

        try {
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($sheetXml === false) {
                throw new \RuntimeException('Foglio Excel non trovato.');
            }

            $sharedStrings = [];
            $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($sharedXml !== false) {
                $sharedRoot = @simplexml_load_string($sharedXml);
                if ($sharedRoot !== false) {
                    $sharedRoot->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                    $items = $sharedRoot->xpath('//x:si') ?: [];
                    foreach ($items as $item) {
                        $parts = $item->xpath('.//x:t') ?: [];
                        $text = '';
                        foreach ($parts as $part) {
                            $text .= (string) $part;
                        }
                        $sharedStrings[] = trim($text);
                    }
                }
            }

            $sheetRoot = @simplexml_load_string($sheetXml);
            if ($sheetRoot === false) {
                throw new \RuntimeException('Contenuto XLSX non valido.');
            }

            $sheetRoot->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $rowNodes = $sheetRoot->xpath('//x:sheetData/x:row') ?: [];
            $rows = [];

            foreach ($rowNodes as $rowNode) {
                $rowData = [];
                $cells = $rowNode->xpath('./x:c') ?: [];
                foreach ($cells as $cell) {
                    $ref = (string) ($cell['r'] ?? '');
                    $colIndex = $this->columnIndexFromReference($ref);
                    $type = (string) ($cell['t'] ?? '');
                    $value = '';

                    if ($type === 's') {
                        $raw = (string) ($cell->v ?? '');
                        $idx = ctype_digit($raw) ? (int) $raw : -1;
                        if ($idx >= 0 && $idx < count($sharedStrings)) {
                            $value = $sharedStrings[$idx];
                        }
                    } elseif ($type === 'inlineStr') {
                        $value = trim((string) ($cell->is->t ?? ''));
                    } else {
                        $value = trim((string) ($cell->v ?? ''));
                    }

                    if ($colIndex >= 0) {
                        $rowData[$colIndex] = trim($value);
                    }
                }

                if (count($rowData) > 0) {
                    ksort($rowData);
                    $rows[] = array_values($rowData);
                } else {
                    $rows[] = [];
                }
            }

            return $rows;
        } finally {
            $zip->close();
        }
    }

    private function columnIndexFromReference(string $reference): int
    {
        if ($reference === '' || !preg_match('/^[A-Z]+/i', $reference, $matches)) {
            return -1;
        }

        $letters = strtoupper($matches[0]);
        $index = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - ord('A') + 1);
        }

        return $index - 1;
    }

    private function isHeaderRow(array $row): bool
    {
        $first = strtolower(trim((string) ($row[0] ?? '')));
        $second = strtolower(trim((string) ($row[1] ?? '')));
        $third = strtolower(trim((string) ($row[2] ?? '')));

        return in_array($first, ['year', 'anno'], true)
            && in_array($second, ['name', 'classe', 'nome'], true)
            && in_array($third, ['section', 'sezione'], true);
    }

    private function findTemplateSectionColumn(array $row): ?int
    {
        foreach ($row as $index => $value) {
            $normalized = strtolower(trim((string) $value));
            if ($normalized === 'sezione' || $normalized === 'section') {
                return (int) $index;
            }
        }

        return null;
    }

    private function parseClassCode(string $classCode): ?array
    {
        if (!preg_match('/^([A-Z]+)?([0-9]+)([A-Z]+)$/', $classCode, $matches)) {
            return null;
        }

        $name = $matches[1] ?? '';
        $year = (int) ($matches[2] ?? 0);
        $section = $matches[3] ?? '';

        if ($year < 1 || $year > 20 || $section === '') {
            return null;
        }

        if ($name === '') {
            $name = 'CLS';
        }

        return [$year, $name, $section];
    }
}
