<?php

namespace App\Exports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return Employee::with(['Person', 'DependencyRelationship', 'Category', 'AcademicDegree'])
            ->orderBy('item_number')
            ->get();
    }

    public function headings(): array
    {
        return EmployeesTemplateExport::headingsList();
    }

    public function map($employee): array
    {
        return [
            $employee->Person?->identity_card,
            $employee->DependencyRelationship?->code,
            $employee->Category?->percentage,
            $employee->AcademicDegree?->code,
            $employee->item_number,
            $employee->date_of_entry,
            $employee->state,
        ];
    }
}
