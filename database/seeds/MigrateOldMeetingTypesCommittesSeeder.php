<?php

use Illuminate\Database\Seeder;
use Models\MeetingType;

class MigrateOldMeetingTypesCommittesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("SET foreign_key_checks=0");
        MeetingType::truncate();
        DB::statement("SET foreign_key_checks=1");
    }
}        