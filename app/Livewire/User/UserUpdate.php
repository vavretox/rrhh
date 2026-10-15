<?php

namespace App\Livewire\User;

use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserUpdate extends ModalComponent
{
    public $user;
    public $states;
    // public $people;
    public $roles;
    public $person_id;
    public $email;
    public $password;
    public $password_confirmation;
    public $role;
    public $state;

    public function mount($userId)
    {
        $this->user = User::where('id', $userId)->first();
        $this->states = ['activo', 'inactivo'];
        // $this->people = Person::where('state', 'activo')->whereNotIn('id', User::where('state', 'activo')->pluck('person_id'))->orWhere('id', $this->user->Person->id)->get();
        $this->roles = Role::all();
        $this->person_id = $this->user->person_id;
        $this->email = $this->user->email;
        // $this->password = $this->user->password;
        // $this->password_confirmation = $this->user->password_confirmation;
        $this->role = $this->user->getRoleNames()[0];
        $this->state = $this->user->state;
    }

    public function rules()
    {
        return [
            // 'person_id' => [
            //     'required',
            //     Rule::unique('users', 'person_id')->ignore($this->user->id),
            //     'gt:0',
            // ],
            'email' => [
                'required',
                Rule::unique('users', 'email')->ignore($this->user->id),
            ],
            'state' => [
                'required',
            ],
            'role' => [
                'required',
            ],
        ];
    }

    protected $rules = [
        // 'person_id' => 'required|unique:users,person_id|gt:0',
        'email' => 'required|unique:users,email|email',
        // 'password' => 'required|confirmed',
        // 'password_confirmation' => 'required',
        'state' => 'required',
        'role' => 'required'
    ];

    protected $messages = [
        // 'person_id.required' => 'El campo no puede estar vacio',
        // 'person_id.unique' => 'El valor proporcionado ya existe',
        // 'person_id.gt' => 'Debe seleccionar un elemento',
        'email.required' => 'El campo no puede estar vacio',
        'email.unique' => 'El valor proporcionado ya existe',
        'email.email' => 'El correo no tiene el formato correcto',
        // 'password.required' => 'El campo no puede estar vacio',
        // 'password.confirmed' => 'Las contraseñas no coinciden',
        // 'password_confirmation.required' => 'El campo no puede estar vacio',
        'state.required' => 'Debe seleccionar un estado para el registro',
        'role.required' => 'El campo no puede estar vacio',
    ];

    public function update()
    {
        $this->validate();

        $this->user->update([
            // 'person_id' => $this->person_id,
            'email' => $this->email,
            // 'password' => Hash::make($this->password),
            'state' => $this->state,
            // 'slug' => Str::uuid(),
        ]);

        $this->user->syncRoles([$this->role]);

        $this->closeModal();

        $this->dispatch('pg:eventRefresh-UserTable');
    }

    public function close()
    {
        $this->closeModal();
    }

    //! Tamano
    public static function modalMaxWidth(): string
    {
        return 'lg';
    }

    //! Cerrar con escape
    public static function closeModalOnEscape(): bool
    {
        return false;
    }

    //! Cerrar con clic
    public static function closeModalOnClickAway(): bool
    {
        return false;
    }

    public function render()
    {
        return view('livewire.user.user-update');
    }
}
