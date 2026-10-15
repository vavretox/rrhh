<?php

namespace App\Livewire\OrganizationalAreaEmployee;

use App\Models\Employee;
use App\Models\OrganizationalArea;
use App\Models\OrganizationalAreaEmployee;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Str;

class OrganizationalAreaEmployeeCreate extends ModalComponent
{
    public $organizationalAreas;
    public $employees;
    public $states;
    public $organizational_area_id;
    public $employee_id;
    public $start_date;
    public $state;

    public function mount($employee_id)
    {
        $this->states = ['activo', 'inactivo'];
        $this->organizationalAreas = OrganizationalArea::where('state', 'activo')->orderBy('name', 'asc')->get();
        $this->employee_id = $employee_id;
        // $this->employees = Employee::where('state', 'activo')
        //     ->whereNotIn('id', OrganizationalAreaEmployee::where('state', 'activo')->pluck('employee_id'))
        //     ->get();
        $this->state = 'activo';
    }

    protected $rules = [
        'organizational_area_id' => 'required|gt:0',
        // 'employee_id' => 'required|gt:0',
        'start_date' => 'required',
        'state' => 'required',
    ];

    protected $messages = [
        'organizational_area_id.required' => 'El campo no puede estar vacio',
        'organizational_area_id.gt' => 'Debe seleccionar un elemento',
        // 'employee_id.required' => 'El campo no puede estar vacio',
        // 'employee_id.gt' => 'Debe seleccionar un elemento',
        'start_date.required' => 'El campo no puede estar vacio',
        'state.required' => 'Debe seleccionar un estado para el registro',
    ];

    public function save()
    {
        $this->validate();

        OrganizationalAreaEmployee::create([
            'organizational_area_id' => $this->organizational_area_id,
            'employee_id' => $this->employee_id,
            'start_date' => $this->start_date,
            'end_date' => null,
            'state' => $this->state,
            'slug' => Str::uuid(),
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
        return view('livewire.organizational-area-employee.organizational-area-employee-create');
    }
}
