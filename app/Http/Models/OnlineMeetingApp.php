<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class OnlineMeetingApp extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    protected $fillable = ['app_name_en','app_name_ar'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'online_meeting_apps';

}
