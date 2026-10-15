<?php

namespace App\Livewire\EmployeeForm;

use App\Models\EmployeeForm;
use App\Models\Sheet;
use LivewireUI\Modal\ModalComponent;

class EmployeeFormUpdate extends ModalComponent
{
    // public $states;
    public $employeeForm;

    public $employee_id;
    public $form_id;

    public $sheets;

    public $sheet_id;
    public $worked_days;
    public $border_bonus;
    public $iva;
    public $violations_and_fines;
    public $judicial_withholdings;
    public $other_discounts;
    public $state;

    public function mount($employeeFormId)
    {
        // $this->states = ['activo', 'inactivo'];
        $this->employeeForm = EmployeeForm::where('id', $employeeFormId)->first();
        $this->sheets = Sheet::where('state', 'activo')->get();

        $this->employee_id = $this->employeeForm->employee_id;
        $this->form_id = $this->employeeForm->form_id;

        $this->sheet_id = $this->employeeForm->sheet_id;
        $this->worked_days = $this->employeeForm->worked_days;
        $this->border_bonus = $this->employeeForm->border_bonus;
        $this->iva = $this->employeeForm->iva;
        $this->violations_and_fines = $this->employeeForm->violations_and_fines;
        $this->judicial_withholdings = $this->employeeForm->judicial_withholdings;
        $this->other_discounts = $this->employeeForm->other_discounts;
        $this->state = $this->employeeForm->state;
    }

    protected $rules = [
        'sheet_id' => 'required|gt:0',
        'worked_days' => 'required',
        'border_bonus' => 'required',
        'iva' => 'required',
        'violations_and_fines' => 'required',
        'judicial_withholdings' => 'required',
        'other_discounts' => 'required',
    ];

    protected $messages = [
        'sheet_id.required' => 'El campo no puede estar vacio',
        'sheet_id.gt' => 'El campo no puede estar vacio',
        'worked_days' => 'El campo no puede estar vacio',
        'border_bonus' => 'El campo no puede estar vacio',
        'iva' => 'El campo no puede estar vacio',
        'violations_and_fines' => 'El campo no puede estar vacio',
        'judicial_withholdings' => 'El campo no puede estar vacio',
        'other_discounts' => 'El campo no puede estar vacio',
    ];

    public function update()
    {
        $this->validate();

        $this->employeeForm->update([
            'employee_id' => $this->employee_id,
            'form_id' => $this->form_id,
            'sheet_id' => $this->sheet_id,
            'worked_days' => $this->worked_days,
            'border_bonus' => $this->border_bonus,
            'iva' => $this->iva,
            'violations_and_fines' => $this->violations_and_fines,
            'judicial_withholdings' => $this->judicial_withholdings,
            'other_discounts' => $this->other_discounts,
            'state' => $this->state,
        ]);

        $this->closeModal();

        $this->dispatch('pg:eventRefresh-EmployeeFormTable');
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
        return view('livewire.employee-form.employee-form-update');
    }
}
