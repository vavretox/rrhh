<?php

namespace App\Livewire\ChargeEmployee;

use App\Models\Charge;
use App\Models\ChargeEmployee;
use App\Models\Employee;
use LivewireUI\Modal\ModalComponent;

class ChargeEmployeeUpdate extends ModalComponent
{
    public $states;
    public $charges;
    public $employees;
    public $chargeEmployee;
    public $charge_id;
    public $employee_id;
    public $start_date;
    public $end_date;
    public $state;

    public function mount($chargeEmployeeId)
    {
        $this->states = ['activo', 'inactivo'];
        $this->chargeEmployee = ChargeEmployee::where('id', $chargeEmployeeId)->first();
        $this->charges = Charge::where('state', 'activo')->get();
        // $this->employees = Employee::where('state', 'activo')->whereNotIn('id', ChargeEmployee::where('state', 'activo')->pluck('employee_id'))->orWhere('id', $this->chargeEmployee->Employee->id)->get();
        $this->charge_id = $this->chargeEmployee->charge_id;
        $this->employee_id = $this->chargeEmployee->employee_id;
        $this->start_date = $this->chargeEmployee->start_date;
        $this->end_date = $this->chargeEmployee->end_date;
        $this->state = $this->chargeEmployee->state;
    }

    protected $rules = [
        'charge_id' => 'required',
        // 'employee_id' => 'required|gt:0',
        'start_date' => 'required',
        'state' => 'required',
        'end_date' => 'required_if:state,inactivo'
    ];

    protected $messages = [
        'charge_id.required' => 'El campo nombre no puede estar vacio',
        // 'employee_id.required' => 'El campo nombre no puede estar vacio',
        // 'employee_id.gt' => 'El campo nombre no puede estar vacio',
        'start_date.required' => 'El campo nombre no puede estar vacio',
        'state.required' => 'Debe seleccionar un estado para el registro',
        'end_date.required_if' => 'Para inactivar el registro debe establecen una fecha finalizacion.',
    ];

    public function update()
    {
        $this->validate();

        $this->chargeEmployee->update([
            'charge_id' => $this->charge_id,
            'employee_id' => $this->employee_id,
            'start_date' => $this->start_date,
            'end_date' => (($this->end_date == '') ? null : $this->end_date),
            'state' => $this->state,
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
        return view('livewire.charge-employee.charge-employee-update');
    }
}
