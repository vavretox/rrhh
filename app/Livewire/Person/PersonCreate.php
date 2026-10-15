<?php

namespace App\Livewire\Person;

use App\Models\PensionFundManager;
use App\Models\Person;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Str;

class PersonCreate extends ModalComponent
{
    public $states;
    public $pensionFundManagers;
    public $pension_fund_manager_id;
    public $unique_affiliate_number;
    public $identity_card;
    public $first_name;
    public $middle_name;
    public $first_surname;
    public $second_surname;
    public $married_name;
    public $gender;
    public $date_of_birth;
    public $military_service_booklet_number;
    public $social_security_affiliation_code;
    public $identification_document_plugin;
    public $state;

    public function mount()
    {
        $this->states = ['activo', 'inactivo'];
        $this->pensionFundManagers = PensionFundManager::where('state', 'activo')->get();
        $this->state = 'activo';
    }

    protected $rules = [
        'pension_fund_manager_id' => 'required|gt:0',
        'unique_affiliate_number' => 'required|unique:people,unique_affiliate_number',
        'identity_card' => 'required|unique:people,identity_card',
        'first_name' => 'required',
        //'middle_name' => '',
        //'first_surname' => '',
        //'second_surname' => '',
        //'married_name' => '',
        'gender' => 'required|gt:0',
        'date_of_birth' => 'required',
        //'military_service_booklet_number' => '',
        //'social_security_affiliation_code' => '',
        //'identification_document_plugin' => '',
        'state' => 'required',
    ];

    protected $messages = [
        'pension_fund_manager_id.required' => 'Debe seleccionar una AFP',
        'pension_fund_manager_id.gt' => 'Debe seleccionar una AFP',
        'unique_affiliate_number.required' => 'Este campo no puede estar vacio',
        'unique_affiliate_number.unique' => 'El valor proporcionado ya existe',
        'identity_card.required' => 'Este campo no puede estar vacio',
        'identity_card.unique' => 'El valor proporcionado ya existe',
        'first_name.required' => 'Este campo no puede estar vacio',
        'gender.required' => 'Este campo no puede estar vacio',
        'gender.gt' => 'Este campo no puede estar vacio',
        'date_of_birth.required' => 'Este campo no puede estar vacio',
        'state.required' => 'Este campo no puede estar vacio',
    ];

    public function save()
    {
        $this->validate();

        Person::create([
            'pension_fund_manager_id' => $this->pension_fund_manager_id,
            'unique_affiliate_number' => $this->unique_affiliate_number,
            'identity_card' => $this->identity_card,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'first_surname' => $this->first_surname,
            'second_surname' => $this->second_surname,
            'married_name' => $this->married_name,
            'gender' => $this->gender == 1 ? "M" : "F",
            'date_of_birth' => $this->date_of_birth,
            'military_service_booklet_number' => $this->military_service_booklet_number,
            'social_security_affiliation_code' => $this->social_security_affiliation_code,
            'identification_document_plugin' => $this->identification_document_plugin,
            'state' => $this->state,
            'slug' => Str::uuid()
        ]);

        $this->closeModal();

        $this->dispatch('pg:eventRefresh-PersonTable');
    }

    public function close()
    {
        $this->closeModal();
    }

    //! Tamano
    public static function modalMaxWidth(): string
    {
        return 'xl';
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
        return view('livewire.person.person-create');
    }
}
