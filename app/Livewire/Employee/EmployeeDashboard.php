<?php

namespace App\Livewire\Employee;

use Livewire\Component;

class EmployeeDashboard extends Component
{
    public function render()
    {
        return view('livewire.employee.employee-dashboard')->layout('layouts.app');
    }
}
