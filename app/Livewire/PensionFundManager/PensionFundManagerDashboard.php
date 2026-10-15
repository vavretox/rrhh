<?php

namespace App\Livewire\PensionFundManager;

use Livewire\Component;

class PensionFundManagerDashboard extends Component
{
    public function render()
    {
        return view('livewire.pension-fund-manager.pension-fund-manager-dashboard')->layout('layouts.app');
    }
}
