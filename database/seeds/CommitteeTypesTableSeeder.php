<?php


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommitteeTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'id'=>1,
                'committee_type_name_ar' => 'دائمة',
                'committee_type_name_en' => 'Permanent',
            ],
            [
                'id'=>2,
                'committee_type_name_ar' => ' مؤقتة',
                'committee_type_name_en' => 'Temporary',
            ],
        ];

        foreach ($types as $key => $type) {
            $exists = DB::table('committee_types')->where('id', $type['id'])->first();
            if(!$exists){
                DB::table('committee_types')->insert([$type]);    
            }
        }
    }
}
