<?php

namespace Database\Seeders;

use App\Models\DependencyRelationship;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DependencyRelationshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dependencyRelationships = ["FUNCIONARIO DEPENDIENTE", "PERSONAL A CONTRATO POR PRODUCTO"];

        foreach ($dependencyRelationships as $key => $dependencyRelationship) {
            DependencyRelationship::create([
                'code' => $key + 1,
                'description' => $dependencyRelationship,
                "state" => "activo",
                "slug" => Str::uuid(),
            ]);
        }
    }
}
