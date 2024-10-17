<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class MeetingStatus extends Model {

    protected $fillable = ['meeting_status_name_ar','meeting_status_name_en'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'meeting_statuses';



}
