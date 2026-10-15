<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            (object)[
                'person_id' => 2,
                'dependency_relationship_id' => 1,
                'category_id' => 1,
                'academic_degree_id' => 3,
                'item_number' => 1111,
                'date_of_entry' => '2025-07-01',
            ],
            (object)[
                'person_id' => 3,
                'dependency_relationship_id' => 1,
                'category_id' => 2,
                'academic_degree_id' => 3,
                'item_number' => 2222,
                'date_of_entry' => '2025-07-01',
            ],
            (object)[
                'person_id' => 4,
                'dependency_relationship_id' => 1,
                'category_id' => 1,
                'academic_degree_id' => 3,
                'item_number' => 3333,
                'date_of_entry' => '2025-07-01',
            ],
            (object)[
                'person_id' => 5,
                'dependency_relationship_id' => 1,
                'category_id' => 6,
                'academic_degree_id' => 3,
                'item_number' => 4444,
                'date_of_entry' => '2025-07-01',
            ],
            (object)[
                'person_id' => 6,
                'dependency_relationship_id' => 1,
                'category_id' => 2,
                'academic_degree_id' => 3,
                'item_number' => 5555,
                'date_of_entry' => '2025-07-01',
            ],
            (object)[
                'person_id' => 7,
                'dependency_relationship_id' => 1,
                'category_id' => 1,
                'academic_degree_id' => 3,
                'item_number' => 6666,
                'date_of_entry' => '2025-07-01',
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create([
                'person_id' => $employee->person_id,
                'dependency_relationship_id' => $employee->dependency_relationship_id,
                'category_id' => $employee->category_id,
                'academic_degree_id' => $employee->academic_degree_id,
                'item_number' => $employee->item_number,
                'date_of_entry' => $employee->date_of_entry,
                "state" => 'activo',
                "slug" => Str::uuid(),
            ]);
        }
    }
}
