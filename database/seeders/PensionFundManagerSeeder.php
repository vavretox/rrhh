<?php

namespace Database\Seeders;

use App\Models\PensionFundManager;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PensionFundManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pfmList = ['no aporta', 'futuro', 'prevision', 'gestora'];
        foreach ($pfmList as $id => $pfm) {
            PensionFundManager::create([
                'name' => $pfm,
                'code' => $id,
                'state' => 'activo',
                'slug' => Str::uuid(),
            ]);
        }
    }
}
