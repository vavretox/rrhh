<?php

namespace Database\Seeders;

use App\Models\Charge;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChargeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $charges = [
            (object)['name' => 'GOBERNADOR', 'description' => 'GOBERNADOR', 'code' => 1, 'basic_salary' => 14000, 'state' => 'activo'],
            (object)['name' => 'VICE GOBERNADOR', 'description' => 'VICE GOBERNADOR', 'code' => 2, 'basic_salary' => 13500, 'state' => 'activo'],
            (object)['name' => 'ASAMBLEISTA DEPARTAMENTAL', 'description' => 'ASAMBLEISTA DEPARTAMENTAL', 'code' => 3, 'basic_salary' => 13000, 'state' => 'activo'],
            (object)['name' => 'SECRETARIO DEPARTAMENTAL', 'description' => 'SECRETARIO DEPARTAMENTAL', 'code' => 4, 'basic_salary' => 11000, 'state' => 'activo'],
            (object)['name' => 'SUB GOBERNADOR', 'description' => 'SUB GOBERNADOR', 'code' => 5, 'basic_salary' => 9500, 'state' => 'activo'],
            (object)['name' => 'DIRECTOR I', 'description' => 'DIRECTOR I', 'code' => 6, 'basic_salary' => 8500, 'state' => 'activo'],
            (object)['name' => 'DIRECTOR II', 'description' => 'DIRECTOR II', 'code' => 7, 'basic_salary' => 7500, 'state' => 'activo'],
            (object)['name' => 'JEFE DE UNIDAD', 'description' => 'JEFE DE UNIDAD', 'code' => 8, 'basic_salary' => 6400, 'state' => 'activo'],
            (object)['name' => 'RESPONSABLE DE AREA', 'description' => 'RESPONSABLE DE AREA', 'code' => 9, 'basic_salary' => 6060, 'state' => 'activo'],
            (object)['name' => 'ASESOR', 'description' => 'ASESOR', 'code' => 10, 'basic_salary' => 5750, 'state' => 'activo'],
            (object)['name' => 'PROFESIONAL ESPECIALIZADO I', 'description' => 'PROFESIONAL ESPECIALIZADO I', 'code' => 11, 'basic_salary' => 5600, 'state' => 'activo'],
            (object)['name' => 'PROFESIONAL ESPECIALIZADO II', 'description' => 'PROFESIONAL ESPECIALIZADO II', 'code' => 12, 'basic_salary' => 5200, 'state' => 'activo'],
            (object)['name' => 'PROFESIONAL I', 'description' => 'PROFESIONAL I', 'code' => 13, 'basic_salary' => 5050, 'state' => 'activo'],
            (object)['name' => 'PROFESIONAL II', 'description' => 'PROFESIONAL II', 'code' => 14, 'basic_salary' => 4091, 'state' => 'activo'],
            (object)['name' => 'PROFESIONAL III', 'description' => 'PROFESIONAL III', 'code' => 15, 'basic_salary' => 3500, 'state' => 'activo'],
            (object)['name' => 'TECNICO ESPECIALIZADO', 'description' => 'TECNICO ESPECIALIZADO', 'code' => 16, 'basic_salary' => 3411, 'state' => 'activo'],
            (object)['name' => 'TECNICO ESPECIALIZADO I', 'description' => 'TECNICO ESPECIALIZADO I', 'code' => 17, 'basic_salary' => 3300, 'state' => 'activo'],
            (object)['name' => 'TECNICO ESPECIALIZADO II', 'description' => 'TECNICO ESPECIALIZADO II', 'code' => 18, 'basic_salary' => 3030, 'state' => 'activo'],
            (object)['name' => 'AUXILIAR ADMINISTRATIVO', 'description' => 'AUXILIAR ADMINISTRATIVO', 'code' => 19, 'basic_salary' => 2750, 'state' => 'activo'],
            (object)['name' => 'TECNICO I', 'description' => 'TECNICO I', 'code' => 20, 'basic_salary' => 2499, 'state' => 'inactivo'],
            (object)['name' => 'TECNICO II', 'description' => 'TECNICO II', 'code' => 21, 'basic_salary' => 2400, 'state' => 'inactivo'],
        ];

        foreach ($charges as $charge) {
            Charge::create([
                'name' => $charge->name,
                'description' => $charge->description,
                'code' => $charge->code,
                'basic_salary' => $charge->basic_salary,
                'state' => $charge->state,
                'slug' => Str::uuid(),
            ]);
        }
    }
}
