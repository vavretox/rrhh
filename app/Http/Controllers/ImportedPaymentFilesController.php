<?php

namespace App\Http\Controllers;

use App\Enums\TipoPlanilla;
use App\Models\Employee;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class ImportedPaymentFilesController extends FormattedFilesController
{
    private array $requiredColumns = [
        'planilla',
        'codigo',
        'cedula',
        'apaterno',
        'amaterno',
        'nivel',
        'haber_basico',
        'dias_trabajados',
        'monto_categoria',
        'total_ganado',
        'faltas_multas',
        'retencion_judicial',
        'otros_descuentos',
        'primer_nombre',
    ];

    public function show()
    {
        return view('imported-payment-files.index');
    }

    public function download(Request $request)
    {
        $validated = $request->validate([
            'planilla' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            'tipo_planilla' => ['required', 'in:banco,cheque'],
            'codigo_planilla' => ['required', 'integer', 'min:1'],
            'mes_planilla' => ['required', 'integer', 'min:1', 'max:12'],
            'descripcion_mes' => ['required', 'string', 'max:80'],
            'salario_minimo' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $this->filas = $this->parseExcelRows(
                $request->file('planilla'),
                TipoPlanilla::from($validated['tipo_planilla']),
                (float) ($validated['salario_minimo'] ?? 3300)
            );

            $this->ensureRowsAreReadyForZip($this->filas);
        } catch (Throwable $exception) {
            return back()
                ->withErrors(['planilla' => $exception->getMessage()])
                ->withInput();
        }

        $this->planillaBanco = TipoPlanilla::from($validated['tipo_planilla']);
        $this->codigoPlanilla = (int) $validated['codigo_planilla'];
        $this->mesPlanilla = (int) $validated['mes_planilla'];
        $this->month = $validated['descripcion_mes'];

        $this->generarEMP();
        $this->generarING();
        $this->generarDES();
        $this->generarRLA();
        $this->generarC3C();
        $this->generarCAR();
        $this->generarCUA();
        $this->generarACR();

        return $this->descargarTodos();
    }

    private function parseExcelRows($file, TipoPlanilla $tipoPlanilla, float $salarioMinimo): array
    {
        $filas = $this->parseOfficialWorkbookRows($file, $tipoPlanilla, $salarioMinimo);

        if ($filas !== []) {
            return $filas;
        }

        return $this->parseNormalizedRows($file);
    }

    private function parseNormalizedRows($file): array
    {
        $sheets = Excel::toCollection(new class implements ToCollection {
            public function collection(Collection $rows)
            {
            }
        }, $file);

        $sheet = $sheets->first();

        if (! $sheet || $sheet->count() < 2) {
            throw new \RuntimeException('El Excel debe tener encabezados y al menos una fila de datos.');
        }

        $headers = $sheet->first()
            ->map(fn ($header) => $this->normalizeHeader($header))
            ->all();

        $missing = array_diff($this->requiredColumns, $headers);

        if ($missing !== []) {
            throw new \RuntimeException('Faltan columnas requeridas: ' . implode(', ', $missing));
        }

        return $sheet
            ->skip(1)
            ->filter(fn ($row) => $row->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty())
            ->map(function ($row) use ($headers) {
                $data = [];

                foreach ($headers as $index => $header) {
                    if ($header !== '') {
                        $data[$header] = $row[$index] ?? null;
                    }
                }

                return $this->enrichFilaFromDatabase($this->makeFila($data));
            })
            ->values()
            ->all();
    }

    private function parseOfficialWorkbookRows($file, TipoPlanilla $tipoPlanilla, float $salarioMinimo): array
    {
        $reader = IOFactory::createReaderForFile($file->getRealPath());
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getRealPath());
        $filas = [];

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $title = $sheet->getTitle();

            if (! $this->isSalarySheetForType($title, $tipoPlanilla)) {
                continue;
            }

            $planilla = $this->sheetNumberFromTitle($title);
            $headerRow = $this->findSalaryHeaderRow($sheet);

            if (! $headerRow) {
                continue;
            }

            for ($row = $headerRow + 1; $row <= $sheet->getHighestDataRow(); $row++) {
                $codigo = $this->cell($sheet, 'B', $row);
                $cedula = $this->cell($sheet, 'C', $row);

                if ($codigo === '' || $cedula === '' || Str::contains(Str::lower($codigo), 'total')) {
                    continue;
                }

                $categoria = $this->numberFromCell($sheet, 'H', $row);
                $haberBasico = $this->numberFromCell($sheet, 'I', $row);
                $diasTrabajados = $this->numberFromCell($sheet, 'J', $row);
                $subtotal = $this->validNumberOr($this->numberFromCell($sheet, 'K', $row, false), ($haberBasico / 30) * $diasTrabajados);
                $bonoFrontera = $this->numberFromCell($sheet, 'L', $row);
                $montoCategoria = $this->validNumberOr($this->numberFromCell($sheet, 'M', $row, false), ($salarioMinimo * $categoria) / 100);
                $totalGanado = $this->validNumberOr($this->numberFromCell($sheet, 'N', $row, false), $subtotal + $bonoFrontera + $montoCategoria);
                $aporteAfp = $this->numberFromCell($sheet, 'O', $row);
                $riesgoComun = $this->numberFromCell($sheet, 'P', $row);
                $comisionAfp = $this->validNumberOr($this->numberFromCell($sheet, 'Q', $row, false), $totalGanado * 0.005);
                $apNalSol = $this->numberFromCell($sheet, 'R', $row);
                $apNalAseg = $this->validNumberOr($this->numberFromCell($sheet, 'S', $row, false), $totalGanado * 0.005);
                $iva = $this->numberFromCell($sheet, 'T', $row);
                $faltasMultas = $this->numberFromCell($sheet, 'U', $row);
                $retencionJudicial = $this->numberFromCell($sheet, 'V', $row);
                $otrosDescuentos = $this->numberFromCell($sheet, 'W', $row);
                $totalDescuentos = $this->validNumberOr(
                    $this->numberFromCell($sheet, 'X', $row, false),
                    $aporteAfp + $riesgoComun + $comisionAfp + $apNalSol + $apNalAseg + $iva + $faltasMultas + $retencionJudicial + $otrosDescuentos
                );

                [$primerNombre, $segundoNombre] = $this->splitNames($this->cell($sheet, 'F', $row));

                $filas[] = $this->enrichFilaFromDatabase((object) [
                    'planilla' => $planilla,
                    'codigo' => $codigo,
                    'cedula' => $cedula,
                    'apaterno' => $this->cell($sheet, 'D', $row),
                    'amaterno' => $this->cell($sheet, 'E', $row),
                    'nombres' => $this->cell($sheet, 'F', $row),
                    'nivel' => $this->cell($sheet, 'G', $row),
                    'categoria' => $categoria,
                    'haber_basico' => $haberBasico,
                    'dias_trabajados' => $diasTrabajados,
                    'subtotal' => $subtotal,
                    'bono_frontera' => $bonoFrontera,
                    'monto_categoria' => $montoCategoria,
                    'total_ganado' => $totalGanado,
                    'aporte_afp' => $aporteAfp,
                    'riesgo_comun' => $riesgoComun,
                    'comision_afp' => $comisionAfp,
                    'ap_nal_sol' => $apNalSol,
                    'ap_nal_aseg' => $apNalAseg,
                    'iva' => $iva,
                    'faltas_multas' => $faltasMultas,
                    'retencion_judicial' => $retencionJudicial,
                    'otros_descuentos' => $otrosDescuentos,
                    'total_descuentos' => $totalDescuentos,
                    'liquido_pagable' => $this->validNumberOr($this->numberFromCell($sheet, 'Y', $row, false), $totalGanado - $totalDescuentos),
                    'sexo' => '',
                    'nacimiento' => '',
                    'afiliado' => '',
                    'libreta_militar' => '',
                    'codigo_rentista' => '',
                    'tipo_relacion' => '1',
                    'afp_destino' => '',
                    'fecha_ingreso' => '',
                    'lugar_trabajo' => 60101,
                    'grado_academico' => 0,
                    'unidad_organizacional' => 0,
                    'primer_nombre' => $primerNombre,
                    'segundo_nombre' => $segundoNombre,
                    'acasada' => '',
                    'nco' => '',
                ]);
            }
        }

        return $filas;
    }

    private function enrichFilaFromDatabase(object $fila): object
    {
        $employee = $this->findEmployeeForFila($fila);
        $person = $employee?->Person ?? $this->findPersonByIdentityCard($fila->cedula);

        if ($person) {
            $fila->cedula = (string) $person->identity_card;
            $fila->apaterno = $person->first_surname ?: $fila->apaterno;
            $fila->amaterno = $person->second_surname ?: $fila->amaterno;
            $fila->primer_nombre = $person->first_name ?: $fila->primer_nombre;
            $fila->segundo_nombre = $person->middle_name ?: $fila->segundo_nombre;
            $fila->nombres = trim($fila->primer_nombre . ' ' . $fila->segundo_nombre);
            $fila->acasada = $person->married_name ?: $fila->acasada;
            $fila->sexo = $person->gender ?: $fila->sexo;
            $fila->nacimiento = $person->date_of_birth ? Carbon::parse($person->date_of_birth)->format('dmY') : $fila->nacimiento;
            $fila->afiliado = (string) ($person->unique_affiliate_number ?: $fila->afiliado);
            $fila->libreta_militar = $person->military_service_booklet_number ?: $fila->libreta_militar;
            $fila->codigo_rentista = $person->social_security_affiliation_code ?: $fila->codigo_rentista;
            $fila->nco = $person->identification_document_plugin ?: $fila->nco;
            $fila->afp_destino = $person->PensionFundManager?->code ?: $fila->afp_destino;
        }

        if ($employee) {
            $fila->codigo = (string) $employee->item_number;
            $fila->tipo_relacion = $employee->DependencyRelationship?->code ?: $fila->tipo_relacion;
            $fila->fecha_ingreso = $employee->date_of_entry ? Carbon::parse($employee->date_of_entry)->format('dmY') : $fila->fecha_ingreso;
            $fila->grado_academico = (string) ($employee->AcademicDegree?->code ?: $fila->grado_academico);

            $charge = $employee->Charges()->wherePivot('state', 'activo')->first();
            $area = $employee->OrganizationalAreas()->wherePivot('state', 'activo')->first();

            $fila->nivel = $charge?->code ?: $fila->nivel;
            $fila->unidad_organizacional = (string) ($area?->code ?: $fila->unidad_organizacional);
        }

        $fila->lugar_trabajo = $fila->lugar_trabajo ?: 60101;

        return $fila;
    }

    private function ensureRowsAreReadyForZip(array $filas): void
    {
        $missing = collect($filas)->filter(function ($fila) {
            return ! $fila->sexo
                || ! $fila->nacimiento
                || ! $fila->afiliado
                || ! $fila->fecha_ingreso
                || ! $fila->afp_destino
                || ! $fila->tipo_relacion
                || ! $fila->grado_academico
                || ! $fila->unidad_organizacional;
        });

        if ($missing->isEmpty()) {
            return;
        }

        $examples = $missing
            ->take(8)
            ->map(fn ($fila) => trim($fila->codigo . ' / ' . $fila->cedula . ' / ' . $fila->nombres))
            ->implode('; ');

        throw new \RuntimeException(
            'Hay ' . $missing->count() . ' funcionarios del Excel que no tienen datos base completos en la BD. Ejemplos: ' . $examples
        );
    }

    private function findEmployeeForFila(object $fila): ?Employee
    {
        $employee = Employee::with(['Person.PensionFundManager', 'DependencyRelationship', 'AcademicDegree'])
            ->where('item_number', $fila->codigo)
            ->first();

        if ($employee) {
            return $employee;
        }

        $person = $this->findPersonByIdentityCard($fila->cedula);

        return $person?->Employee;
    }

    private function findPersonByIdentityCard(string $identityCard): ?Person
    {
        $identityCard = preg_replace('/\D+/', '', $identityCard);

        if (! $identityCard) {
            return null;
        }

        return Person::with(['PensionFundManager', 'Employee'])->where('identity_card', $identityCard)->first();
    }

    private function isSalarySheetForType(string $title, TipoPlanilla $tipoPlanilla): bool
    {
        if (! Str::contains($title, '_sld_')) {
            return false;
        }

        return $tipoPlanilla == TipoPlanilla::Banco
            ? Str::startsWith($title, '01_')
            : Str::startsWith($title, ['50_', '147_']);
    }

    private function sheetNumberFromTitle(string $title): int
    {
        return match (true) {
            Str::contains($title, '65-v0') => 1,
            Str::contains($title, '65+v0') => 2,
            Str::contains($title, '65-sv0') => 3,
            Str::contains($title, '65+sv0') => 4,
            default => 1,
        };
    }

    private function findSalaryHeaderRow($sheet): ?int
    {
        for ($row = 1; $row <= min(20, $sheet->getHighestDataRow()); $row++) {
            if ($this->cell($sheet, 'B', $row) === 'Codigo' && Str::contains($this->cell($sheet, 'C', $row), 'Cedula')) {
                return $row;
            }
        }

        return null;
    }

    private function cell($sheet, string $column, int $row): string
    {
        return trim((string) $sheet->getCell($column . $row)->getFormattedValue());
    }

    private function numberFromCell($sheet, string $column, int $row, bool $fallbackToZero = true): ?float
    {
        $cell = $sheet->getCell($column . $row);
        $calculated = $cell->getCalculatedValue();

        if (is_numeric($calculated)) {
            return (float) $calculated;
        }

        $value = trim((string) $cell->getFormattedValue());

        if ($value === '' || Str::contains($value, '#')) {
            return $fallbackToZero ? 0 : null;
        }

        $value = str_replace(' ', '', $value);

        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : ($fallbackToZero ? 0 : null);
    }

    private function validNumberOr(?float $value, float $fallback): float
    {
        return $value === null ? $fallback : $value;
    }

    private function splitNames(string $names): array
    {
        $parts = preg_split('/\s+/', trim($names), 2);

        return [
            $parts[0] ?? '',
            $parts[1] ?? '',
        ];
    }

    private function makeFila(array $data): object
    {
        $primerNombre = $this->text($data, 'primer_nombre');
        $segundoNombre = $this->text($data, 'segundo_nombre');

        return (object) [
            'planilla' => $this->number($data, 'planilla'),
            'codigo' => $this->text($data, 'codigo'),
            'cedula' => $this->text($data, 'cedula'),
            'apaterno' => $this->text($data, 'apaterno'),
            'amaterno' => $this->text($data, 'amaterno'),
            'nombres' => trim($primerNombre . ' ' . $segundoNombre),
            'nivel' => $this->text($data, 'nivel'),
            'categoria' => $this->number($data, 'categoria'),
            'haber_basico' => $this->number($data, 'haber_basico'),
            'dias_trabajados' => $this->number($data, 'dias_trabajados'),
            'subtotal' => $this->number($data, 'subtotal'),
            'bono_frontera' => $this->number($data, 'bono_frontera'),
            'monto_categoria' => $this->number($data, 'monto_categoria'),
            'total_ganado' => $this->number($data, 'total_ganado'),
            'aporte_afp' => $this->number($data, 'aporte_afp'),
            'riesgo_comun' => $this->number($data, 'riesgo_comun'),
            'comision_afp' => $this->number($data, 'comision_afp'),
            'ap_nal_sol' => $this->number($data, 'ap_nal_sol'),
            'ap_nal_aseg' => $this->number($data, 'ap_nal_aseg'),
            'iva' => $this->number($data, 'iva'),
            'faltas_multas' => $this->number($data, 'faltas_multas'),
            'retencion_judicial' => $this->number($data, 'retencion_judicial'),
            'otros_descuentos' => $this->number($data, 'otros_descuentos'),
            'total_descuentos' => $this->number($data, 'total_descuentos'),
            'liquido_pagable' => $this->number($data, 'liquido_pagable'),
            'sexo' => $this->text($data, 'sexo'),
            'nacimiento' => $this->date($data, 'nacimiento'),
            'afiliado' => $this->text($data, 'afiliado'),
            'libreta_militar' => $this->text($data, 'libreta_militar'),
            'codigo_rentista' => $this->text($data, 'codigo_rentista'),
            'tipo_relacion' => $this->text($data, 'tipo_relacion'),
            'afp_destino' => $this->text($data, 'afp_destino'),
            'fecha_ingreso' => $this->date($data, 'fecha_ingreso'),
            'lugar_trabajo' => $this->text($data, 'lugar_trabajo'),
            'grado_academico' => $this->text($data, 'grado_academico'),
            'unidad_organizacional' => $this->text($data, 'unidad_organizacional'),
            'primer_nombre' => $primerNombre,
            'segundo_nombre' => $segundoNombre,
            'acasada' => $this->text($data, 'acasada'),
            'nco' => $this->text($data, 'nco'),
        ];
    }

    private function normalizeHeader($header): string
    {
        $normalized = Str::of((string) $header)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();

        return [
            'ret_jud' => 'retencion_judicial',
            'ret_judicial' => 'retencion_judicial',
            'fecha_nacimiento' => 'nacimiento',
            'nua' => 'afiliado',
            'lib' => 'libreta_militar',
            'ren' => 'codigo_rentista',
            'ap1' => 'apaterno',
            'ap2' => 'amaterno',
            'ap3' => 'acasada',
            'no1' => 'primer_nombre',
            'no2' => 'segundo_nombre',
        ][$normalized] ?? $normalized;
    }

    private function text(array $data, string $key): string
    {
        return trim((string) ($data[$key] ?? ''));
    }

    private function number(array $data, string $key): float
    {
        $value = $data[$key] ?? 0;

        if ($value === null || $value === '') {
            return 0;
        }

        return (float) str_replace(',', '.', (string) $value);
    }

    private function date(array $data, string $key): string
    {
        $value = $data[$key] ?? '';

        if ($value === null || $value === '') {
            return '';
        }

        $valueAsString = trim((string) $value);

        if (preg_match('/^\d{8}$/', $valueAsString)) {
            return $valueAsString;
        }

        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('dmY');
        }

        return Carbon::parse($valueAsString)->format('dmY');
    }
}
