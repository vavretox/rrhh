<?php

namespace Database\Seeders;

use App\Models\Type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            (object)['name' => 'Banco', 'description' => 'Para funcionarios que reciben pago por medio de cuenta bancaria'],
            (object)['name' => 'Cheque', 'description' => 'Para funcionarios que reciben pago por medio de cheque'],
        ];
        foreach ($types as $type) {
            Type::create([
                'name' => $type->name,
                'description' => $type->description,
                'state' => 'activo',
                'slug' => Str::uuid(),
            ]);
        }
    }
}
