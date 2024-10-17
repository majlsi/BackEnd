<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class TaskActionHistory extends Model {

    protected $fillable = ['task_id','task_status_id','user_id','action_time','task_comment_text','is_status_changed'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'task_action_history';



}
