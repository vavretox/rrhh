<?php

namespace App\Livewire\EmployeeForm;

use App\Models\Employee;
use App\Models\EmployeeForm;
use App\Models\Form;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use Illuminate\Support\Str;

final class EmployeeFormAddingTable extends PowerGridComponent
{
    public string $tableName = 'EmployeeFormAddingTable';
    public $formId;

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function header(): array
    {
        return [
            Button::add('bulk-verify-electoral-record')
                ->slot('Agregar seleccion (<span x-text="window.pgBulkActions.count(\'' . $this->tableName . '\')"></span>)')
                ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
                ->attributes(['type' => 'button'])
                ->dispatch('bulkVerifyElectoralRecord', []),
            // Button::add('bulk-revert-verify-electoral-record')
            //     ->slot('Revertir seleccion (<span x-text="window.pgBulkActions.count(\'' . $this->tableName . '\')"></span>)')
            //     ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
            //     ->dispatch('bulkRevertVerifyElectoralRecord', []),
        ];
    }

    #[On('bulkVerifyElectoralRecord')]
    public function bulkVerifyElectoralRecord(): void
    {
        $form = Form::findOrFail($this->formId);
        if ($this->checkboxValues) {
            $employeeIds = $this->eligibleEmployeesQuery($form)
                ->whereKey($this->checkboxValues)
                ->pluck('id');

            foreach ($employeeIds as $value) {
                EmployeeForm::create([
                    'employee_id' => $value,
                    'form_id' => $form->id,
                    'sheet_id' => 1,
                    'worked_days' => 30,
                    'border_bonus' => 0,
                    'iva' => 0,
                    'violations_and_fines' => 0,
                    'judicial_withholdings' => 0,
                    'other_discounts' => 0,
                    'state' => 'activo',
                    'slug' => Str::uuid(),
                ]);
                // $ep = ElectoralRecord::find($value);
                // $ep->update([
                //     'verified' => 1
                // ]);
            }
            $this->dispatch('close-adding-modal');

            // $this->dispatch('pg:eventRefresh-EmployeeFormCreate');

        }
    }

    public function datasource(): Builder
    {
        $form = Form::findOrFail($this->formId);

        return $this->eligibleEmployeesQuery($form);
    }

    private function eligibleEmployeesQuery(Form $form): Builder
    {
        return Employee::query()
            ->where('state', 'activo')
            ->whereNotIn(
                'id',
                EmployeeForm::whereIn(
                    'form_id',
                    Form::where('period', $form->period)->pluck('id')
                )->pluck('employee_id')
            );
    }

    public function relationSearch(): array
    {
        return [
            'Person' => ['identity_card', 'first_name', 'middle_name', 'first_surname', 'second_surname', 'married_name'],
            'DependencyRelationship' => ['code', 'description', 'state'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('person_id', function ($model) {
                return $model->Person->first_name . ' ' .
                    $model->Person->middle_name . ' ' .
                    $model->Person->first_surname . ' ' .
                    $model->Person->second_surname;
            })
            ->add('dependency_relationship_id', function ($model) {
                return $model->DependencyRelationship->description;
            })
            ->add('category_id')
            ->add('item_number')
            ->add('date_of_entry_formatted', fn(Employee $model) => Carbon::parse($model->date_of_entry)->format('d/m/Y'))
            ->add('state')
            ->add('slug')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            // Column::make('Id', 'id'),

            Column::make('FUNCIONARIO', 'person_id')
                ->searchable(),

            Column::make('RELACION DE DEPENDENCIA', 'dependency_relationship_id')
                ->searchable(),

            // Column::make('Category id', 'category_id'),

            Column::make('NUMERO DE ITEM', 'item_number')
                ->sortable()
                ->searchable(),

            // Column::make('Date of entry', 'date_of_entry_formatted', 'date_of_entry')
            //     ->sortable(),

            // Column::make('State', 'state')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('Slug', 'slug')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('Created at', 'created_at_formatted', 'created_at')
            //     ->sortable(),

            // Column::make('Created at', 'created_at')
            //     ->sortable()
            //     ->searchable(),

            // Column::action('ACCIONES')
        ];
    }

    public function filters(): array
    {
        return [
            Filter::datepicker('date_of_entry'),
        ];
    }

    // public function actions(Employee $row): array
    // {
    //     return [
    //         Button::add('edit')
    //             ->slot('Edit: ' . $row->id)
    //             ->id()
    //             ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
    //             ->dispatch('edit', ['rowId' => $row->id])
    //     ];
    // }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
