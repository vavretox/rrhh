<?php

namespace App\Livewire\Charge;

use App\Models\Charge;
use Illuminate\Validation\Rule;
use LivewireUI\Modal\ModalComponent;

class ChargeUpdate extends ModalComponent
{
    public $states;
    public $charge;
    public $name;
    public $description;
    public $code;
    public $basic_salary;
    public $state;

    public function mount($chargeId)
    {
        $this->states = ['activo', 'inactivo'];
        $this->charge = Charge::where('id', $chargeId)->first();
        $this->name = $this->charge->name;
        $this->description = $this->charge->description;
        $this->code = $this->charge->code;
        $this->basic_salary = $this->charge->basic_salary;
        $this->state = $this->charge->state;
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                Rule::unique('charges')->ignore($this->charge->id)
            ],
            'description' => [
                'required',
            ],
            'code' => [
                'required',
                Rule::unique('charges')->ignore($this->charge->id),
                'gt:0'
            ],
            'basic_salary' => [
                'required',
                'gt:0'
            ],
            'state' => [
                'required'
            ],
        ];
    }

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

    public function update()
    {
        $this->validate();

        $this->charge->update([
            'name' => $this->name,
            'description' => $this->description,
            'code' => $this->code,
            'basic_salary' => $this->basic_salary,
            'state' => $this->state,
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
        return view('livewire.charge.charge-update');
    }
}
