<?php

namespace App\Livewire\Category;

use App\Models\Category;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Str;

class CategoryCreate extends ModalComponent
{
    public $states;
    public $percentage;
    public $description;
    public $state;

    public function mount()
    {
        $this->states = ['activo', 'inactivo'];
        $this->state = 'activo';
    }

    protected $rules = [
        'percentage' => 'required|unique:categories,percentage|gt:-1',
        'description' => 'required',
        'state' => 'required',
    ];

    protected $messages = [
        'percentage.required' => 'El campo nombre no puede estar vacio',
        'percentage.unique' => 'El valor proporcionado ya existe',
        'percentage.gt' => 'El valor debe ser mayor o igual a 0',
        'description.required' => 'El campo no puede estar vacio',
        'state.required' => 'Debe seleccionar un valor para el registro',
    ];

    public function save()
    {
        $this->validate();

        Category::create([
            'percentage' => $this->percentage,
            'description' => $this->description,
            'state' => $this->state,
            'slug' => Str::uuid()
        ]);

        $this->closeModal();

        $this->dispatch('pg:eventRefresh-CategoryTable');
    }

    public function close()
    {
        $this->closeModal();
    }

    //! Tamano
    public static function modalMaxWidth(): string
    {
        return 'lg';
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
        return view('livewire.category.category-create');
    }
}
