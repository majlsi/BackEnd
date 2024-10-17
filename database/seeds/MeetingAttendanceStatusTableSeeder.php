<?php

use Illuminate\Database\Seeder;

class MeetingAttendanceStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $meetingAttendanceStatusData = [
            ['id' => 1, 'meeting_attendance_action_name_ar' => 'حاضر', 'meeting_attendance_action_name_en' => 'Yes','meeting_attendance_status_name_ar'=>'حاضر','meeting_attendance_status_name_en'=>'Attend','icon_class_name'=>'la-check','color_class_name'=>'m-badge--brand'],
            ['id' => 2, 'meeting_attendance_action_name_ar' => 'معتذر', 'meeting_attendance_action_name_en' => 'No','meeting_attendance_status_name_ar'=>'معتذر','meeting_attendance_status_name_en'=>'Absent','icon_class_name'=>'la-remove','color_class_name'=>'m-badge--metal'],
            ['id' => 3, 'meeting_attendance_action_name_ar' => 'ربما', 'meeting_attendance_action_name_en' => 'Maybe','meeting_attendance_status_name_ar'=>'قد يحضر','meeting_attendance_status_name_en'=>'May Attend','icon_class_name'=>'la-question-circle','color_class_name'=>'m-badge--black'],

        ];

        foreach ($meetingAttendanceStatusData as $key => $meetingAttendanceStatus) {
            $exists = DB::table('meeting_attendance_statuses')->where('id', $meetingAttendanceStatus['id'])->first();
            if(!$exists){
                DB::table('meeting_attendance_statuses')->insert([$meetingAttendanceStatus]);    
            }
        }
    }
}        