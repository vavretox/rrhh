<?php

namespace App\Livewire\OrganizationalArea;

use App\Models\Employee;
use Livewire\Attributes\On;
use Livewire\Component;

class OrganizationalAreaDashboard extends Component
{
    public $employee;
    public $canAdd;

    public function mount($employeeId)
    {
        $this->employee = Employee::where('id', $employeeId)->first();
        $this->refresh();
    }

    #[On('refresh-canAdd-OrganizationalAreaEmployee')]
    public function refresh()
    {
        $this->canAdd = $this->employee->OrganizationalAreas()->wherePivot('state', 'activo')->count() == 0;
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
        return true;
    }

    //! Cerrar con clic
    public static function closeModalOnClickAway(): bool
    {
        return false;
    }

    public function render()
    {
        return view('livewire.organizational-area.organizational-area-dashboard');
    }
}
