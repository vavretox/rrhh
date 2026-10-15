<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PensionFundManagerSeeder::class);
        $this->call(PersonSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(EntitySeeder::class);
        $this->call(OrganizationalAreaSeeder::class);
        $this->call(ChargeSeeder::class);
        $this->call(DependencyRelationshipSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(SheetSeeder::class);
        $this->call(TypeSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(FormSeeder::class);
        $this->call(AcademicDegreeSeeder::class);
        //$this->call(EmployeeSeeder::class);
        //$this->call(ChargeEmployeeSeeder::class);
        //$this->call(OrganizationalAreaEmployeeSeeder::class);
        //$this->call(EmployeeFormSeeder::class);
    }
}
