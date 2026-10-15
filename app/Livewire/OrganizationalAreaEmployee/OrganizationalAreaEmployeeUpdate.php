<?php

namespace App\Livewire\OrganizationalAreaEmployee;

use App\Models\Employee;
use App\Models\OrganizationalArea;
use App\Models\OrganizationalAreaEmployee;
use LivewireUI\Modal\ModalComponent;

class OrganizationalAreaEmployeeUpdate extends ModalComponent
{
    public $organizationalAreas;
    public $employees;
    public $states;
    public $organizationalAreaEmployee;
    public $organizational_area_id;
    public $employee_id;
    public $start_date;
    public $end_date;
    public $state;

    public function mount($organizationalAreaEmployeeId)
    {
        $this->states = ['activo', 'inactivo'];
        $this->organizationalAreaEmployee = OrganizationalAreaEmployee::where('id', $organizationalAreaEmployeeId)->first();
        $this->organizationalAreas = OrganizationalArea::where('state', 'activo')->orderBy('name', 'asc')->get();
        // $this->employees = Employee::where('state', 'activo')
        //     ->whereNotIn('id', OrganizationalAreaEmployee::where('state', 'activo')->pluck('employee_id'))
        //     ->orWhere('id', $this->organizationalAreaEmployee->Employee->id)
        //     ->get();
        $this->organizational_area_id = $this->organizationalAreaEmployee->organizational_area_id;
        $this->employee_id = $this->organizationalAreaEmployee->employee_id;
        $this->start_date = $this->organizationalAreaEmployee->start_date;
        $this->end_date = is_null($this->organizationalAreaEmployee->end_date) ? null : $this->organizationalAreaEmployee->end_date;
        $this->state = $this->organizationalAreaEmployee->state;
    }

    public function rules()
    {
        return [
            'organizational_area_id' => [
                'required',
                'gt:0'
            ],
            // 'employee_id' => [
            //     'required',
            //     'gt:0'
            // ],
            'start_date' => [
                'required',
            ],
            'end_date' => [
                'required_if:state,inactivo',
            ],
            'state' => [
                'required'
            ],
        ];
    }

    protected $messages = [
        'organizational_area_id.required' => 'El campo no puede estar vacio',
        'organizational_area_id.gt' => 'Debe seleccionar un elemento',
        // 'employee_id.required' => 'El campo no puede estar vacio',
        // 'employee_id.gt' => 'Debe seleccionar un elemento',
        'start_date.required' => 'El campo no puede estar vacio',
        'end_date.required_if' => 'Para inactivar el registro debe establecen una fecha finalizacion',
        'state.required' => 'Debe seleccionar un estado para el registro',
    ];

    public function update()
    {
        $this->validate();

        $this->organizationalAreaEmployee->update([
            'organizational_area_id' => $this->organizational_area_id,
            // 'employee_id' => $this->employee_id,
            'start_date' => $this->start_date,
            'end_date' => ($this->end_date == '') ? null : $this->end_date,
            'state' => $this->state,
            // 'slug' => Str::uuid(),
        ]);

        $this->closeModal();

        $this->dispatch('pg:eventRefresh-OrganizationalAreaEmployeeTable');
        $this->dispatch('refresh-canAdd-OrganizationalAreaEmployee');
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
        return view('livewire.organizational-area-employee.organizational-area-employee-update');
    }
}
