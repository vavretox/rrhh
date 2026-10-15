<?php

namespace Database\Seeders;

use App\Models\Entity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Entity::create([
            'name' => "GOBIERNO AUTONOMO DEPARTAMENTAL DE TARIJA",
            'code' => "906",
            'nit' => "178928029",
            'state' => "activo",
            'slug' => Str::uuid(),
        ]);
    }
}
