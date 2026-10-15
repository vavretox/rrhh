<?php

namespace App\Livewire\Form;

use App\Models\Form;
use App\Models\Setting;
use App\Models\Type;
use Illuminate\Support\Carbon;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FormCreate extends ModalComponent
{
    public $states;
    public $types;
    public $type_id;
    public $setting;
    public $setting_state;
    public $national_minimum_wage;
    public $period;
    public $state;

    public function mount()
    {
        $this->states = ['activo', 'inactivo'];
        $this->types = Type::where('state', 'activo')->get();
        $this->checkSetting();
        $this->state = 'activo';
    }

    public function checkSetting()
    {
        $setting_check = Setting::where('state', 'activo')->count();
        if ($setting_check == 1) {
            $this->setting = Setting::where('state', 'activo')->first();
            $this->national_minimum_wage = $this->setting->national_minimum_wage;
            $this->setting_state = true;
        } else {
            $this->setting_state = false;
        }
    }

    protected function rules()
    {
        return [
            'type_id' => 'required|gt:0',
            'period' => [
                'required',
                Rule::unique('forms')->where(function ($query) {
                    return $query->where('type_id', $this->type_id);
                }),
            ],
        ];
    }

    protected $messages = [
        'type_id.required' => 'El campo no puede estar vacio',
        'type_id.gt' => 'Debe seleccionar un elemento',
        'period.required' => 'El campo no puede estar vacio',
        'period.unique' => 'Ya tiene un formulario en el mes seleccionado',
    ];

    public function save()
    {
        $this->validate();

        Form::create([
            'type_id' => $this->type_id,
            'setting_id' => $this->setting->id,
            'period' => $this->period,
            'state' => 'activo',
            'slug' => Str::uuid(),
        ]);

        $this->closeModal();

        $this->dispatch('pg:eventRefresh-FormTable');
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
        return view('livewire.form.form-create');
    }
}
