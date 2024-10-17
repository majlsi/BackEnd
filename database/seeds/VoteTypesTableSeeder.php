<?php

use Illuminate\Database\Seeder;

class VoteTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $voteTypesData = [
            ['id' => 1, 'vote_type_name_ar' => 'أثناء اﻷجتماع', 'vote_type_name_en' => 'During meeting'],
            ['id' => 2, 'vote_type_name_ar' => 'لفترة محددة', 'vote_type_name_en' => 'For specific time'],
        ];

        foreach ($voteTypesData as $key => $voteType) {
            $exists = DB::table('vote_types')->where('id', $voteType['id'])->first();
            if(!$exists){
                DB::table('vote_types')->insert([$voteType]);    
            }
        }
    }
}        