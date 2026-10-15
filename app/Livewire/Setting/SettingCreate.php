<?php

namespace App\Livewire\Setting;

use App\Models\Setting;
use Carbon\Carbon;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Str;

class SettingCreate extends ModalComponent
{
    public $states;
    public $national_minimum_wage;
    public $description;
    public $start_date;
    public $state;

    public function mount()
    {
        $this->states = ['activo', 'inactivo'];
        $this->state = 'activo';
    }

    protected $rules = [
        'national_minimum_wage' => 'required',
        'description' => 'required',
        'start_date' => 'required',
        'state' => 'required',
    ];

    protected $messages = [
        'national_minimum_wage.required' => 'El campo no puede estar vacio',
        'description.required' => 'El campo no puede estar vacio',
        'start_date.required' => 'El campo no puede estar vacio',
        'state.required' => 'El campo no puede estar vacio',
    ];

    public function save()
    {
        $this->validate();

        if ($this->state === 'activo') {
            Setting::where('state', 'activo')->update([
                'end_date' => Carbon::parse($this->start_date)->subDay()->toDateString(),
                'state' => 'inactivo',
            ]);
        }

        Setting::create([
            'national_minimum_wage' => $this->national_minimum_wage,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => null,
            'state' => $this->state,
            'slug' => Str::uuid(),
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
        return view('livewire.setting.setting-create');
    }
}
