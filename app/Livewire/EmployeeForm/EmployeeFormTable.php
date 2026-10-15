<?php

namespace App\Livewire\EmployeeForm;

use App\Models\EmployeeForm;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class EmployeeFormTable extends PowerGridComponent
{
    public string $tableName = 'EmployeeFormTable';
    public $formId;

    public function setUp(): array
    {
        // $this->showCheckBox();

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
        return EmployeeForm::where('form_id', $this->formId);
    }

    public function relationSearch(): array
    {
        return [
            'Employee' => [
                'Person' => ['identity_card', 'first_name', 'middle_name', 'first_surname', 'second_surname', 'married_name'],
            ],
            'Sheet' => ['number', 'description', 'state'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('employee_id', function ($model) {
                return $model->Employee->Person->first_name
                    . ' ' . $model->Employee->Person->middle_name
                    . ' ' . $model->Employee->Person->first_surname
                    . ' ' . $model->Employee->Person->second_surname;
            })
            ->add('form_id')
            ->add('sheet_id')
            ->add('worked_days')
            ->add('border_bonus', function ($model) {
                return is_null($model->border_bonus) ? 0 : $model->border_bonus;
            })
            ->add('iva', function ($model) {
                return is_null($model->function) ? 0 : $model->function;
            })
            ->add('violations_and_fines', function ($model) {
                return is_null($model->violations_and_fines) ? 0 : $model->violations_and_fines;
            })
            ->add('judicial_withholdings', function ($model) {
                return is_null($model->judicial_withholdings) ? 0 : $model->judicial_withholdings;
            })
            ->add('other_discounts', function ($model) {
                return is_null($model->other_discounts) ? 0 : $model->other_discounts;
            })
            ->add('state')
            ->add('slug')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            // Column::make('Id', 'id'),
            Column::make('FUNCIONARIO', 'employee_id')
                ->searchable(),
            // Column::make('Form id', 'form_id'),
            Column::make('TIPO', 'sheet_id')
                ->searchable(),
            Column::make('DIAS TR.', 'worked_days')
                ->sortable()
                ->searchable(),

            Column::make('BONO FR.', 'border_bonus')
                ->sortable()
                ->searchable(),

            Column::make('IVA', 'iva')
                ->sortable()
                ->searchable(),

            Column::make('FALTAS Y M.', 'violations_and_fines')
                ->sortable()
                ->searchable(),

            Column::make('RETENCION JUD.', 'judicial_withholdings')
                ->sortable()
                ->searchable(),

            Column::make('OTROS DES.', 'other_discounts')
                ->sortable()
                ->searchable(),

            // Column::make('ESTADO', 'state')
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

            Column::action('ACCIONES')
        ];
    }

    public function filters(): array
    {
        return [];
    }

    #[On('deleteEmployeeForm')]
    public function delete($employeeFormId): void
    {
        EmployeeForm::where('id', $employeeFormId)->delete();
    }

    #[On('payment_slip')]
    public function payment_slip($employeeFormId): void
    {
        $efd = EmployeeForm::where('id', $employeeFormId)->first();
        $efd->payment_slip();
    }

    public function actions(EmployeeForm $row): array
    {
        $buttons = array();

        $employeeFormUpdate =
            Button::add('edit')
            ->slot("Modificar")
            // ->slot('
            //         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
            //             <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
            //         </svg>
            //     ')
            ->class('inline-flex items-center px-4 py-2 border border-blue-600 bg-transparent text-blue-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-blue-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->attributes(['type' => 'button'])
            ->openModal('employee-form.employee-form-update', ['employeeFormId' => $row->id]);

        $employeeFormDelete =
            Button::add('remove')
            ->slot("Quitar")
            // ->slot('
            //         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
            //             <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
            //             <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
            //         </svg>
            //     ')
            ->class('inline-flex items-center px-4 py-2 border border-red-600 bg-transparent text-red-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-red-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->attributes(['type' => 'button'])
            ->confirm('Esta seguro de quitar este funcionario de la planilla y eliminar el registro de la base de datos?')
            ->dispatch('deleteEmployeeForm', ['employeeFormId' => $row->id]);

        $paymentSlip =
            Button::add('payment-slip')
            ->slot("Boleta de pago")
            // ->slot('
            //         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
            //             <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
            //             <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/>
            //         </svg>
            //     ')
            ->class('inline-flex items-center px-4 py-2 border border-cyan-600 bg-transparent text-cyan-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-cyan-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->route('pdf.payment-slip', ['employeeFormSlug' => $row->slug], '_blank');

        if ($row->Form->state != "consolidado") {
            array_push($buttons, $employeeFormUpdate);
            array_push($buttons, $employeeFormDelete);
        } else {
            array_push($buttons, $paymentSlip);
        }
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
