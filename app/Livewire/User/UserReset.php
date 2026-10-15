<?php

namespace App\Livewire\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use LivewireUI\Modal\ModalComponent;

class UserReset extends ModalComponent
{
    public $user;
    public $password;
    public $password_confirmation;

    public function mount($userId)
    {
        $this->user = User::where('id', $userId)->first();
    }

    protected $rules = [
        'password' => 'required|confirmed',
        'password_confirmation' => 'required',
    ];

    protected $messages = [
        'password.required' => 'El campo no puede estar vacio',
        'password.confirmed' => 'Las contraseñas no coinciden',
        'password_confirmation.required' => 'El campo no puede estar vacio',
    ];

    public function reset_password()
    {
        $this->validate();

        $this->user->update([
            'password' => Hash::make($this->password),
        ]);

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
        return view('livewire.user.user-reset');
    }
}
