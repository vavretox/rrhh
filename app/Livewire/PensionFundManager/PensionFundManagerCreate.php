<?php

namespace App\Livewire\PensionFundManager;

use App\Models\PensionFundManager;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Str;

class PensionFundManagerCreate extends ModalComponent
{
    public $name;
    public $code;
    public $state;
    public $states;

    public function mount()
    {
        $this->states = ['activo', 'inactivo'];
        $this->state = 'activo';
        $this->name = '';
        $this->code = '';
    }

    protected $rules = [
        'name' => 'required|unique:pension_fund_managers,name',
        'code' => 'required|unique:pension_fund_managers,code',
    ];

    protected $messages = [
        'name.required' => 'El campo nombre no puede estar vacio',
        'name.unique' => 'El valor proporcionado ya existe',
        'code.required' => 'El campo codigo no puede estar vacio',
        'code.unique' => 'El valor proporcionado ya existe',
    ];

    public function save()
    {
        $this->validate();

        PensionFundManager::create([
            'name' => $this->name,
            'code' => $this->code,
            'state' => $this->state,
            'slug' => Str::uuid()
        ]);

        $this->closeModal();

        $this->dispatch('pg:eventRefresh-PensionFundManagerTable');
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
        return view('livewire.pension-fund-manager.pension-fund-manager-create');
    }
}
