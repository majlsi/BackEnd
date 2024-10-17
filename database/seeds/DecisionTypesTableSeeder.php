<?php

use Illuminate\Database\Seeder;

class DecisionTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $voteTypesData = [
            ['id' => 1, 'decision_type_name_ar' => 'للمعلومية', 'decision_type_name_en' => 'For information','organization_id' => null,'is_system' => 1],
            ['id' => 2, 'decision_type_name_ar' => 'للإحالة', 'decision_type_name_en' => 'To assign','organization_id' => null,'is_system' => 1],
            ['id' => 3, 'decision_type_name_ar' => 'للتنفيذ', 'decision_type_name_en' => 'For execution','organization_id' => null,'is_system' => 1],
            ['id' => 4, 'decision_type_name_ar' => 'مؤجل', 'decision_type_name_en' => 'Postponed','organization_id' => null,'is_system' => 1],
        ];

        foreach ($voteTypesData as $key => $voteType) {
            $exists = DB::table('decision_types')->where('id', $voteType['id'])->first();
            if(!$exists){
                DB::table('decision_types')->insert([$voteType]);    
            } else {
                DB::table('decision_types')
                    ->where('id', $voteType['id'])
                    ->update($voteType);
            }
        }
    }
}        