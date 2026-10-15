<?php

namespace App\Livewire\Person;

use LivewireUI\Modal\ModalComponent;

class PersonImportExcel extends ModalComponent
{
    public function close()
    {
        $this->closeModal();
    }

    public static function modalMaxWidth(): string
    {
        return 'xl';
    }

    public static function closeModalOnEscape(): bool
    {
        return false;
    }

    public static function closeModalOnClickAway(): bool
    {
        return false;
    }

    public function render()
    {
        return view('livewire.person.person-import-excel');
    }
}
