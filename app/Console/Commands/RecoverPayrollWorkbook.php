<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeeForm;
use App\Models\Form;
use App\Models\Setting;
use App\Models\Sheet;
use App\Models\Type;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RecoverPayrollWorkbook extends Command
{
    protected $signature = 'payroll:recover-workbook {file} {period} {type : banco|cheque} {--prefix= : Prefijos separados por coma, por ejemplo 01 o 50,147} {--dry-run}';
    protected $description = 'Reconstruye una planilla y sus funcionarios desde un Excel oficial previamente generado.';

    public function handle(): int
    {
        $path = (string) $this->argument('file');
        $typeName = Str::lower((string) $this->argument('type'));
        if (! is_file($path) || ! in_array($typeName, ['banco', 'cheque'], true)) {
            $this->error('Archivo o tipo inválido.');
            return self::FAILURE;
        }

        $type = Type::whereRaw('LOWER(name) = ?', [$typeName])->firstOrFail();
        $setting = Setting::where('state', 'activo')->latest('start_date')->firstOrFail();
        $employees = Employee::with('Person')->get();
        $byItem = $employees->keyBy(fn ($employee) => (string) $employee->item_number);
        $byCi = $employees->filter(fn ($employee) => $employee->Person)->keyBy(fn ($employee) => $this->digits((string) $employee->Person->identity_card));
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $book = $reader->load($path);
        $rows = [];

        foreach ($book->getWorksheetIterator() as $sheet) {
            $title = $sheet->getTitle();
            // Los archivos exportados ya están separados por tipo, aunque versiones anteriores conservaron el prefijo 01 en cheques.
            if (! Str::contains($title, '_sld_')) continue;
            $prefixes = array_filter(explode(',', (string) $this->option('prefix')));
            if ($prefixes && ! collect($prefixes)->contains(fn ($prefix) => Str::startsWith($title, trim($prefix).'_'))) continue;
            $header = $this->headerRow($sheet);
            if (! $header) continue;
            for ($row = $header + 1; $row <= $sheet->getHighestDataRow(); $row++) {
                $item = $this->cell($sheet, 'B', $row);
                $ci = $this->digits($this->cell($sheet, 'C', $row));
                if ($item === '' || $ci === '' || Str::contains(Str::lower($item), 'total')) continue;
                $employee = $byItem->get($item) ?? $byCi->get($ci);
                $rows[] = ['employee' => $employee, 'sheet' => $this->sheetNumber($title), 'worked_days' => (int) $this->number($sheet, 'J', $row), 'border_bonus' => $this->number($sheet, 'L', $row), 'iva' => $this->number($sheet, 'T', $row), 'violations_and_fines' => $this->number($sheet, 'U', $row), 'judicial_withholdings' => $this->number($sheet, 'V', $row), 'other_discounts' => $this->number($sheet, 'W', $row), 'item' => $item, 'ci' => $ci];
            }
        }

        $found = collect($rows)->whereNotNull('employee')->count();
        $missing = count($rows) - $found;
        $this->info("Filas: ".count($rows)."; vinculadas: {$found}; no encontradas: {$missing}");
        if ($this->option('dry-run')) return self::SUCCESS;

        DB::transaction(function () use ($rows, $type, $setting) {
            $form = Form::updateOrCreate(['type_id' => $type->id, 'period' => $this->argument('period')], ['setting_id' => $setting->id, 'state' => 'activo', 'slug' => Str::uuid()]);
            foreach ($rows as $row) {
                if (! $row['employee']) continue;
                $sheet = Sheet::where('number', $row['sheet'])->firstOrFail();
                EmployeeForm::updateOrCreate(['employee_id' => $row['employee']->id, 'form_id' => $form->id], ['sheet_id' => $sheet->id, 'worked_days' => $row['worked_days'], 'border_bonus' => $row['border_bonus'], 'iva' => $row['iva'], 'violations_and_fines' => $row['violations_and_fines'], 'judicial_withholdings' => $row['judicial_withholdings'], 'other_discounts' => $row['other_discounts'], 'state' => 'activo', 'slug' => Str::uuid()]);
            }
        });
        $this->info('Planilla recuperada.');
        return self::SUCCESS;
    }

    private function headerRow($sheet): ?int { for ($i=1; $i<=min(20,$sheet->getHighestDataRow()); $i++) if ($this->cell($sheet,'B',$i)==='Codigo' && Str::contains($this->cell($sheet,'C',$i),'Cedula')) return $i; return null; }
    private function sheetNumber(string $title): int { return match (true) { Str::contains($title,'65-v0')=>1, Str::contains($title,'65+v0')=>2, Str::contains($title,'65-sv0')=>3, Str::contains($title,'65+sv0')=>4, default=>1 }; }
    private function cell($sheet,string $column,int $row): string { return trim((string)$sheet->getCell($column.$row)->getFormattedValue()); }
    private function digits(string $value): string { return preg_replace('/\D+/','',$value) ?: ''; }
    private function number($sheet,string $column,int $row): float { $cell=$sheet->getCell($column.$row); $value=$cell->getCalculatedValue(); if(is_numeric($value)) return (float)$value; $value=str_replace(' ','',trim((string)$cell->getFormattedValue())); if(str_contains($value,',')) $value=str_replace(',','.',str_replace('.','',$value)); return is_numeric($value)?(float)$value:0; }
}
