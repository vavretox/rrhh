<?php

namespace Database\Seeders;

use App\Models\Sheet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SheetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sheets = [
            (object)['number' => '1', 'description' => 'Bolivianos menores de 65 años con aportes AFP'],
            (object)['number' => '2', 'description' => 'Bolivianos mayores de 65 años con aportes AFP'],
            (object)['number' => '3', 'description' => 'Bolivianos menores de 65 años sin aportes AFP'],
            (object)['number' => '4', 'description' => 'Bolivianos mayores de 65 años sin aportes AFP'],
        ];
        foreach ($sheets as $sheet) {
            Sheet::create([
                'number' => $sheet->number,
                'description' => $sheet->description,
                'state' => 'activo',
                'slug' => Str::uuid(),
            ]);
        }
    }
}
