<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::create(['name' => 'admin']); // Administrador del sistema
        $rrhh = Role::create(['name' => 'rrhh']); // Administrador
        $query = Role::create(['name' => 'query']); // Vendedor

        // $stand_update = Permission::create(['name' => 'stand.update'])->syncRoles([$sysadmin, $admin]);
    }
}
