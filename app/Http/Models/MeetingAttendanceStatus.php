<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class MeetingAttendanceStatus extends Model {

    protected $fillable = ['meeting_attendance_status_name_ar','meeting_attendance_status_name_en','icon_class_name','color_class_name','meeting_attendance_action_name_ar','meeting_attendance_action_name_en'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'meeting_attendance_statuses';



}
