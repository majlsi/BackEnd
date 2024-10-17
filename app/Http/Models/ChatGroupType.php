<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class ChatGroupType extends Model {

    protected $fillable = ['chat_group_type_en','chat_group_type_ar'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'chat_group_types';



}
