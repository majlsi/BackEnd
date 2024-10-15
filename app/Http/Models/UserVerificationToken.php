<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;

class UserVerificationToken extends Model {

    protected $fillable = ['user_id','expire_date','verification_code','is_used'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'user_verification_tokens';



}
