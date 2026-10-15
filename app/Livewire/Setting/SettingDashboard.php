<?php

namespace App\Livewire\Setting;

use App\Models\Setting;
use Livewire\Attributes\On;
use Livewire\Component;

class SettingDashboard extends Component
{
    public function render()
    {
        return view('livewire.setting.setting-dashboard')->layout('layouts.app');
    }
}
