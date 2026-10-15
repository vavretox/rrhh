<?php

namespace App\Livewire\Charge;

use App\Models\Charge;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Str;

class ChargeCreate extends ModalComponent
{
    public $name;
    public $description;
    public $code;
    public $basic_salary;
    public $state;
    public $states;

    public function mount()
    {
        $this->states = ['activo', 'inactivo'];
        $this->state = 'activo';
    }

    protected $rules = [
        'name' => 'required|unique:charges,name',
        'description' => 'required',
        'code' => 'required|unique:charges,code|gt:0',
        'basic_salary' => 'required|gt:0',
    ];

    protected $messages = [
        'name.required' => 'El campo nombre no puede estar vacio',
        'name.unique' => 'El valor proporcionado ya existe',
        'description.required' => 'El campo nombre no puede estar vacio',
        'code.required' => 'El campo descripcion no puede estar vacio',
        'code.unique' => 'El valor proporcionado ya existe',
        'code.gt' => 'El valor debe ser mayor a 0',
        'basic_salary.required' => 'El campo nombre no puede estar vacio',
        'basic_salary.gt' => 'El valor debe ser mayor a 0',
    ];

    public function save()
    {
        $this->validate();

        Charge::create([
            'name' => $this->name,
            'description' => $this->description,
            'code' => $this->code,
            'basic_salary' => $this->basic_salary,
            'state' => $this->state,
            'slug' => Str::uuid()
        ]);

        $this->closeModal();

        $this->dispatch('pg:eventRefresh-ChargeTable');
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
        return view('livewire.charge.charge-create');
    }
}
