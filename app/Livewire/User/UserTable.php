<?php

namespace App\Livewire\User;

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

final class UserTable extends PowerGridComponent
{
    public string $tableName = 'UserTable';

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
        return User::query()
            ->join('model_has_roles', function ($model_has_roles) {
                $model_has_roles->on('model_has_roles.model_id', '=', 'users.id');
            })
            ->join('roles', function ($roles) {
                $roles->on('model_has_roles.role_id', '=', 'roles.id');
            })
            ->select(['users.*', 'roles.name as role_name']);
    }

    public function relationSearch(): array
    {
        return [
            'Person' => ['identity_card', 'first_name', 'middle_name', 'first_surname', 'second_surname', 'married_name'],
            'roles' => ['name'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('person_id', function ($model) {
                return $model->Person->first_name . ' ' . $model->Person->middle_name . ' ' . $model->Person->first_surname . ' ' . $model->Person->second_surname;
            })
            ->add('email')
            ->add('role', function ($model) {
                return $model->role_name;
            })
            ->add('state')
            ->add('slug')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            // Column::make('Id', 'id'),

            Column::make('PERSONA', 'person_id')
                ->searchable(),

            Column::make('EMAIL', 'email')
                ->sortable()
                ->searchable(),

            Column::make('ROL', 'role')
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
        return [];
    }

    #[On('deleteUser')]
    public function delete($userId): void
    {
        DB::transaction(function () use ($userId) {
            $user = User::where('id', $userId)->first();

            if (is_null($user)) {
                return;
            }

            $user->roles()->detach();
            $user->tokens()->delete();
            DB::table('sessions')->where('user_id', $userId)->delete();
            DB::table('team_user')->where('user_id', $userId)->delete();
            DB::table('teams')->where('user_id', $userId)->delete();
            $user->delete();
        });
    }

    public function actions(User $row): array
    {
        $buttons = array();
        $userUpdate =
            Button::add('edit')
            ->slot('
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                        <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                    </svg>
                ')
            ->class('inline-flex items-center px-4 py-2 border border-blue-600 bg-transparent text-blue-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-blue-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->attributes(['type' => 'button'])
            ->openModal('user.user-update', ['userId' => $row->id]);

        array_push($buttons, $userUpdate);

        $userResetPassword =
            Button::add('reset-password')
            ->slot('
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
                        <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5"/>
                        <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                    </svg>
                ')
            ->class('inline-flex items-center px-4 py-2 border border-purple-600 bg-transparent text-purple-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-purple-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->attributes(['type' => 'button'])
            ->openModal('user.user-reset', ['userId' => $row->id]);

        array_push($buttons, $userResetPassword);

        $userDelete =
            Button::add('remove')
            ->slot("Eliminar")
            ->class('inline-flex items-center px-4 py-2 border border-red-600 bg-transparent text-red-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-red-600 hover:text-white hover:outline-none hover:ring-2 hover:ring-gray-400 hover:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150')
            ->attributes(['type' => 'button'])
            ->confirm('Esta seguro de eliminar este usuario de la base de datos?')
            ->dispatch('deleteUser', ['userId' => $row->id]);

        array_push($buttons, $userDelete);

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
