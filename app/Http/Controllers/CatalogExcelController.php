<?php

namespace App\Http\Controllers;

use App\Exports\EmployeesExport;
use App\Exports\EmployeesTemplateExport;
use App\Exports\PeopleExport;
use App\Exports\PeopleTemplateExport;
use App\Models\AcademicDegree;
use App\Models\Category;
use App\Models\DependencyRelationship;
use App\Models\Employee;
use App\Models\PensionFundManager;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CatalogExcelController extends Controller
{
    public function peopleTemplate(): BinaryFileResponse
    {
        return Excel::download(new PeopleTemplateExport, 'plantilla-personas.xlsx');
    }

    public function peopleExport(): BinaryFileResponse
    {
        return Excel::download(new PeopleExport, 'personas.xlsx');
    }

    public function employeesTemplate(): BinaryFileResponse
    {
        return Excel::download(new EmployeesTemplateExport, 'plantilla-funcionarios.xlsx');
    }

    public function employeesExport(): BinaryFileResponse
    {
        return Excel::download(new EmployeesExport, 'funcionarios.xlsx');
    }

    public function importPeople(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $rows = $this->readRows($request->file('file')->getRealPath());
        $errors = $this->validatePeopleRows($rows);

        if ($errors !== []) {
            return back()->with('excel_errors', $errors);
        }

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $afp = PensionFundManager::where('code', $row['afp_codigo'])->first();

                Person::create([
                    'pension_fund_manager_id' => $afp->id,
                    'unique_affiliate_number' => $row['nua'],
                    'identity_card' => $row['cedula'],
                    'first_name' => $row['primer_nombre'],
                    'middle_name' => $row['segundo_nombre'] ?: null,
                    'first_surname' => $row['primer_apellido'] ?: null,
                    'second_surname' => $row['segundo_apellido'] ?: null,
                    'married_name' => $row['apellido_casada'] ?: null,
                    'gender' => strtoupper($row['genero']),
                    'date_of_birth' => $this->formatDate($row['fecha_nacimiento']),
                    'military_service_booklet_number' => $row['libreta_militar'] ?: null,
                    'social_security_affiliation_code' => $row['codigo_seguro_social'] ?: null,
                    'identification_document_plugin' => $row['complemento_ci'] ?: null,
                    'state' => $row['estado'] ?: 'activo',
                    'slug' => Str::uuid(),
                ]);
            }
        });

        return back()->with('excel_success', 'Personas importadas correctamente.');
    }

    public function importEmployees(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls,csv']]);

        $rows = $this->readRows($request->file('file')->getRealPath());
        $errors = $this->validateEmployeeRows($rows);

        if ($errors !== []) {
            return back()->with('excel_errors', $errors);
        }

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $person = Person::where('identity_card', $row['cedula'])->first();
                $dependencyRelationship = DependencyRelationship::where('code', $row['relacion_dependencia_codigo'])->first();
                $category = Category::where('percentage', $row['categoria_porcentaje'])->first();
                $academicDegree = AcademicDegree::where('code', $row['grado_academico_codigo'])->first();

                Employee::create([
                    'person_id' => $person->id,
                    'dependency_relationship_id' => $dependencyRelationship->id,
                    'category_id' => $category->id,
                    'academic_degree_id' => $academicDegree->id,
                    'item_number' => $row['numero_item'],
                    'date_of_entry' => $this->formatDate($row['fecha_ingreso']),
                    'state' => $row['estado'] ?: 'activo',
                    'slug' => Str::uuid(),
                ]);
            }
        });

        return back()->with('excel_success', 'Funcionarios importados correctamente.');
    }

    private function readRows(string $filePath): array
    {
        $sheets = Excel::toCollection(new class implements ToCollection {
            public function collection(\Illuminate\Support\Collection $collection) {}
        }, $filePath);

        $sheet = $sheets->first();
        $headings = $sheet->shift()
            ->map(fn ($heading) => Str::of((string) $heading)->trim()->lower()->replace(' ', '_')->toString())
            ->all();

        return $sheet
            ->map(function ($row, int $index) use ($headings) {
                $values = array_combine($headings, $row->all());

                return [
                    'line' => $index + 2,
                    'data' => collect($values)->map(fn ($value) => is_string($value) ? trim($value) : $value)->all(),
                ];
            })
            ->filter(fn ($row) => collect($row['data'])->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty())
            ->values()
            ->all();
    }

    private function validatePeopleRows(array $rows): array
    {
        $errors = $this->validateRequiredHeadings($rows, PeopleTemplateExport::headingsList());

        $this->validateRepeatedRows($rows, $errors);
        $this->validateRepeatedValue($rows, 'cedula', 'cedula', $errors);
        $this->validateRepeatedValue($rows, 'nua', 'NUA', $errors);

        foreach ($rows as $row) {
            $data = $row['data'];
            $line = $row['line'];

            foreach (['afp_codigo', 'nua', 'cedula', 'primer_nombre', 'genero', 'fecha_nacimiento'] as $field) {
                if (($data[$field] ?? '') === '') {
                    $errors[] = "Linea {$line}: el campo {$field} es obligatorio.";
                }
            }

            if (! in_array(strtoupper((string) ($data['genero'] ?? '')), ['M', 'F'], true)) {
                $errors[] = "Linea {$line}: el genero debe ser M o F.";
            }

            $state = $data['estado'] ?? 'activo';
            if ($state !== '' && ! in_array($state, ['activo', 'inactivo'], true)) {
                $errors[] = "Linea {$line}: el estado debe ser activo o inactivo.";
            }

            if (! $this->isValidDate($data['fecha_nacimiento'] ?? null)) {
                $errors[] = "Linea {$line}: la fecha_nacimiento no tiene un formato valido.";
            }

            if (($data['afp_codigo'] ?? '') !== '' && ! PensionFundManager::where('code', $data['afp_codigo'])->exists()) {
                $errors[] = "Linea {$line}: no existe una AFP con codigo {$data['afp_codigo']}.";
            }
        }

        $this->validateDatabaseDuplicate($rows, 'cedula', Person::class, 'identity_card', $errors);
        $this->validateDatabaseDuplicate($rows, 'nua', Person::class, 'unique_affiliate_number', $errors);

        return array_slice($errors, 0, 50);
    }

    private function validateEmployeeRows(array $rows): array
    {
        $errors = $this->validateRequiredHeadings($rows, EmployeesTemplateExport::headingsList());

        $this->validateRepeatedRows($rows, $errors);
        $this->validateRepeatedValue($rows, 'cedula', 'cedula', $errors);
        $this->validateRepeatedValue($rows, 'numero_item', 'numero_item', $errors);

        foreach ($rows as $row) {
            $data = $row['data'];
            $line = $row['line'];

            foreach (['cedula', 'relacion_dependencia_codigo', 'categoria_porcentaje', 'grado_academico_codigo', 'numero_item', 'fecha_ingreso'] as $field) {
                if (($data[$field] ?? '') === '') {
                    $errors[] = "Linea {$line}: el campo {$field} es obligatorio.";
                }
            }

            $state = $data['estado'] ?? 'activo';
            if ($state !== '' && ! in_array($state, ['activo', 'inactivo'], true)) {
                $errors[] = "Linea {$line}: el estado debe ser activo o inactivo.";
            }

            if (! $this->isValidDate($data['fecha_ingreso'] ?? null)) {
                $errors[] = "Linea {$line}: la fecha_ingreso no tiene un formato valido.";
            }

            if (($data['cedula'] ?? '') !== '' && ! Person::where('identity_card', $data['cedula'])->exists()) {
                $errors[] = "Linea {$line}: no existe una persona con cedula {$data['cedula']}.";
            }

            if (($data['relacion_dependencia_codigo'] ?? '') !== '' && ! DependencyRelationship::where('code', $data['relacion_dependencia_codigo'])->exists()) {
                $errors[] = "Linea {$line}: no existe una relacion de dependencia con codigo {$data['relacion_dependencia_codigo']}.";
            }

            if (($data['categoria_porcentaje'] ?? '') !== '' && ! Category::where('percentage', $data['categoria_porcentaje'])->exists()) {
                $errors[] = "Linea {$line}: no existe una categoria con porcentaje {$data['categoria_porcentaje']}.";
            }

            if (($data['grado_academico_codigo'] ?? '') !== '' && ! AcademicDegree::where('code', $data['grado_academico_codigo'])->exists()) {
                $errors[] = "Linea {$line}: no existe un grado academico con codigo {$data['grado_academico_codigo']}.";
            }
        }

        $this->validateEmployeePersonDuplicate($rows, $errors);
        $this->validateDatabaseDuplicate($rows, 'numero_item', Employee::class, 'item_number', $errors);

        return array_slice($errors, 0, 50);
    }

    private function validateRequiredHeadings(array $rows, array $requiredHeadings): array
    {
        if ($rows === []) {
            return ['El Excel no tiene filas para importar.'];
        }

        $headings = array_keys($rows[0]['data']);
        $missing = array_diff($requiredHeadings, $headings);

        if ($missing === []) {
            return [];
        }

        return ['Faltan columnas requeridas: ' . implode(', ', $missing) . '.'];
    }

    private function validateRepeatedRows(array $rows, array &$errors): void
    {
        $seen = [];

        foreach ($rows as $row) {
            $signature = md5(json_encode($row['data']));

            if (isset($seen[$signature])) {
                $errors[] = "Linea {$row['line']}: fila repetida con la linea {$seen[$signature]}.";
                continue;
            }

            $seen[$signature] = $row['line'];
        }
    }

    private function validateRepeatedValue(array $rows, string $field, string $label, array &$errors): void
    {
        $seen = [];

        foreach ($rows as $row) {
            $value = (string) ($row['data'][$field] ?? '');

            if ($value === '') {
                continue;
            }

            if (isset($seen[$value])) {
                $errors[] = "Linea {$row['line']}: {$label} repetido con la linea {$seen[$value]}.";
                continue;
            }

            $seen[$value] = $row['line'];
        }
    }

    private function validateDatabaseDuplicate(array $rows, string $field, string $model, string $column, array &$errors): void
    {
        $values = collect($rows)->pluck("data.{$field}")->filter(fn ($value) => $value !== null && $value !== '')->values();
        $existing = $model::whereIn($column, $values)->pluck($column)->map(fn ($value) => (string) $value)->all();

        foreach ($rows as $row) {
            $value = (string) ($row['data'][$field] ?? '');

            if ($value !== '' && in_array($value, $existing, true)) {
                $errors[] = "Linea {$row['line']}: el valor {$value} ya existe en la base de datos.";
            }
        }
    }

    private function validateEmployeePersonDuplicate(array $rows, array &$errors): void
    {
        $identityCards = collect($rows)->pluck('data.cedula')->filter()->values();
        $personIds = Person::whereIn('identity_card', $identityCards)->pluck('id')->all();
        $usedPersonIds = Employee::whereIn('person_id', $personIds)->pluck('person_id')->all();
        $usedIdentityCards = Person::whereIn('id', $usedPersonIds)->pluck('identity_card')->map(fn ($value) => (string) $value)->all();

        foreach ($rows as $row) {
            $identityCard = (string) ($row['data']['cedula'] ?? '');

            if ($identityCard !== '' && in_array($identityCard, $usedIdentityCards, true)) {
                $errors[] = "Linea {$row['line']}: la persona con cedula {$identityCard} ya esta registrada como funcionario.";
            }
        }
    }

    private function isValidDate($value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        try {
            $this->formatDate($value);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function formatDate($value): string
    {
        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        return Carbon::parse($value)->format('Y-m-d');
    }
}
