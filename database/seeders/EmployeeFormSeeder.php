<?php

namespace Database\Seeders;

use App\Models\EmployeeForm;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmployeeFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeForms = [
            (object)[
                'employee_id' => 1,
                'form_id' => 1,
                'sheet_id' => 1,
                'worked_days' => 30,
            ],
            (object)[
                'employee_id' => 2,
                'form_id' => 2,
                'sheet_id' => 1,
                'worked_days' => 30,
            ],
        ];

        foreach ($employeeForms as $employeeForm) {
            EmployeeForm::create([
                'employee_id' => $employeeForm->employee_id,
                'form_id' => $employeeForm->form_id,
                'sheet_id' => $employeeForm->sheet_id,
                'worked_days' => $employeeForm->worked_days,
                'border_bonus' => 0,
                'iva' => 0,
                'violations_and_fines' => 0,
                'judicial_withholdings' => 0,
                'other_discounts' => 0,
                'state' => 'activo',
                'slug' => Str::uuid(),
            ]);
        }
    }
}
