<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class VoteResultStatus extends Model {

    protected $fillable = ['vote_result_status_name_ar','vote_result_status_name_en'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'vote_result_statuses';



}
