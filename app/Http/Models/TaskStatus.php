<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class TaskStatus extends Model {

    protected $fillable = ['task_status_name_ar','task_status_name_en'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'task_statuses';



}
