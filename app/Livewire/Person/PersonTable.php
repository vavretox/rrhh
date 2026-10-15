<?php

namespace App\Livewire\Person;

use App\Models\ChargeEmployee;
use App\Models\Employee;
use App\Models\EmployeeForm;
use App\Models\OrganizationalAreaEmployee;
use App\Models\Person;
use App\Models\User;
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

final class PersonTable extends PowerGridComponent
{
    public string $tableName = 'PersonTable';

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
        return Person::query();
    }

    public function relationSearch(): array
    {
        return [
            'PensionFundManager' => ['name', 'code', 'state'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('pension_fund_manager_id', function ($model) {
                return $model->PensionFundManager->name;
            })
            ->add('unique_affiliate_number')
            ->add('identity_card')
            ->add('first_name')
            ->add('middle_name')
            ->add('first_surname')
            ->add('second_surname')
            ->add('married_name')
            ->add('gender')
            ->add('date_of_birth_formatted', fn(Person $model) => Carbon::parse($model->date_of_birth)->format('d/m/Y'))
            ->add('military_service_booklet_number')
            ->add('social_security_affiliation_code')
            ->add('identification_document_plugin')
            ->add('state')
            ->add('slug')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            //Column::make('Id', 'id'),

            Column::make('AFP', 'pension_fund_manager_id')
                ->searchable(),

            Column::make('NRO AFILIADO', 'unique_affiliate_number')
                ->sortable()
                ->searchable(),

            Column::make('NRO DE CI', 'identity_card')
                ->sortable()
                ->searchable(),

            Column::make('PRIMER NOMBRE', 'first_name')
                ->sortable()
                ->searchable(),

            Column::make('SEGUNDO NOMBRE', 'middle_name')
                ->sortable()
                ->searchable(),

            Column::make('APELLIDO PATERNO', 'first_surname')
                ->sortable()
                ->searchable(),

            Column::make('APELLIDO MATERNO', 'second_surname')
                ->sortable()
                ->searchable(),

            Column::make('APELLIDO DE CASADA', 'married_name')
                ->sortable()
                ->searchable(),

            Column::make('GENERO', 'gender')
                ->sortable()
                ->searchable(),

            Column::make('FECHA DE NACIMIENTO', 'date_of_birth_formatted', 'date_of_birth')
                ->sortable(),

            Column::make('NRO LIBRETA SERVICIO MILITAR', 'military_service_booklet_number')
                ->sortable()
                ->searchable(),

            Column::make('CODIGO SEGURO SOCIAL', 'social_security_affiliation_code')
                ->sortable()
                ->searchable(),

            Column::make('COMPLEMENTO', 'identification_document_plugin')
                ->sortable()
                ->searchable(),

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
            //Filter::datepicker('date_of_birth'),
        ];
    }

    #[On('deletePerson')]
    public function delete($personId): void
    {
        DB::transaction(function () use ($personId) {
            $employeeIds = Employee::where('person_id', $personId)->pluck('id');

            EmployeeForm::whereIn('employee_id', $employeeIds)->delete();
            ChargeEmployee::whereIn('employee_id', $employeeIds)->delete();
            OrganizationalAreaEmployee::whereIn('employee_id', $employeeIds)->delete();
            Employee::whereIn('id', $employeeIds)->delete();
            User::where('person_id', $personId)->delete();
            Person::where('id', $personId)->delete();
        });
    }

    public function actions(Person $row): array
    {
        $buttons = array();
        $personUpdate =
            Button::add('edit')
            ->slot("Modificar")
            // ->slot('
            //         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
            //             <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
            //         </svg>
            //     ')
            ->class('inline-flex items-center px-4 py-2 border border-blue-600 bg-transparent text-blue-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-blue-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->attributes(['type' => 'button'])
            ->openModal('person.person-update', ['personId' => $row->id]);

        $personDelete =
            Button::add('remove')
            ->slot("Eliminar")
            ->class('inline-flex items-center px-4 py-2 border border-red-600 bg-transparent text-red-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-red-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->attributes(['type' => 'button'])
            ->confirm('Esta seguro de eliminar esta persona de la base de datos?')
            ->dispatch('deletePerson', ['personId' => $row->id]);

        array_push($buttons, $personUpdate);
        array_push($buttons, $personDelete);
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
