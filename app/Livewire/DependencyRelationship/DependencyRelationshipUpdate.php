<?php

namespace App\Livewire\DependencyRelationship;

use App\Models\DependencyRelationship;
use Illuminate\Validation\Rule;
use LivewireUI\Modal\ModalComponent;

class DependencyRelationshipUpdate extends ModalComponent
{
    public $states;
    public $dependencyRelationship;
    public $code;
    public $description;
    public $state;

    public function mount($dependencyRelationshipId)
    {
        $this->states = ['activo', 'inactivo'];
        $this->dependencyRelationship = DependencyRelationship::where('id', $dependencyRelationshipId)->first();
        $this->code = $this->dependencyRelationship->code;
        $this->description = $this->dependencyRelationship->description;
        $this->state = $this->dependencyRelationship->state;
    }

    public function rules()
    {
        return [
            'code' => [
                'required',
                Rule::unique('dependency_relationships')->ignore($this->dependencyRelationship->id)
            ],
            'description' => [
                'required',
                Rule::unique('dependency_relationships')->ignore($this->dependencyRelationship->id)
            ],
            'state' => [
                'required'
            ],
        ];
    }

    protected $messages = [
        'code.required' => 'El campo codigo no puede estar vacio',
        'code.unique' => 'El valor proporcionado ya existe',
        'description.required' => 'El campo descripcion no puede estar vacio',
        'description.unique' => 'El valor proporcionado ya existe',
        'state.required' => 'El campo descripcion no puede estar vacio',
    ];

    public function update()
    {
        $this->validate();

        $this->dependencyRelationship->update([
            'code' => $this->code,
            'description' => $this->description,
            'state' => $this->state,
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
        return view('livewire.dependency-relationship.dependency-relationship-update');
    }
}
