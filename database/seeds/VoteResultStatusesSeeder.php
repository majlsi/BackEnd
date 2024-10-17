<?php

use Illuminate\Database\Seeder;

class VoteResultStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $records=[
            ['id' => 1, 'vote_result_status_name_ar' => 'مصدق عليه', 'vote_result_status_name_en' => 'Approved'],
            ['id' => 2, 'vote_result_status_name_ar' => 'مرفوض', 'vote_result_status_name_en' => 'Rejected'],
            ['id' => 3, 'vote_result_status_name_ar' => 'متعادل', 'vote_result_status_name_en' => 'Balanced'],
            ['id' => 4, 'vote_result_status_name_ar' => 'لم يتم التصويت', 'vote_result_status_name_en' => 'No votes'],
            ['id' => 5, 'vote_result_status_name_ar' => 'تحت الاجراء', 'vote_result_status_name_en' => 'In progress'],
        ];

        foreach ($records as $key => $record) {
            $exists = DB::table('vote_result_statuses')->where('id', $record['id'])->first();
            if(!$exists){
                DB::table('vote_result_statuses')->insert([$record]);    
            } else {
                DB::table('vote_result_statuses')
                    ->where('id', $record['id'])
                    ->update($record);
            }
        }
    }
}
