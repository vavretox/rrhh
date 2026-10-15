<?php

namespace App\Livewire\ChargeEmployee;

use App\Models\Charge;
use App\Models\ChargeEmployee;
use App\Models\Employee;
use App\Models\Person;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Str;

class ChargeEmployeeCreate extends ModalComponent
{
    public $states;
    public $charges;
    public $employees;
    public $charge_id;
    public $employee_id;
    public $start_date;
    public $end_date;
    public $state;

    public function mount($employee_id)
    {
        $this->employee_id = $employee_id;
        $this->states = ['activo', 'inactivo'];
        $this->charges = Charge::where('state', 'activo')->get();
        // $this->employees = Employee::where('state', 'activo')->whereNotIn('id', ChargeEmployee::where('state', 'activo')->pluck('employee_id'))->get();
        $this->state = 'activo';
    }

    protected $rules = [
        'charge_id' => 'required',
        // 'employee_id' => 'required',
        'start_date' => 'required',
        'state' => 'required',
    ];

    protected $messages = [
        'charge_id.required' => 'El campo nombre no puede estar vacio',
        // 'employee_id.required' => 'El campo nombre no puede estar vacio',
        'start_date.required' => 'El campo nombre no puede estar vacio',
        'state.required' => 'Debe seleccionar un estado para el registro',
    ];

    public function save()
    {
        $this->validate();

        ChargeEmployee::create([
            'charge_id' => $this->charge_id,
            'employee_id' => $this->employee_id,
            'start_date' => $this->start_date,
            'end_date' => null,
            'state' => $this->state,
            'slug' => Str::uuid(),
        ]);

        $this->closeModal();

        $this->dispatch('pg:eventRefresh-ChargeEmployeeTable');
        $this->dispatch('refresh-canAdd-ChargeEmployee');
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
        return view('livewire.charge-employee.charge-employee-create');
    }
}
