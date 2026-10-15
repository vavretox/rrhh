<?php

namespace App\Livewire\Person;

use Livewire\Component;

class PersonDashboard extends Component
{
    public function render()
    {
        return view('livewire.person.person-dashboard')->layout('layouts.app');
    }
}
