<?php

use Illuminate\Database\Seeder;

class VoteStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $voteStatusData = [
            ['id' => 1, 'vote_status_name_ar' => 'موافق', 'vote_status_name_en' => 'Yes','icon_class_name'=>'fa-check','color_class_name'=>'btn-success'],
            ['id' => 2, 'vote_status_name_ar' => 'ارفض', 'vote_status_name_en' => 'No','icon_class_name'=>'fa-times','color_class_name'=>'btn-danger'],
            ['id' => 3, 'vote_status_name_ar' => 'امتنع عن القرار', 'vote_status_name_en' => 'Abstained','icon_class_name'=>'fa-minus','color_class_name'=>'btn-warning'],
            ['id' => 4, 'vote_status_name_ar' => 'لم يتم القرار بعد', 'vote_status_name_en' => 'Not decided Yet','icon_class_name'=>'fa-minus','color_class_name'=>'btn-warning'],

        ];

        foreach ($voteStatusData as $key => $voteStatus) {
            $exists = DB::table('vote_statuses')->where('id', $voteStatus['id'])->first();
            if(!$exists){
                DB::table('vote_statuses')->insert([$voteStatus]);    
            }
        }
    }
}        