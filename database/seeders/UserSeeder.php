<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario administrador
        $admin = User::create([
            'person_id' => 1,
            'email' => 'admin@admin.com',
            'password' => '4dm1n2o2s',
            'state' => 'activo',
            'slug' => Str::uuid(),
        ]);
        $admin->syncRoles(['admin']);
    }
}
