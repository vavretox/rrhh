<?php

namespace App\Console\Commands;

use App\Models\AcademicDegree;
use App\Models\Category;
use App\Models\Charge;
use App\Models\ChargeEmployee;
use App\Models\DependencyRelationship;
use App\Models\Employee;
use App\Models\Entity;
use App\Models\OrganizationalArea;
use App\Models\OrganizationalAreaEmployee;
use App\Models\PensionFundManager;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportLegacyPaymentFiles extends Command
{
    protected $signature = 'payment-files:import-legacy
        {--emp= : Ruta del archivo .EMP}
        {--rla= : Ruta del archivo .RLA}
        {--ing= : Ruta del archivo .ING para inferir antiguedad}
        {--car= : Ruta del archivo .CAR para crear/actualizar cargos}
        {--cua= : Ruta del archivo .CUA para crear/actualizar areas organizacionales}
        {--salario-minimo=3300 : Salario minimo usado para inferir antiguedad desde partida 11220}
        {--dry-run : Solo muestra conteos, no guarda cambios}';

    protected $description = 'Importa datos base de Personas, Antiguedad y Funcionarios desde archivos planos EMP/RLA/ING.';

    private array $stats = [
        'people_created' => 0,
        'people_updated' => 0,
        'categories_created' => 0,
        'employees_created' => 0,
        'employees_updated' => 0,
        'charges_created' => 0,
        'charges_updated' => 0,
        'areas_created' => 0,
        'areas_updated' => 0,
        'charge_links_created' => 0,
        'area_links_created' => 0,
        'missing_charges' => 0,
        'missing_areas' => 0,
    ];

    public function handle(): int
    {
        $empPath = $this->option('emp');
        $rlaPath = $this->option('rla');
        $ingPath = $this->option('ing');
        $carPath = $this->option('car');
        $cuaPath = $this->option('cua');
        $dryRun = (bool) $this->option('dry-run');

        if (! $empPath || ! is_file($empPath)) {
            $this->error('Debe proporcionar una ruta valida para --emp.');

            return self::FAILURE;
        }

        if ($rlaPath && ! is_file($rlaPath)) {
            $this->error('La ruta proporcionada para --rla no existe.');

            return self::FAILURE;
        }

        if ($ingPath && ! is_file($ingPath)) {
            $this->error('La ruta proporcionada para --ing no existe.');

            return self::FAILURE;
        }

        if ($carPath && ! is_file($carPath)) {
            $this->error('La ruta proporcionada para --car no existe.');

            return self::FAILURE;
        }

        if ($cuaPath && ! is_file($cuaPath)) {
            $this->error('La ruta proporcionada para --cua no existe.');

            return self::FAILURE;
        }

        $people = $this->parseEmp($empPath);
        $rla = $rlaPath ? $this->parseRla($rlaPath) : [];
        $categories = $ingPath ? $this->inferCategoriesFromIng($ingPath, (float) $this->option('salario-minimo')) : [];
        $charges = $carPath ? $this->parseCar($carPath) : [];
        $areas = $cuaPath ? $this->parseCua($cuaPath) : [];

        $this->info('Registros EMP leidos: ' . count($people));
        $this->info('Registros RLA leidos: ' . count($rla));
        $this->info('Categorias inferidas desde ING: ' . count(array_unique($categories)));
        $this->info('Cargos CAR leidos: ' . count($charges));
        $this->info('Areas CUA leidas: ' . count($areas));

        DB::transaction(function () use ($people, $rla, $categories, $charges, $areas, $dryRun) {
            $this->importCharges($charges, $dryRun);
            $this->importAreas($areas, $dryRun);

            foreach ($people as $doc => $personData) {
                $person = Person::where('identity_card', $doc)->first();
                $pfm = $this->findPensionFundManager($rla[$doc]['afp_destino'] ?? null, $dryRun);

                $payload = [
                    'pension_fund_manager_id' => $pfm->id,
                    'unique_affiliate_number' => (int) $personData['unique_affiliate_number'],
                    'identity_card' => (int) $doc,
                    'first_name' => $personData['first_name'],
                    'middle_name' => $personData['middle_name'],
                    'first_surname' => $personData['first_surname'],
                    'second_surname' => $personData['second_surname'],
                    'married_name' => $personData['married_name'],
                    'gender' => $personData['gender'] ?: 'M',
                    'date_of_birth' => $personData['date_of_birth'],
                    'military_service_booklet_number' => $personData['military_service_booklet_number'],
                    'social_security_affiliation_code' => $personData['social_security_affiliation_code'],
                    'identification_document_plugin' => $personData['identification_document_plugin'],
                    'state' => 'activo',
                ];

                if ($person) {
                    $this->stats['people_updated']++;

                    if (! $dryRun) {
                        $person->update($payload);
                    }
                } else {
                    $this->stats['people_created']++;

                    if (! $dryRun) {
                        $person = Person::create($payload + ['slug' => Str::uuid()]);
                    }
                }

                if (isset($rla[$doc])) {
                    if ($dryRun && ! $person) {
                        $this->stats['employees_created']++;
                        continue;
                    }

                    $this->upsertEmployee($person, $rla[$doc], $categories[$doc] ?? 0, $dryRun);
                }
            }

        });

        $this->table(['Operacion', 'Cantidad'], collect($this->stats)->map(fn ($value, $key) => [$key, $value])->all());

        if ($dryRun) {
            $this->warn('Dry-run: no se guardaron cambios.');
        }

        return self::SUCCESS;
    }

    private function parseEmp(string $path): array
    {
        $records = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
            if (trim($line) === '') {
                continue;
            }

            $doc = ltrim(substr($line, 0, 15), '0') ?: '0';

            $records[$doc] = [
                'identity_card' => $doc,
                'first_surname' => trim(substr($line, 15, 25)),
                'second_surname' => trim(substr($line, 40, 25)),
                'married_name' => trim(substr($line, 65, 25)),
                'first_name' => trim(substr($line, 90, 25)),
                'middle_name' => trim(substr($line, 115, 25)),
                'gender' => trim(substr($line, 140, 1)),
                'date_of_birth' => Carbon::createFromFormat('dmY', substr($line, 141, 8))->format('Y-m-d'),
                'unique_affiliate_number' => ltrim(substr($line, 149, 10), '0') ?: 0,
                'military_service_booklet_number' => ltrim(substr($line, 159, 12), '0'),
                'social_security_affiliation_code' => ltrim(substr($line, 171, 10), '0'),
                'identification_document_plugin' => trim(substr($line, 181, 2)),
            ];
        }

        return $records;
    }

    private function parseRla(string $path): array
    {
        $records = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
            if (trim($line) === '') {
                continue;
            }

            $doc = ltrim(substr($line, 19, 15), '0') ?: '0';

            $records[$doc] = [
                'item_number' => (int) ltrim(substr($line, 14, 5), '0'),
                'tipo_relacion' => trim(substr($line, 34, 1)),
                'afp_destino' => trim(substr($line, 35, 1)),
                'date_of_entry' => Carbon::createFromFormat('dmY', substr($line, 36, 8))->format('Y-m-d'),
                'nivel' => ltrim(substr($line, 44, 5), '0') ?: '0',
                'grado_academico' => ltrim(substr($line, 94, 2), '0') ?: '0',
                'unidad_organizacional' => ltrim(substr($line, 96, 4), '0') ?: '0',
            ];
        }

        return $records;
    }

    private function inferCategoriesFromIng(string $path, float $salarioMinimo): array
    {
        $categories = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
            if (trim($line) === '') {
                continue;
            }

            $partida = ltrim(substr($line, 42, 5), '0') ?: '0';

            if ($partida !== '11220') {
                continue;
            }

            $doc = ltrim(substr($line, 19, 15), '0') ?: '0';
            $montoCategoria = (float) trim(substr($line, 47, 9));
            $percentage = $salarioMinimo > 0 ? (int) round(($montoCategoria / $salarioMinimo) * 100) : 0;

            $categories[$doc] = $percentage;
        }

        return $categories;
    }

    private function parseCar(string $path): array
    {
        $records = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
            if (trim($line) === '') {
                continue;
            }

            $code = ltrim(substr($line, 14, 4), '0') ?: '0';
            $description = trim(substr($line, 18, 40));

            $records[$code] = $description;
        }

        return $records;
    }

    private function parseCua(string $path): array
    {
        $records = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
            if (trim($line) === '') {
                continue;
            }

            $code = ltrim(substr($line, 14, 4), '0') ?: '0';
            $description = trim(substr($line, 18, 40));

            $records[$code] = $description;
        }

        return $records;
    }

    private function importCharges(array $charges, bool $dryRun): void
    {
        foreach ($charges as $code => $description) {
            $charge = Charge::where('code', $code)->first();

            if ($charge) {
                $this->stats['charges_updated']++;

                if (! $dryRun) {
                    $charge->update([
                        'name' => $description,
                        'description' => $description,
                        'state' => 'activo',
                    ]);
                }
            } else {
                $this->stats['charges_created']++;

                if (! $dryRun) {
                    Charge::create([
                        'name' => $description,
                        'description' => $description,
                        'code' => $code,
                        'basic_salary' => 0,
                        'state' => 'activo',
                        'slug' => Str::uuid(),
                    ]);
                }
            }
        }
    }

    private function importAreas(array $areas, bool $dryRun): void
    {
        $entity = Entity::where('code', 906)->first() ?? Entity::first();

        if (! $entity && ! $dryRun) {
            $entity = Entity::create([
                'name' => 'GOBIERNO AUTONOMO DEPARTAMENTAL DE TARIJA',
                'code' => 906,
                'nit' => 178928029,
                'state' => 'activo',
                'slug' => Str::uuid(),
            ]);
        }

        foreach ($areas as $code => $description) {
            $area = OrganizationalArea::where('code', $code)->first();

            if ($area) {
                $this->stats['areas_updated']++;

                if (! $dryRun) {
                    $area->update([
                        'entity_id' => $area->entity_id ?: $entity?->id,
                        'name' => $description,
                        'description' => $description,
                        'state' => 'activo',
                    ]);
                }
            } else {
                $this->stats['areas_created']++;

                if (! $dryRun) {
                    OrganizationalArea::create([
                        'entity_id' => $entity->id,
                        'relationship' => null,
                        'name' => $description,
                        'description' => $description,
                        'code' => $code,
                        'state' => 'activo',
                        'slug' => Str::uuid(),
                    ]);
                }
            }
        }
    }

    private function upsertEmployee(?Person $person, array $rla, int $categoryPercentage, bool $dryRun): void
    {
        if (! $person) {
            return;
        }

        $dependency = $this->findDependencyRelationship($rla['tipo_relacion'], $dryRun);
        $category = $this->findCategory($categoryPercentage, $dryRun);
        $academicDegree = $this->findAcademicDegree($rla['grado_academico'], $dryRun);

        $payload = [
            'person_id' => $person->id,
            'dependency_relationship_id' => $dependency->id,
            'category_id' => $category->id,
            'academic_degree_id' => $academicDegree->id,
            'item_number' => $rla['item_number'],
            'date_of_entry' => $rla['date_of_entry'],
            'state' => 'activo',
        ];

        $employee = Employee::where('person_id', $person->id)->first();

        if ($employee) {
            $this->stats['employees_updated']++;

            if (! $dryRun) {
                $employee->update($payload);
            }
        } else {
            $this->stats['employees_created']++;

            if (! $dryRun) {
                $employee = Employee::create($payload + ['slug' => Str::uuid()]);
            }
        }

        $this->syncCharge($employee, $rla, $dryRun);
        $this->syncArea($employee, $rla, $dryRun);
    }

    private function syncCharge(?Employee $employee, array $rla, bool $dryRun): void
    {
        if (! $employee) {
            return;
        }

        $charge = Charge::where('code', $rla['nivel'])->first();

        if (! $charge) {
            $this->stats['missing_charges']++;

            return;
        }

        $exists = ChargeEmployee::where('employee_id', $employee->id)
            ->where('charge_id', $charge->id)
            ->where('state', 'activo')
            ->exists();

        if (! $exists) {
            $this->stats['charge_links_created']++;

            if (! $dryRun) {
                ChargeEmployee::create([
                    'charge_id' => $charge->id,
                    'employee_id' => $employee->id,
                    'start_date' => $employee->date_of_entry,
                    'state' => 'activo',
                    'slug' => Str::uuid(),
                ]);
            }
        }
    }

    private function syncArea(?Employee $employee, array $rla, bool $dryRun): void
    {
        if (! $employee) {
            return;
        }

        $area = OrganizationalArea::where('code', $rla['unidad_organizacional'])->first();

        if (! $area) {
            $this->stats['missing_areas']++;

            return;
        }

        $exists = OrganizationalAreaEmployee::where('employee_id', $employee->id)
            ->where('organizational_area_id', $area->id)
            ->where('state', 'activo')
            ->exists();

        if (! $exists) {
            $this->stats['area_links_created']++;

            if (! $dryRun) {
                OrganizationalAreaEmployee::create([
                    'organizational_area_id' => $area->id,
                    'employee_id' => $employee->id,
                    'start_date' => $employee->date_of_entry,
                    'state' => 'activo',
                    'slug' => Str::uuid(),
                ]);
            }
        }
    }

    private function findPensionFundManager(?string $code, bool $dryRun): PensionFundManager
    {
        $model = PensionFundManager::where('code', $code)->first();

        if ($model || $dryRun) {
            return $model ?? new PensionFundManager(['id' => 0]);
        }

        return PensionFundManager::firstOrCreate(
            ['code' => $code ?: 0],
            ['name' => 'AFP ' . ($code ?: 0), 'state' => 'activo', 'slug' => Str::uuid()]
        );
    }

    private function findDependencyRelationship(string $code, bool $dryRun): DependencyRelationship
    {
        $model = DependencyRelationship::where('code', $code)->first();

        if ($model || $dryRun) {
            return $model ?? new DependencyRelationship(['id' => 0]);
        }

        return DependencyRelationship::firstOrCreate(
            ['code' => $code],
            ['description' => 'RELACION ' . $code, 'state' => 'activo', 'slug' => Str::uuid()]
        );
    }

    private function findCategory(int $percentage, bool $dryRun): Category
    {
        $category = Category::where('percentage', $percentage)->first();

        if (! $category) {
            $this->stats['categories_created']++;
        }

        if ($category || $dryRun) {
            return $category ?? new Category(['id' => 0]);
        }

        return Category::firstOrCreate(
            ['percentage' => $percentage],
            ['description' => 'ANTIGUEDAD ' . $percentage . '%', 'state' => 'activo', 'slug' => Str::uuid()]
        );
    }

    private function findAcademicDegree(string $code, bool $dryRun): AcademicDegree
    {
        $model = AcademicDegree::where('code', $code)->first();

        if ($model || $dryRun) {
            return $model ?? new AcademicDegree(['id' => 0]);
        }

        return AcademicDegree::firstOrCreate(
            ['code' => $code],
            ['description' => 'GRADO ' . $code, 'state' => 'activo', 'slug' => Str::uuid()]
        );
    }
}
