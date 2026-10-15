<?php

namespace Database\Seeders;

use App\Models\AttendanceRule;
use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class AttendanceConfigSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['VAC', 'Vacaciones', true, 15], ['MED', 'Permiso médico', true, null],
            ['PER', 'Permiso personal', false, 5], ['MAT', 'Maternidad', true, null],
            ['PAT', 'Paternidad', true, null],
        ] as [$code, $name, $paid, $limit]) {
            LeaveType::firstOrCreate(['team_id' => null, 'code' => $code], ['name' => $name, 'is_paid' => $paid, 'annual_day_limit' => $limit, 'requires_approval' => true]);
        }

        AttendanceRule::firstOrCreate(['work_schedule_id' => null, 'name' => 'Regla estándar'], [
            'late_tolerance_minutes' => 10, 'early_departure_tolerance_minutes' => 5,
            'minimum_daily_minutes' => 480, 'overtime_after_minutes' => 480,
        ]);
    }
}
