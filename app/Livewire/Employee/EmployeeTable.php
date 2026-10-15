<?php

namespace App\Livewire\Employee;

use App\Models\ChargeEmployee;
use App\Models\Employee;
use App\Models\EmployeeForm;
use App\Models\OrganizationalAreaEmployee;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class EmployeeTable extends PowerGridComponent
{
    public string $tableName = 'EmployeeTable';

    public function setUp(): array
    {
        //$this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Employee::query();
    }

    public function relationSearch(): array
    {
        return [
            'Person' => ['identity_card', 'first_name', 'middle_name', 'first_surname', 'second_surname', 'married_name'],
            'DependencyRelationship' => ['code', 'description', 'state'],
            'Category' => ['percentage', 'description', 'state'],
            'AcademicDegree' => ['code', 'description', 'state'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('person_id', function ($model) {
                return $model->Person->first_name . ' ' . $model->Person->middle_name . ' ' . $model->Person->first_surname . ' ' . $model->Person->second_surname;
            })
            ->add('dependency_relationship_id', function ($model) {
                return $model->DependencyRelationship->description;
            })
            ->add('category_id', function ($model) {
                return $model->Category->percentage;
            })
            ->add('academic_degree_id', function ($model) {
                return $model->AcademicDegree->description;
            })
            ->add('item_number')
            ->add('date_of_entry_formatted', fn(Employee $model) => Carbon::parse($model->date_of_entry)->format('d/m/Y'))
            ->add('state')
            ->add('slug')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            //Column::make('Id', 'id'),

            Column::make('PERSONA', 'person_id')
                ->searchable(),

            Column::make('RELACION DE DEPENDENCIA', 'dependency_relationship_id')
                ->searchable(),

            Column::make('CAT.', 'category_id')
                ->searchable(),

            Column::make('GRADO', 'academic_degree_id')
                ->searchable(),

            Column::make('ITEM', 'item_number')
                ->sortable()
                ->searchable(),

            Column::make('F. DE INGRESO', 'date_of_entry_formatted', 'date_of_entry')
                ->sortable(),

            Column::make('ESTADO', 'state')
                ->sortable()
                ->searchable(),

            /* Column::make('Slug', 'slug')
                ->sortable()
                ->searchable(),

            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::make('Created at', 'created_at')
                ->sortable()
                ->searchable(), */

            Column::action('ACCIONES')
        ];
    }

    public function filters(): array
    {
        return [
            //Filter::datepicker('date_of_entry'),
        ];
    }

    #[On('deleteEmployee')]
    public function delete($employeeId): void
    {
        DB::transaction(function () use ($employeeId) {
            EmployeeForm::where('employee_id', $employeeId)->delete();
            ChargeEmployee::where('employee_id', $employeeId)->delete();
            OrganizationalAreaEmployee::where('employee_id', $employeeId)->delete();
            Employee::where('id', $employeeId)->delete();
        });
    }

    public function actions(Employee $row): array
    {
        $buttons = array();
        $employeeUpdate =
            Button::add('edit')
            ->slot("Modificar")
            // ->slot('
            //         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
            //             <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
            //         </svg>
            //     ')
            ->class('inline-flex items-center px-4 py-2 border border-blue-600 bg-transparent text-blue-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-blue-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->attributes(['type' => 'button'])
            ->openModal('employee.employee-update', ['employeeId' => $row->id]);

        $chargeEmployee =
            Button::add('charge')
            ->slot("Cargo")
            // ->slot('
            //         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-down-up" viewBox="0 0 16 16">
            //           <path fill-rule="evenodd" d="M11.5 15a.5.5 0 0 0 .5-.5V2.707l3.146 3.147a.5.5 0 0 0 .708-.708l-4-4a.5.5 0 0 0-.708 0l-4 4a.5.5 0 1 0 .708.708L11 2.707V14.5a.5.5 0 0 0 .5.5m-7-14a.5.5 0 0 1 .5.5v11.793l3.146-3.147a.5.5 0 0 1 .708.708l-4 4a.5.5 0 0 1-.708 0l-4-4a.5.5 0 0 1 .708-.708L4 13.293V1.5a.5.5 0 0 1 .5-.5"/>
            //         </svg>
            //     ')
            ->class('inline-flex items-center px-4 py-2 border border-orange-600 bg-transparent text-orange-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-orange-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->attributes(['type' => 'button'])
            ->openModal('charge-employee.charge-employee-dashboard', ['employeeId' => $row->id]);

        $organizationalAreaEmployee =
            Button::add('organizational-area')
            ->slot("A. O.")
            // ->slot('
            //         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-diagram-2" viewBox="0 0 16 16">
            //             <path fill-rule="evenodd" d="M6 3.5A1.5 1.5 0 0 1 7.5 2h1A1.5 1.5 0 0 1 10 3.5v1A1.5 1.5 0 0 1 8.5 6v1H11a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 5 7h2.5V6A1.5 1.5 0 0 1 6 4.5zM8.5 5a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5zM3 11.5A1.5 1.5 0 0 1 4.5 10h1A1.5 1.5 0 0 1 7 11.5v1A1.5 1.5 0 0 1 5.5 14h-1A1.5 1.5 0 0 1 3 12.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm4.5.5a1.5 1.5 0 0 1 1.5-1.5h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1A1.5 1.5 0 0 1 9 12.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/>
            //         </svg>
            //     ')
            ->class('inline-flex items-center px-4 py-2 border border-cyan-600 bg-transparent text-cyan-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-cyan-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->attributes(['type' => 'button'])
            ->openModal('organizational-area-employee.organizational-area-employee-dashboard', ['employeeId' => $row->id]);

        $employeeDelete =
            Button::add('remove')
            ->slot("Eliminar")
            ->class('inline-flex items-center px-4 py-2 border border-red-600 bg-transparent text-red-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-red-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->attributes(['type' => 'button'])
            ->confirm('Esta seguro de eliminar este funcionario de la base de datos?')
            ->dispatch('deleteEmployee', ['employeeId' => $row->id]);

        array_push($buttons, $employeeUpdate);
        array_push($buttons, $chargeEmployee);
        array_push($buttons, $organizationalAreaEmployee);
        array_push($buttons, $employeeDelete);
        return $buttons;
    }

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
