<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class MeetingStatusHistory extends Model {

    protected $fillable = ['meeting_id','meeting_status_id','user_id','action_time'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'meeting_status_history';



}
