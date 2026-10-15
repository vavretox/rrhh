<?php

namespace App\Livewire\DependencyRelationship;

use App\Models\DependencyRelationship;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Str;

class DependencyRelationshipCreate extends ModalComponent
{
    public $states;
    public $code;
    public $description;
    public $state;

    public function mount()
    {
        $this->states = ['activo', 'inactivo'];
        $this->state = 'activo';
    }

    protected $rules = [
        'code' => 'required|unique:dependency_relationships,code',
        'description' => 'required|unique:dependency_relationships,description',
        'state' => 'required'
    ];

    protected $messages = [
        'code.required' => 'El campo codigo no puede estar vacio',
        'code.unique' => 'El valor proporcionado ya existe',
        'description.required' => 'El campo descripcion no puede estar vacio',
        'description.unique' => 'El valor proporcionado ya existe',
        'state.required' => 'El campo descripcion no puede estar vacio',
    ];

    public function save()
    {
        $this->validate();

        DependencyRelationship::create([
            'code' => $this->code,
            'description' => $this->description,
            'state' => $this->state,
            'slug' => Str::uuid()
        ]);

        $this->closeModal();

        $this->dispatch('pg:eventRefresh-DependencyRelationshipTable');
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
        return view('livewire.dependency-relationship.dependency-relationship-create');
    }
}
