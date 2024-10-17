<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class NotificationUser extends Model  implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['user_id','notification_id','is_read'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'notification_users';
}
