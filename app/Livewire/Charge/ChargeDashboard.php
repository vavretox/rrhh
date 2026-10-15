<?php

namespace App\Livewire\Charge;

use Livewire\Component;

class ChargeDashboard extends Component
{
    public function render()
    {
        return view('livewire.charge.charge-dashboard')->layout('layouts.app');
    }
}
