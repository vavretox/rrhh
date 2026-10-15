<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PeopleTemplateExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public static function headingsList(): array
    {
        return [
            'afp_codigo',
            'nua',
            'cedula',
            'primer_nombre',
            'segundo_nombre',
            'primer_apellido',
            'segundo_apellido',
            'apellido_casada',
            'genero',
            'fecha_nacimiento',
            'libreta_militar',
            'codigo_seguro_social',
            'complemento_ci',
            'estado',
        ];
    }

    public function headings(): array
    {
        return self::headingsList();
    }

    public function collection(): Collection
    {
        return collect([
            [
                'AFP',
                '123456',
                '1234567',
                'JUAN',
                'CARLOS',
                'PEREZ',
                'GOMEZ',
                '',
                'M',
                '1990-01-31',
                '',
                '',
                '',
                'activo',
            ],
        ]);
    }
}
