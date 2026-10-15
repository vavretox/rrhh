<?php

namespace Database\Seeders;

use App\Models\AcademicDegree;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AcademicDegreeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicDegrees = [
            (object)['code' => 1, 'description' => 'Doctorado'],
            (object)['code' => 2, 'description' => 'Maestria'],
            (object)['code' => 3, 'description' => 'Licenciatura'],
            (object)['code' => 4, 'description' => 'Egresado de la universidad'],
            (object)['code' => 5, 'description' => 'Tecnico superior'],
            (object)['code' => 6, 'description' => 'Universitario'],
            (object)['code' => 7, 'description' => 'Tecnico medio'],
            (object)['code' => 8, 'description' => 'Bachiller'],
            (object)['code' => 9, 'description' => 'Secundaria'],
            (object)['code' => 10, 'description' => 'Primaria'],
        ];

        foreach ($academicDegrees as $academicDegree) {
            AcademicDegree::create([
                'code' => $academicDegree->code,
                'description' => $academicDegree->description,
                'state' => 'activo',
                'slug' => Str::uuid()
            ]);
        }
    }
}
