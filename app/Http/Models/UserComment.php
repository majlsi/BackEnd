<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Lang;

class UserComment extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;


    protected $fillable = ['user_id','meeting_agenda_id','comment_text','is_organizer'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'user_comments';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'user_id' => 'required',
                    'meeting_agenda_id' => 'required',
                    'comment_text' => 'required',
                );
            case 'update':
                return array(
                    'user_id' => 'required',
                    'meeting_agenda_id' => 'required',
                    'comment_text' => 'required',
                );
        }
    }


    public function userComment(){
        return $this->belongsTo('Models\User');
    }

   

}
