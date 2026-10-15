<?php

namespace App\Livewire\Form;

use App\Models\Form;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Str;

class FormConsolidate extends ModalComponent
{
    public $form;
    public $consolidate_code;
    public $consolidate_input;

    public function mount($formId)
    {
        $this->form = Form::where('id', $formId)->first();
        $this->consolidate_code = Str::random(8);
    }

    public function rules()
    {
        return [
            'consolidate_input' => 'required|in:' . $this->consolidate_code,
        ];
    }

    protected $messages = [
        'consolidate_input.required' => 'El campo no puede estar vacio',
        'consolidate_input.in' => 'El codigo proporcionado es incorrecto',
    ];

    public function consolidate()
    {
        $this->validate();

        $this->form->update([
            'state' => 'consolidado'
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
        return view('livewire.form.form-consolidate');
    }
}
