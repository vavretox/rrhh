<?php

namespace App\Exports;

use App\Models\Person;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PeopleExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return Person::with('PensionFundManager')->orderBy('identity_card')->get();
    }

    public function headings(): array
    {
        return PeopleTemplateExport::headingsList();
    }

    public function map($person): array
    {
        return [
            $person->PensionFundManager?->code,
            $person->unique_affiliate_number,
            $person->identity_card,
            $person->first_name,
            $person->middle_name,
            $person->first_surname,
            $person->second_surname,
            $person->married_name,
            $person->gender,
            $person->date_of_birth,
            $person->military_service_booklet_number,
            $person->social_security_affiliation_code,
            $person->identification_document_plugin,
            $person->state,
        ];
    }
}
