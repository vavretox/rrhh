<?php

namespace App\Livewire\Setting;

use App\Models\Setting;
use LivewireUI\Modal\ModalComponent;

class SettingUpdate extends ModalComponent
{
    public $setting;
    public $states;
    public $national_minimum_wage;
    public $description;
    public $start_date;
    public $end_date;
    public $state;

    public function mount($settingId)
    {
        $this->setting = Setting::where('id', $settingId)->first();
        $this->states = ['activo', 'inactivo'];
        $this->national_minimum_wage = $this->setting->national_minimum_wage;
        $this->description = $this->setting->description;
        $this->start_date = $this->setting->start_date;
        $this->state = $this->setting->state;
    }

    protected $rules = [
        'national_minimum_wage' => 'required',
        'description' => 'required',
        'start_date' => 'required',
        'end_date' => 'required_if:state,inactivo',
        'state' => 'required',
    ];

    protected $messages = [
        'national_minimum_wage.required' => 'El campo no puede estar vacio',
        'description.required' => 'El campo no puede estar vacio',
        'start_date.required' => 'El campo no puede estar vacio',
        'end_date.required_if' => 'Para inactivar el registro se debe ingresar la fecha final',
        'state.required' => 'El campo no puede estar vacio',
    ];

    public function save()
    {
        $this->validate();

        $this->setting->update([
            'national_minimum_wage' => $this->national_minimum_wage,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'state' => $this->state,
        ]);

        $this->closeModal();

        $this->dispatch('pg:eventRefresh-SettingTable');
        $this->dispatch('refresh-canAdd-Setting');
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
        return view('livewire.setting.setting-update');
    }
}
