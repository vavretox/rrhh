<?php

namespace App\Livewire\Employee;

use App\Models\AcademicDegree;
use App\Models\Category;
use App\Models\DependencyRelationship;
use App\Models\Employee;
use App\Models\Person;
use Illuminate\Validation\Rule;
use LivewireUI\Modal\ModalComponent;

class EmployeeUpdate extends ModalComponent
{
    public $states;
    public $people;
    public $dependencyRelationships;
    public $categories;
    public $academicDegrees;
    public $employee;
    public $person_id;
    public $dependency_relationship_id;
    public $category_id;
    public $academic_degree_id;
    public $item_number;
    public $date_of_entry;
    public $state;

    public function mount($employeeId)
    {
        $this->employee = Employee::where('id', $employeeId)->first();
        $this->states = ['activo', 'inactivo'];
        // $this->people = Person::where('state', 'activo')
        //     ->whereNotIn('id', Employee::pluck('person_id'))
        //     ->orWhere('id', $this->employee->Person->id)
        //     ->get();
        $this->dependencyRelationships = DependencyRelationship::where('state', 'activo')->get();
        $this->categories = Category::where('state', 'activo')->get();
        $this->academicDegrees = AcademicDegree::where('state', 'activo')->get();
        $this->person_id = $this->employee->person_id;
        $this->dependency_relationship_id = $this->employee->dependency_relationship_id;
        $this->category_id = $this->employee->category_id;
        $this->academic_degree_id = $this->employee->academic_degree_id;
        $this->item_number = $this->employee->item_number;
        $this->date_of_entry = $this->employee->date_of_entry;
        $this->state = $this->employee->state;
    }

    public function rules()
    {
        return [
            // 'person_id' => [
            //     'required',
            //     'gt:0',
            //     Rule::unique('employees')->ignore($this->employee->id),
            // ],
            'dependency_relationship_id' => [
                'required',
                'gt:0',
            ],
            'category_id' => [
                'required',
                'gt:-1',
            ],
            'academic_degree_id' => [
                'required',
                'gt:-1',
            ],
            'item_number' => [
                'required',
                'gt:0',
                Rule::unique('employees')->ignore($this->employee->id),
            ],
            'date_of_entry' => [
                'required',
            ],
            'state' => [
                'required',
            ],
        ];
    }

    protected $messages = [
        // 'person_id.required' => 'El campo no puede estar vacio',
        // 'person_id.unique' => 'El valor proporcionado ya existe',
        // 'person_id.gt' => 'Debe seleccionar un elemento',
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

    public function update()
    {
        $this->validate();

        $this->employee->update([
            // 'person_id' => $this->person_id,
            'dependency_relationship_id' => $this->dependency_relationship_id,
            'category_id' => $this->category_id,
            'academic_degree_id' => $this->academic_degree_id,
            'item_number' => $this->item_number,
            'date_of_entry' => $this->date_of_entry,
            'state' => $this->state
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
        return view('livewire.employee.employee-update');
    }
}
