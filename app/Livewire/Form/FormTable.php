<?php

namespace App\Livewire\Form;

use App\Models\EmployeeForm;
use App\Models\Form;
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

final class FormTable extends PowerGridComponent
{
    public string $tableName = 'FormTable';

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
        return Form::query();
    }

    public function relationSearch(): array
    {
        return [
            'Type' => ['name', 'description', 'state'],
            'Setting' => ['national_minimum_wage', 'description', 'state'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('type_id', function ($model) {
                return $model->Type->name;
            })
            ->add('setting_id', function ($model) {
                return $model->Setting->national_minimum_wage;
            })
            ->add('period_formatted', fn(Form $model) => Carbon::parse($model->period)->format('M Y'))
            ->add('state')
            ->add('slug')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            // Column::make('Id', 'id'),

            Column::make('TIPO', 'type_id')
                ->searchable(),

            Column::make('SAL. MIN. NAL.', 'setting_id')
                ->searchable(),

            Column::make('PERIODO', 'period_formatted', 'period')
                ->sortable()
                ->searchable(),

            Column::make('ESTADO', 'state')
                ->sortable()
                ->searchable(),

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
        return [
            // Filter::datepicker('period'),
        ];
    }

    #[On('deleteForm')]
    public function delete($formId): void
    {
        DB::transaction(function () use ($formId) {
            EmployeeForm::where('form_id', $formId)->delete();
            Form::where('id', $formId)->delete();
        });
    }

    public function actions(Form $row): array
    {
        $buttons = array();
        $employeeForm =
            Button::add('employees')
            ->slot("Funcionarios")
            // ->slot('
            //         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-add" viewBox="0 0 16 16">
            //             <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0m-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
            //             <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
            //         </svg>
            //     ')
            ->class('inline-flex items-center px-4 py-2 border border-blue-600 bg-transparent text-blue-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-blue-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->attributes(['type' => 'button'])
            ->openModal('employee-form.employee-form-dashboard', ['formId' => $row->id]);

        $paymentFormDownload =
            Button::add('export')
            ->slot("Planilla")
            // ->slot('
            //         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-spreadsheet" viewBox="0 0 16 16">
            //             <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V9H3V2a1 1 0 0 1 1-1h5.5zM3 12v-2h2v2zm0 1h2v2H4a1 1 0 0 1-1-1zm3 2v-2h3v2zm4 0v-2h3v1a1 1 0 0 1-1 1zm3-3h-3v-2h3zm-7 0v-2h3v2z"/>
            //         </svg>
            //     ')
            ->class('inline-flex items-center px-4 py-2 border border-purple-600 bg-transparent text-purple-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-purple-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->route('export.sheets', ['formId' => $row->id], '_blank');

        $paymentFormCompressDownload =
            Button::add('download-files')
            ->slot("Archivos")
            // ->slot('
            //         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-zip" viewBox="0 0 16 16">
            //             <path d="M6.5 7.5a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v.938l.4 1.599a1 1 0 0 1-.416 1.074l-.93.62a1 1 0 0 1-1.109 0l-.93-.62a1 1 0 0 1-.415-1.074l.4-1.599zm2 0h-1v.938a1 1 0 0 1-.03.243l-.4 1.598.93.62.93-.62-.4-1.598a1 1 0 0 1-.03-.243z"/>
            //             <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm5.5-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9v1H8v1h1v1H8v1h1v1H7.5V5h-1V4h1V3h-1V2h1z"/>
            //         </svg>
            //     ')
            ->class('inline-flex items-center px-4 py-2 border border-red-600 bg-transparent text-red-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-red-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->route('formatted-files.download', ['formId' => $row->id], '_blank');

        $blockForm =
            Button::add('consolidate')
            ->slot("Consolidar")
            // ->slot('
            //         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shield-lock" viewBox="0 0 16 16">
            //             <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
            //             <path d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415"/>
            //         </svg>
            //     ')
            ->class('inline-flex items-center px-4 py-2 border border-cyan-600 bg-transparent text-cyan-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-cyan-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->attributes(['type' => 'button'])
            ->openModal('form.form-consolidate', ['formId' => $row->id]);

        $formDelete =
            Button::add('remove')
            ->slot("Eliminar")
            ->class('inline-flex items-center px-4 py-2 border border-red-600 bg-transparent text-red-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-red-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->attributes(['type' => 'button'])
            ->confirm('Esta seguro de eliminar esta planilla de la base de datos? Tambien se quitaran sus funcionarios asignados.')
            ->dispatch('deleteForm', ['formId' => $row->id]);

        if ($row->state != 'consolidado') {
            array_push($buttons, $employeeForm);
            array_push($buttons, $blockForm);
            array_push($buttons, $formDelete);
        } else {
            array_push($buttons, $employeeForm);
            array_push($buttons, $paymentFormDownload);
            array_push($buttons, $paymentFormCompressDownload);
            array_push($buttons, $formDelete);
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
