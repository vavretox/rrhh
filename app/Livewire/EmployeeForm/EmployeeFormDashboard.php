<?php

namespace App\Livewire\EmployeeForm;

use App\Models\EmployeeForm;
use App\Models\Form;
use LivewireUI\Modal\ModalComponent;

class EmployeeFormDashboard extends ModalComponent
{
    public $form;
    public function mount($formId)
    {
        $this->form = Form::where('id', $formId)->first();
    }

    public function close()
    {
        $this->closeModal();
    }

    //! Tamano
    public static function modalMaxWidth(): string
    {
        return '7xl';
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
        return view('livewire.employee-form.employee-form-dashboard');
    }
}
