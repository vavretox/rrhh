<?php

namespace App\Livewire\PensionFundManager;

use App\Models\PensionFundManager;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PensionFundManagerUpdate extends ModalComponent
{
    public $name;
    public $code;
    public $state;
    public $states;
    public $pensionFundManager;

    public function mount($pensionFundManagerId)
    {
        $this->pensionFundManager = PensionFundManager::where('id', $pensionFundManagerId)->first();
        $this->states = ['activo', 'inactivo'];
        $this->state = $this->pensionFundManager->state;
        $this->name = $this->pensionFundManager->name;
        $this->code = $this->pensionFundManager->code;
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                Rule::unique('pension_fund_managers')->ignore($this->pensionFundManager->id)
            ],
            'code' => [
                'required',
                Rule::unique('pension_fund_managers')->ignore($this->pensionFundManager->id)
            ],
            'state' => [
                'required'
            ],
        ];
    }

    protected $messages = [
        'name.required' => 'El campo nombre no puede estar vacio',
        'name.unique' => 'El valor proporcionado ya existe',
        'code.required' => 'El campo codigo no puede estar vacio',
        'code.unique' => 'El valor proporcionado ya existe',
    ];

    public function update()
    {
        $this->validate();

        $this->pensionFundManager->update([
            'name' => $this->name,
            'code' => $this->code,
            'state' => $this->state,
        ]);

        $this->closeModal();

        $this->dispatch('pg:eventRefresh-PensionFundManagerTable');
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
        return view('livewire.pension-fund-manager.pension-fund-manager-update');
    }
}
