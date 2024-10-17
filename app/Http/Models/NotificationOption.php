<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class NotificationOption extends Model {

    protected $fillable = ['notification_option_name_ar','notification_option_name_en'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'notification_options';

}
