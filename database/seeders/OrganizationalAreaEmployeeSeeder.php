<?php

namespace Database\Seeders;

use App\Models\OrganizationalAreaEmployee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use illuminate\Support\Str;

class OrganizationalAreaEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizationalAreaEmployees = [
            (object)[
                'organizational_area_id' => 1,
                'employee_id' => 1,
                'start_date' => '2025-01-01',
                'end_date' => null,
                'state' => 'activo',
            ],
            (object)[
                'organizational_area_id' => 1,
                'employee_id' => 2,
                'start_date' => '2025-01-01',
                'end_date' => null,
                'state' => 'activo',
            ],
            (object)[
                'organizational_area_id' => 1,
                'employee_id' => 3,
                'start_date' => '2025-01-01',
                'end_date' => null,
                'state' => 'activo',
            ],
            (object)[
                'organizational_area_id' => 1,
                'employee_id' => 4,
                'start_date' => '2025-01-01',
                'end_date' => null,
                'state' => 'activo',
            ],
            (object)[
                'organizational_area_id' => 1,
                'employee_id' => 5,
                'start_date' => '2025-01-01',
                'end_date' => null,
                'state' => 'activo',
            ],
            (object)[
                'organizational_area_id' => 1,
                'employee_id' => 6,
                'start_date' => '2025-01-01',
                'end_date' => null,
                'state' => 'activo',
            ],
        ];

        foreach ($organizationalAreaEmployees as $organizationalAreaEmployee) {
            OrganizationalAreaEmployee::create([
                'organizational_area_id' => $organizationalAreaEmployee->organizational_area_id,
                'employee_id' => $organizationalAreaEmployee->employee_id,
                'start_date' => $organizationalAreaEmployee->start_date,
                'end_date' => $organizationalAreaEmployee->end_date,
                'state' => $organizationalAreaEmployee->state,
                'slug' => Str::uuid(),
            ]);
        }
    }
}
