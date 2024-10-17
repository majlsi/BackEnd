<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class MeetingReminder extends Model  implements Auditable {

    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['reminder_id','meeting_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'meeting_reminders';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'meeting_id' => 'required',
                    'reminder_id' => 'required',
                );
            case 'update':
                return array(
                    'meeting_id' => 'required',
                    'reminder_id' => 'required',
                );
        }
    }

    //Audit trail
    public function transformAudit(array $data):array
    {
        $data['meeting_id']=$this->meeting_id;
        return $data;
    }

}
