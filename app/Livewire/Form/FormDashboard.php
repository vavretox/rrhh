<?php

namespace App\Livewire\Form;

use Livewire\Component;

class FormDashboard extends Component
{
    public function render()
    {
        return view('livewire.form.form-dashboard')->layout('layouts.app');
    }
}
