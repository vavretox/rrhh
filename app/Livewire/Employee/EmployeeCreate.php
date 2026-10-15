<?php

namespace App\Livewire\Employee;

use App\Models\AcademicDegree;
use App\Models\Category;
use App\Models\DependencyRelationship;
use App\Models\Employee;
use App\Models\Person;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Str;

class EmployeeCreate extends ModalComponent
{
    public $states;
    public $people;
    public $dependencyRelationships;
    public $categories;
    public $academicDegrees;
    public $person_id;
    public $category_id;
    public $dependency_relationship_id;
    public $academic_degree_id;
    public $item_number;
    public $date_of_entry;
    public $state;

    public function mount()
    {
        $this->states = ['activo', 'inactivo'];
        $this->people = Person::where('state', 'activo')
            ->whereNotIn('id', Employee::pluck('person_id'))
            ->get();
        $this->dependencyRelationships = DependencyRelationship::where('state', 'activo')->get();
        $this->categories = Category::where('state', 'activo')->get();
        $this->academicDegrees = AcademicDegree::where('state', 'activo')->get();
        $this->state = 'activo';
    }

    protected $rules = [
        'person_id' => 'required|unique:employees,person_id|gt:0',
        'dependency_relationship_id' => 'required|gt:0',
        'category_id' => 'required|gt:-1',
        'academic_degree_id' => 'required|gt:-1',
        'item_number' => 'required|unique:employees,item_number|gt:0', // AGREGAR VALIDACION DE LIMITE SUPERIOR PARA EL RANGO
        'date_of_entry' => 'required',
        'state' => 'required',
    ];

    protected $messages = [
        'person_id.required' => 'El campo no puede estar vacio',
        'person_id.unique' => 'El valor proporcionado ya existe',
        'person_id.gt' => 'Debe seleccionar un elemento',
        'dependency_relationship_id.required' => 'El campo no puede estar vacio',
        'dependency_relationship_id.gt' => 'Debe seleccionar un elemento',
        'category_id.required' => 'El campo no puede estar vacio',
        'category_id.gt' => 'Debe seleccionar un elemento',
        'academic_degree_id.required' => 'El campo no puede estar vacio',
        'academic_degree_id.gt' => 'Debe seleccionar un elemento',
        'item_number.required' => 'El campo no puede estar vacio',
        'item_number.unique' => 'El valor proporcionado ya existe',
        'item_number.gt' => 'Numero de item no valido',
        'date_of_entry.required' => 'El campo no puede estar vacio',
        'state.required' => 'Debe seleccionar un estado para el registro',
    ];

    public function save()
    {
        $this->validate();

        Employee::create([
            'person_id' => $this->person_id,
            'dependency_relationship_id' => $this->dependency_relationship_id,
            'category_id' => $this->category_id,
            'academic_degree_id' => $this->academic_degree_id,
            'item_number' => $this->item_number,
            'date_of_entry' => $this->date_of_entry,
            'state' => $this->state,
            'slug' => Str::uuid()
        ]);

        $this->closeModal();

        $this->dispatch('pg:eventRefresh-EmployeeTable');
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
        return view('livewire.employee.employee-create');
    }
}
