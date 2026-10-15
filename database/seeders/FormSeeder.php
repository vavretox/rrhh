<?php

namespace Database\Seeders;

use App\Models\Form;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periods = [
            (object)['type_id' => 1, 'setting_id' => 1, 'period' => '2025-06-01'],
            (object)['type_id' => 2, 'setting_id' => 1, 'period' => '2025-06-01'],
        ];
        foreach ($periods as $period) {
            Form::create([
                'type_id' => $period->type_id,
                'setting_id' => $period->setting_id,
                'period' => $period->period,
                'state' => 'activo',
                'slug' => Str::uuid(),
            ]);
        }
    }
}
