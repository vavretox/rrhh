<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Illuminate\Validation\Rule;
use LivewireUI\Modal\ModalComponent;

class CategoryUpdate extends ModalComponent
{
    public $states;
    public $category;
    public $percentage;
    public $description;
    public $state;

    public function mount($categoryId)
    {
        $this->category = Category::where('id', $categoryId)->first();
        $this->states = ['activo', 'inactivo'];
        $this->percentage = $this->category->percentage;
        $this->description = $this->category->description;
        $this->state = $this->category->state;
    }

    public function rules()
    {
        return [
            'percentage' => [
                'required',
                Rule::unique('categories', 'percentage')->ignore($this->category->id),
                'gt:-1'
            ],
            'description' => [
                'required',
            ],
            'state' => [
                'required',
            ],
        ];
    }

    protected $messages = [
        'percentage.required' => 'El campo nombre no puede estar vacio',
        'percentage.unique' => 'El valor proporcionado ya existe',
        'percentage.gt' => 'El valor debe ser mayor o igual a 0',
        'description.required' => 'El campo no puede estar vacio',
        'state.required' => 'Debe seleccionar un valor para el registro',
    ];

    public function update()
    {
        $this->validate();

        $this->category->update([
            'percentage' => $this->percentage,
            'description' => $this->description,
            'state' => $this->state,
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
        return view('livewire.category.category-update');
    }
}
