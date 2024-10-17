<?php

use Illuminate\Database\Seeder;

class OrganizationTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $organizationTypesData = [
            ['organization_type_name_ar' => 'Cloud', 'organization_type_name_en' => 'Cloud'],
            ['organization_type_name_ar' => 'On Premises', 'organization_type_name_en' => 'On Premises']
        ];

        foreach ($organizationTypesData as $key => $organizationType) {
            $exists = DB::table('organization_types')->where('organization_type_name_ar', $organizationType['organization_type_name_ar'])->where('organization_type_name_en', $organizationType['organization_type_name_en'])->first();
            if(!$exists){
                DB::table('organization_types')->insert([$organizationType]);    
            }
        }
    }
}
