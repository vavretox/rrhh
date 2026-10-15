<?php

namespace App\Livewire\OrganizationalArea;

use App\Models\OrganizationalArea;
use Illuminate\Validation\Rule;
use LivewireUI\Modal\ModalComponent;

class OrganizationalAreaUpdate extends ModalComponent
{
    public $states;
    public $organizationalArea;
    public $organizationalAreas;
    public $relationship;
    public $name;
    public $description;
    public $code;
    public $state;

    public function mount($organizationalAreaId)
    {
        $this->states = ['activo', 'inactivo'];
        $this->organizationalAreas = OrganizationalArea::where('state', 'activo')->get();
        $this->organizationalArea = OrganizationalArea::where('id', $organizationalAreaId)->first();
        $this->relationship = $this->organizationalArea->relationship < 1 ? 0 : $this->organizationalArea->relationship;
        $this->name = $this->organizationalArea->name;
        $this->description = $this->organizationalArea->description;
        $this->code = $this->organizationalArea->code;
        $this->state = $this->organizationalArea->state;
    }

    public function rules()
    {
        return [
            'relationship' => [
                'required',
            ],
            'name' => [
                'required',
                Rule::unique('organizational_areas')->ignore($this->organizationalArea->id)
            ],
            'description' => [
                'required',
            ],
            'code' => [
                'required',
                Rule::unique('organizational_areas')->ignore($this->organizationalArea->id)
            ],
            'state' => [
                'required'
            ],
        ];
    }

    protected $messages = [
        'relationship.required' => 'Debe seleccionar una relacion de unidad',
        'name.required' => 'El campo nombre no puede estar vacio',
        'name.unique' => 'El valor proporcionado ya existe',
        'description.required' => 'El campo nombre no puede estar vacio',
        'code.required' => 'El campo codigo no puede estar vacio',
        'code.unique' => 'El valor proporcionado ya existe',
        'state.required' => 'Debe seleccionar un estado para el registro',
    ];

    public function update()
    {
        $this->validate();

        $this->organizationalArea->update([
            'entity_id' => 1,
            'relationship' => $this->relationship == 0 ? null : $this->relationship,
            'name' => $this->name,
            'description' => $this->description,
            'code' => $this->code,
            'state' => $this->state,
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
        return view('livewire.organizational-area.organizational-area-update');
    }
}
