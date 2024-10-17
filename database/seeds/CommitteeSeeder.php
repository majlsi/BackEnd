<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommitteeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $records = [
            [
                'committee_name_en' => 'Stakeholders Committee',
                'committee_name_ar' => 'لجنة المساهمين',
                'committee_code' => 'SC',
                'is_system' => 1,
                'committeee_members_count' => 0,
            ]
        ];
        try {
            foreach ($records as $key => $record) {
                $exists = DB::table('committees')->where('committee_code', $record['committee_code'])->first();
                if (!$exists) {
                    DB::table('committees')->insert([$record]);
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
