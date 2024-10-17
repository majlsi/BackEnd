<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Notification extends Model  implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['notification_title_ar','notification_title_en','notification_body_ar',
        'notification_body_en','notification_icon','notification_url','notification_model_type',
        'notification_model_id','notification_date'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'notifications';

    public function notificationUsers(){
        return $this->hasMany('Models\NotificationUser','notification_id');
    }
}
