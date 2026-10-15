<?php

namespace App\Livewire\Category;

use Livewire\Component;

class CategoryDashboard extends Component
{
    public function render()
    {
        return view('livewire.category.category-dashboard')->layout('layouts.app');
    }
}
