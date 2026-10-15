<?php

namespace App\Livewire\EmployeeForm;

use Livewire\Attributes\On;
use LivewireUI\Modal\ModalComponent;

class EmployeeFormCreate extends ModalComponent
{
    public $formId;

    public function mount($formId)
    {
        $this->reset();
        $this->formId = $formId;
    }

    // close-adding-modal
    #[On('close-adding-modal')]
    public function refresh()
    {
        $this->dispatch('pg:eventRefresh-EmployeeFormTable');
        $this->close();
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
        return view('livewire.employee-form.employee-form-create');
    }
}
