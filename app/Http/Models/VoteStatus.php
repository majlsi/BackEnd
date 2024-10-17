<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class VoteStatus extends Model {

    protected $fillable = ['vote_status_name_ar','vote_status_name_en','icon_class_name','color_class_name'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'vote_statuses';



}
