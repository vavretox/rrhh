<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeesTemplateExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public static function headingsList(): array
    {
        return [
            'cedula',
            'relacion_dependencia_codigo',
            'categoria_porcentaje',
            'grado_academico_codigo',
            'numero_item',
            'fecha_ingreso',
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
                '1234567',
                'TIP',
                '0',
                '1',
                '1001',
                '2024-01-01',
                'activo',
            ],
        ]);
    }
}
