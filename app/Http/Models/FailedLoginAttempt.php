<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Lang;
use Exception;

class FailedLoginAttempt extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;


    protected $fillable = ['user_id','email_address','ip_address','failed_login_date'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'failed_login_attempts';

}