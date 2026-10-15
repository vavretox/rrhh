<?php

namespace App\Livewire\EmployeeForm;

use App\Models\EmployeeForm;
use App\Models\Form;
use App\Models\Sheet;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use LivewireUI\Modal\ModalComponent;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EmployeeFormImportExcel extends ModalComponent
{
    use WithFileUploads;

    public $form;
    public $planilla;
    public $updated = 0;
    public $notFound = 0;
    public $skipped = 0;
    public $summary = null;
    public $foundRows = [];
    public $notFoundRows = [];
    public $skippedRows = [];

    public function mount($formId)
    {
        $this->form = Form::with('Type')->findOrFail($formId);
    }

    protected function rules()
    {
        return [
            'planilla' => 'required|file|mimes:xlsx,xls,csv',
        ];
    }

    protected $messages = [
        'planilla.required' => 'Debe seleccionar un archivo Excel.',
        'planilla.mimes' => 'El archivo debe ser xlsx, xls o csv.',
    ];

    public function import()
    {
        $this->validate();

        $this->updated = 0;
        $this->notFound = 0;
        $this->skipped = 0;
        $this->foundRows = [];
        $this->notFoundRows = [];
        $this->skippedRows = [];

        $rows = $this->readOfficialWorkbookRows($this->planilla->getRealPath());
        $employeeForms = $this->employeeFormsLookup();

        foreach ($rows as $row) {
            $employeeForm = $employeeForms['item'][$row['codigo']] ?? $employeeForms['ci'][$row['cedula']] ?? null;

            if (! $employeeForm) {
                $this->notFound++;
                $this->notFoundRows[] = $this->resultRow($row, 'No esta seleccionado en esta planilla');
                continue;
            }

            $sheet = Sheet::where('number', $row['sheet_number'])->first();

            if (! $sheet) {
                $this->skipped++;
                $this->skippedRows[] = $this->resultRow($row, 'No existe el tipo de calculo ' . $row['sheet_number']);
                continue;
            }

            $employeeForm->update([
                'sheet_id' => $sheet->id,
                'worked_days' => $row['worked_days'],
                'border_bonus' => $row['border_bonus'],
                'iva' => $row['iva'],
                'violations_and_fines' => $row['violations_and_fines'],
                'judicial_withholdings' => $row['judicial_withholdings'],
                'other_discounts' => $row['other_discounts'],
            ]);

            $this->updated++;
            $this->foundRows[] = $this->resultRow($row, 'Actualizado');
        }

        $this->summary = "Actualizados: {$this->updated}. No encontrados en esta planilla: {$this->notFound}. Omitidos: {$this->skipped}.";
        $this->dispatch('pg:eventRefresh-EmployeeFormTable');
    }

    private function resultRow(array $row, string $status): array
    {
        return [
            'sheet' => $row['sheet_title'],
            'codigo' => $row['codigo'],
            'cedula' => $row['cedula'],
            'nombre' => $row['nombre'],
            'status' => $status,
        ];
    }

    private function employeeFormsLookup(): array
    {
        $lookup = [
            'item' => [],
            'ci' => [],
        ];

        EmployeeForm::with('Employee.Person')
            ->where('form_id', $this->form->id)
            ->get()
            ->each(function (EmployeeForm $employeeForm) use (&$lookup) {
                $item = (string) $employeeForm->Employee->item_number;
                $ci = $this->onlyDigits((string) $employeeForm->Employee->Person->identity_card);

                $lookup['item'][$item] = $employeeForm;
                $lookup['ci'][$ci] = $employeeForm;
            });

        return $lookup;
    }

    private function readOfficialWorkbookRows(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $rows = [];

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $title = $sheet->getTitle();

            if (! $this->isSalarySheetForCurrentForm($title)) {
                continue;
            }

            $headerRow = $this->findHeaderRow($sheet);

            if (! $headerRow) {
                continue;
            }

            $sheetNumber = $this->sheetNumberFromTitle($title);

            for ($row = $headerRow + 1; $row <= $sheet->getHighestDataRow(); $row++) {
                $codigo = $this->cell($sheet, 'B', $row);
                $cedula = $this->onlyDigits($this->cell($sheet, 'C', $row));

                if ($codigo === '' || $cedula === '' || Str::contains(Str::lower($codigo), 'total')) {
                    continue;
                }

                $rows[] = [
                    'sheet_number' => $sheetNumber,
                    'sheet_title' => $title,
                    'codigo' => $codigo,
                    'cedula' => $cedula,
                    'nombre' => $this->cell($sheet, 'F', $row),
                    'worked_days' => $this->numberFromCell($sheet, 'J', $row),
                    'border_bonus' => $this->numberFromCell($sheet, 'L', $row),
                    'iva' => $this->numberFromCell($sheet, 'T', $row),
                    'violations_and_fines' => $this->numberFromCell($sheet, 'U', $row),
                    'judicial_withholdings' => $this->numberFromCell($sheet, 'V', $row),
                    'other_discounts' => $this->numberFromCell($sheet, 'W', $row),
                ];
            }
        }

        return $rows;
    }

    private function isSalarySheetForCurrentForm(string $title): bool
    {
        if (! Str::contains($title, '_sld_')) {
            return false;
        }

        return Str::contains(Str::lower($this->form->Type->name), 'banco')
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

    private function findHeaderRow($sheet): ?int
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

    private function numberFromCell($sheet, string $column, int $row): float
    {
        $cell = $sheet->getCell($column . $row);
        $calculated = $cell->getCalculatedValue();

        if (is_numeric($calculated)) {
            return (float) $calculated;
        }

        $value = trim((string) $cell->getFormattedValue());

        if ($value === '' || Str::contains($value, '#')) {
            return 0;
        }

        $value = str_replace(' ', '', $value);

        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : 0;
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?: '';
    }

    public function close()
    {
        $this->closeModal();
    }

    public static function modalMaxWidth(): string
    {
        return '5xl';
    }

    public static function closeModalOnEscape(): bool
    {
        return false;
    }

    public static function closeModalOnClickAway(): bool
    {
        return false;
    }

    public function render()
    {
        return view('livewire.employee-form.employee-form-import-excel');
    }
}
