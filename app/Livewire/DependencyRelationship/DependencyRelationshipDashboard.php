<?php

namespace App\Livewire\DependencyRelationship;

use Livewire\Component;

class DependencyRelationshipDashboard extends Component
{
    public function render()
    {
        return view('livewire.dependency-relationship.dependency-relationship-dashboard')->layout('layouts.app');
    }
}
