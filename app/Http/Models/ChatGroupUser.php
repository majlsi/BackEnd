<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Lang;
use Exception;

class ChatGroupUser extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;


    protected $fillable = ['chat_group_id','user_id','meeting_guest_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'chat_group_users';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'chat_group_id' => 'required',
                    'user_id' => 'required',
                );
        }
    }

    public function user(){
        return $this->belongsTo('Models\User', 'user_id');
    }

}