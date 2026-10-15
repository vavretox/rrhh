<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario administrador
        Person::create([
            'pension_fund_manager_id' => 1,
            'unique_affiliate_number' => 0,
            'identity_card' => 1234567,
            'first_name' => 'gadt',
            'middle_name' => 'gadt',
            'first_surname' => 'gadt',
            'second_surname' => 'gadt',
            'married_name' => 'gadt',
            'gender' => 'M',
            'date_of_birth' => '2000-01-01',
            'military_service_booklet_number' => '0',
            'social_security_affiliation_code' => '0',
            'identification_document_plugin' => '1A',
            "state" => 'activo',
            "slug" => Str::uuid(),
        ]);

        /*$pensionFundManagerId = 4; // Gestora
        $people = [
            (object)[
                'pension_fund_manager_id' => $pensionFundManagerId,
                // 'unique_affiliate_number' => 0,
                'identity_card' => 1841392,
                'first_name' => 'maya',
                'middle_name' => 'angelica',
                'first_surname' => 'soruco',
                'second_surname' => 'urzagaste',
                'married_name' => '',
                'gender' => 'F',
                'date_of_birth' => '1980-05-22',
                'military_service_booklet_number' => '',
                'social_security_affiliation_code' => '',
                'identification_document_plugin' => '',
                "state" => 'activo',
            ],
            (object)[
                'pension_fund_manager_id' => $pensionFundManagerId,
                // 'unique_affiliate_number' => 0,
                'identity_card' => 7158150,
                'first_name' => 'gabriela',
                'middle_name' => 'nicol',
                'first_surname' => 'almazan',
                'second_surname' => 'mendez',
                'married_name' => '',
                'gender' => 'F',
                'date_of_birth' => '1980-05-23',
                'military_service_booklet_number' => '',
                'social_security_affiliation_code' => '',
                'identification_document_plugin' => '',
                "state" => 'activo',
            ],
            (object)[
                'pension_fund_manager_id' => $pensionFundManagerId,
                // 'unique_affiliate_number' => 0,
                'identity_card' => 4154764,
                'first_name' => 'harold',
                'middle_name' => 'miguel',
                'first_surname' => 'alvarez',
                'second_surname' => 'fernandez',
                'married_name' => '',
                'gender' => 'M',
                'date_of_birth' => '1980-05-24',
                'military_service_booklet_number' => '',
                'social_security_affiliation_code' => '',
                'identification_document_plugin' => '',
                "state" => 'activo',
            ],
            (object)[
                'pension_fund_manager_id' => $pensionFundManagerId,
                // 'unique_affiliate_number' => 0,
                'identity_card' => 4121387,
                'first_name' => 'eduardo',
                'middle_name' => '',
                'first_surname' => 'robles',
                'second_surname' => 'mendoza',
                'married_name' => '',
                'gender' => 'M',
                'date_of_birth' => '1980-05-25',
                'military_service_booklet_number' => '',
                'social_security_affiliation_code' => '',
                'identification_document_plugin' => '',
                "state" => 'activo',
            ],
            (object)[
                'pension_fund_manager_id' => $pensionFundManagerId,
                // 'unique_affiliate_number' => 0,
                'identity_card' => 7109653,
                'first_name' => 'guido',
                'middle_name' => 'hernan',
                'first_surname' => 'ruiz',
                'second_surname' => 'camacho',
                'married_name' => '',
                'gender' => 'M',
                'date_of_birth' => '1980-05-26',
                'military_service_booklet_number' => '',
                'social_security_affiliation_code' => '',
                'identification_document_plugin' => '',
                "state" => 'activo',
            ],
            (object)[
                'pension_fund_manager_id' => $pensionFundManagerId,
                // 'unique_affiliate_number' => 0,
                'identity_card' => 7100240,
                'first_name' => 'alan',
                'middle_name' => 'andres',
                'first_surname' => 'avalos',
                'second_surname' => 'rico',
                'married_name' => '',
                'gender' => 'M',
                'date_of_birth' => '1980-05-27',
                'military_service_booklet_number' => '',
                'social_security_affiliation_code' => '',
                'identification_document_plugin' => '',
                "state" => 'activo',
            ],
        ];

        foreach ($people as $key => $person) {
            Person::create([
                'pension_fund_manager_id' => $person->pension_fund_manager_id,
                'unique_affiliate_number' => $key + 1,
                'identity_card' => $person->identity_card,
                'first_name' => $person->first_name,
                'middle_name' => $person->middle_name,
                'first_surname' => $person->first_surname,
                'second_surname' => $person->second_surname,
                'married_name' => $person->married_name,
                'gender' => $person->gender,
                'date_of_birth' => $person->date_of_birth,
                'military_service_booklet_number' => $person->military_service_booklet_number,
                'social_security_affiliation_code' => $person->social_security_affiliation_code,
                'identification_document_plugin' => $person->identification_document_plugin,
                "state" => $person->state,
                "slug" => Str::uuid(),
            ]);
        }*/
    }
}
