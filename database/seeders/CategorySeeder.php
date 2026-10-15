<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            (object)['percentage' => 0, 'description' => 'De cero a un año de antiguedad'],
            (object)['percentage' => 5, 'description' => 'De dos a cuatro años de antiguedad'],
            (object)['percentage' => 11, 'description' => 'De cinco a siete años de antiguedad'],
            (object)['percentage' => 18, 'description' => 'De ocho a diez años de antiguedad'],
            (object)['percentage' => 26, 'description' => 'De once a catorce años de antiguedad'],
            (object)['percentage' => 34, 'description' => 'De quince a diecinueve años de antiguedad'],
            (object)['percentage' => 42, 'description' => 'De veinte a veinticuatro años de antiguedad'],
            (object)['percentage' => 50, 'description' => 'De veinticinco a mas años de antiguedad'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'percentage' => $category->percentage,
                'description' => $category->description,
                'state' => 'activo',
                'slug' => Str::uuid(),
            ]);
        }
    }
}
