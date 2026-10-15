<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            (object)['national_minimum_wage' => 3300.0, 'description' => 'Salario minimo nacional vigente', 'start_date' => '2025-06-01', 'end_date' => null],
        ];

        foreach ($settings as $setting) {
            Setting::create([
                'national_minimum_wage' => $setting->national_minimum_wage,
                'description' => $setting->description,
                'start_date' => $setting->start_date,
                'end_date' => $setting->end_date,
                'state' => 'activo',
                'slug' => Str::uuid(),
            ]);
        }
    }
}
