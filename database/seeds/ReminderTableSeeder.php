<?php

use Illuminate\Database\Seeder;

class ReminderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $remindersData = [
            ['id'=> 1, 'reminder_description_ar' => 'قبل 5 دقائق', 'reminder_description_en' => '5 minutes before', 'reminder_duration_in_minutes' => 5],
            ['id'=> 2, 'reminder_description_ar' => 'قبل 15 دقيقة', 'reminder_description_en' => '15 minutes before', 'reminder_duration_in_minutes' => 15],
            ['id'=> 3, 'reminder_description_ar' => 'قبل 30 دقيقة', 'reminder_description_en' => '30 minutes before', 'reminder_duration_in_minutes' => 30],
            ['id'=> 4, 'reminder_description_ar' => 'قبل ساعة', 'reminder_description_en' => '1 hour before', 'reminder_duration_in_minutes' => 60],
            ['id'=> 5, 'reminder_description_ar' => 'قبل ساعتين', 'reminder_description_en' => '2 hour before', 'reminder_duration_in_minutes' => 120],
            ['id'=> 6, 'reminder_description_ar' => 'قبل يوم', 'reminder_description_en' => '1 day before', 'reminder_duration_in_minutes' => 1440],
            ['id'=> 7, 'reminder_description_ar' => 'قبل يومين', 'reminder_description_en' => '2 day before', 'reminder_duration_in_minutes' => 2880],
            ['id'=> 8, 'reminder_description_ar' => 'قبل 3 ايام', 'reminder_description_en' => '3 day before', 'reminder_duration_in_minutes' => 4320],
            ['id'=> 9, 'reminder_description_ar' => 'قبل 7 ايام', 'reminder_description_en' => '7 day before', 'reminder_duration_in_minutes' => 10080],
            ['id'=> 10, 'reminder_description_ar' => 'قبل 14 يوم', 'reminder_description_en' => '14 day before', 'reminder_duration_in_minutes' => 20160],
            ['id'=> 11, 'reminder_description_ar' => 'قبل 21 يوم', 'reminder_description_en' => '21 day before', 'reminder_duration_in_minutes' => 30240],
        ];
        foreach ($remindersData as $key => $reminder) {
            $exists = DB::table('reminders')->where('id', $reminder['id'])->first();
            if(!$exists){
                DB::table('reminders')->insert([$reminder]);    
            }
        }
    }
}
