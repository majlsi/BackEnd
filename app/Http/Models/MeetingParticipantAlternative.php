<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class MeetingParticipantAlternative extends Model implements Auditable
{

    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['meeting_participant_id','rejection_reason_comment'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'meeting_participant_alternatives';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'meeting_participant_id' => 'required',
                    'rejection_reason_comment'=> 'max:1000'
                );
            case 'update':
                return array(
                    'meeting_participant_id' => 'required',
                    'rejection_reason_comment'=> 'max:1000'
                );
        }
    }
}