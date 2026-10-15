<?php

namespace App\Livewire\OrganizationalArea;

use App\Models\OrganizationalArea;
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

final class OrganizationalAreaTable extends PowerGridComponent
{
    public string $tableName = 'OrganizationalAreaTable';

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
        return OrganizationalArea::query();
    }

    public function relationSearch(): array
    {
        return [
            'ParentArea' => ['name', 'description', 'code', 'state'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('entity_id')
            ->add('relationship', fn($model) => !is_null($model->relationship) ? OrganizationalArea::where('id', $model->relationship)->first()->name : "Ninguno")
            ->add('name')
            ->add('description')
            ->add('code')
            ->add('state')
            ->add('slug')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            //Column::make('Id', 'id'),

            //Column::make('ENTIDAD', 'entity_id'),

            Column::make('UOS', 'relationship')
                ->searchable(),

            Column::make('NOMBRE DE LA UNIDAD ORGANIZACIONAL', 'name')
                ->sortable()
                ->searchable(),

            Column::make('DESCRIPCION', 'description')
                ->sortable()
                ->searchable(),

            Column::make('CODIGO', 'code')
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
        return [];
    }

    #[On('deleteOrganizationalArea')]
    public function delete($organizationalAreaId): void
    {
        DB::transaction(function () use ($organizationalAreaId) {
            OrganizationalAreaEmployee::where('organizational_area_id', $organizationalAreaId)->delete();
            OrganizationalArea::where('relationship', $organizationalAreaId)->update(['relationship' => null]);
            OrganizationalArea::where('id', $organizationalAreaId)->delete();
        });
    }

    public function actions(OrganizationalArea $row): array
    {
        $buttons = array();
        $organizationalAreaUpdate =
            Button::add('edit')
            ->slot('
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                        <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                    </svg>
                ')
            ->class('inline-flex items-center px-4 py-2 border border-transparent bg-blue-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150 rounded')
            ->attributes(['type' => 'button'])
            ->openModal('organizational-area.organizational-area-update', ['organizationalAreaId' => $row->id]);

        $organizationalAreaDelete =
            Button::add('remove')
            ->slot("Eliminar")
            ->class('inline-flex items-center px-4 py-2 border border-red-600 bg-transparent text-red-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-red-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->attributes(['type' => 'button'])
            ->confirm('Esta seguro de eliminar esta area organizacional de la base de datos? Tambien se quitaran sus asignaciones.')
            ->dispatch('deleteOrganizationalArea', ['organizationalAreaId' => $row->id]);

        array_push($buttons, $organizationalAreaUpdate);
        array_push($buttons, $organizationalAreaDelete);
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
