<?php

namespace Database\Seeders;

use App\Models\ChargeEmployee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChargeEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chargeEmployees = [
            (object)['charge_id' => 2, 'employee_id' => 1, 'start_date' => '2025-01-01', 'end_date' => null, 'state' => 'activo',],
            (object)['charge_id' => 11, 'employee_id' => 2, 'start_date' => '2025-01-01', 'end_date' => null, 'state' => 'activo',],
            (object)['charge_id' => 12, 'employee_id' => 3, 'start_date' => '2025-01-01', 'end_date' => null, 'state' => 'activo',],
            (object)['charge_id' => 12, 'employee_id' => 4, 'start_date' => '2025-01-01', 'end_date' => null, 'state' => 'activo',],
            (object)['charge_id' => 12, 'employee_id' => 5, 'start_date' => '2025-01-01', 'end_date' => null, 'state' => 'activo',],
            (object)['charge_id' => 15, 'employee_id' => 6, 'start_date' => '2025-01-01', 'end_date' => null, 'state' => 'activo',],
        ];

        foreach ($chargeEmployees as $chargeEmployee) {
            ChargeEmployee::create([
                'charge_id' => $chargeEmployee->charge_id,
                'employee_id' => $chargeEmployee->employee_id,
                'start_date' => $chargeEmployee->start_date,
                'end_date' => $chargeEmployee->end_date,
                'state' => $chargeEmployee->state,
                'slug' => Str::uuid(),
            ]);
        }
    }
}
