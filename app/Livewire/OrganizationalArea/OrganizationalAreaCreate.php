<?php

namespace App\Livewire\OrganizationalArea;

use App\Models\Entity;
use App\Models\OrganizationalArea;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Str;

class OrganizationalAreaCreate extends ModalComponent
{
    public $states;
    public $organizationalAreas;
    public $relationship;
    public $name;
    public $description;
    public $code;
    public $state;

    public function mount()
    {
        $this->states = ['activo', 'inactivo'];
        $this->state = 'activo';
        $this->organizationalAreas = OrganizationalArea::where('state', 'activo')->get();
        $this->relationship = 0;
    }

    protected $rules = [
        'relationship' => 'required',
        'name' => 'required|unique:organizational_areas,name',
        'description' => 'required',
        'code' => 'required|unique:organizational_areas,code',
        'state' => 'required',
    ];

    protected $messages = [
        'relationship.required' => 'Debe seleccionar una relacion de unidad',
        'name.required' => 'El campo nombre no puede estar vacio',
        'name.unique' => 'El valor proporcionado ya existe',
        'description.required' => 'El campo nombre no puede estar vacio',
        'code.required' => 'El campo codigo no puede estar vacio',
        'code.unique' => 'El valor proporcionado ya existe',
        'state.required' => 'Debe seleccionar un estado para el registro',
    ];

    public function save()
    {
        $this->validate();

        OrganizationalArea::create([
            'entity_id' => 1,
            'relationship' => $this->relationship == 0 ? null : $this->relationship,
            'name' => $this->name,
            'description' => $this->description,
            'code' => $this->code,
            'state' => $this->state,
            'slug' => Str::uuid(),
        ]);

        $this->closeModal();

        $this->dispatch('pg:eventRefresh-OrganizationalAreaTable');
    }

    public function close()
    {
        $this->closeModal();
    }

    //! Tamano
    public static function modalMaxWidth(): string
    {
        return 'sm';
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
        return view('livewire.organizational-area.organizational-area-create');
    }
}
