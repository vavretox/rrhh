<?php

namespace App\Livewire\Setting;

use App\Models\EmployeeForm;
use App\Models\Form;
use App\Models\Setting;
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

final class SettingTable extends PowerGridComponent
{
    public string $tableName = 'SettingTable';

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
        return Setting::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('national_minimum_wage')
            ->add('description')
            ->add('start_date', function ($model) {
                return $model->start_date;
            })
            ->add('end_date', function ($model) {
                return is_null($model->end_date) ? 'En vigencia' : $model->end_date;
            })
            ->add('state')
            ->add('slug')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            // Column::make('Id', 'id'),

            Column::make('SAL. MIN. NAL.', 'national_minimum_wage')
                ->sortable()
                ->searchable(),

            Column::make('DESCRIPCION', 'description')
                ->sortable()
                ->searchable(),

            Column::make('F. INICIO', 'start_date')
                ->sortable(),

            Column::make('F. FIN', 'end_date')
                ->sortable(),

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
            // Filter::datepicker('start_date'),
            // Filter::datepicker('end_date'),
        ];
    }

    #[On('deleteSetting')]
    public function delete($settingId): void
    {
        DB::transaction(function () use ($settingId) {
            $formIds = Form::where('setting_id', $settingId)->pluck('id');

            EmployeeForm::whereIn('form_id', $formIds)->delete();
            Form::whereIn('id', $formIds)->delete();
            Setting::where('id', $settingId)->delete();
        });
    }

    public function actions(Setting $row): array
    {
        $buttons = array();

        if ($row->state != 'inactivo') {
            $settingUpdate =
                Button::add('edit')
                ->slot('
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                        <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                    </svg>
                ')
                ->class('inline-flex items-center px-4 py-2 border border-blue-600 bg-transparent text-blue-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-blue-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
                ->attributes(['type' => 'button'])
                ->openModal('setting.setting-update', ['settingId' => $row->id]);
            array_push($buttons, $settingUpdate);
        }

        $settingDelete =
            Button::add('remove')
            ->slot("Eliminar")
            ->class('inline-flex items-center px-4 py-2 border border-red-600 bg-transparent text-red-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-red-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->attributes(['type' => 'button'])
            ->confirm('Esta seguro de eliminar esta configuracion de la base de datos? Tambien se eliminaran sus planillas asociadas.')
            ->dispatch('deleteSetting', ['settingId' => $row->id]);

        array_push($buttons, $settingDelete);

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
