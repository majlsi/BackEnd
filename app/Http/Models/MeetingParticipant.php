<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class MeetingParticipant extends Model implements Auditable
{

    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['user_id','meeting_id','meeting_role_id','participant_order',
    'meeting_attendance_status_id','is_signed','is_signature_sent',
    'is_signature_sent_individualy','signature_comment','send_mom','can_sign','is_accept_absent_by_organiser'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'meeting_participants';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'user_id' => 'required',
                    'meeting_id' => 'required',
                    'meeting_role_id' => 'required',
                    'participant_order' => 'required'
                );
            case 'update':
                return array(
                    'user_id' => 'required',
                    'meeting_id' => 'required',
                    'meeting_role_id' => 'required',
                    'participant_order' => 'required'
                );
        }
    }

    public function meetingAttendanceStatus(){
        return $this->belongsTo('Models\MeetingAttendanceStatus', 'meeting_attendance_status_id');
    }

    //Audit trail
    public function transformAudit(array $data):array
    {
        $data['meeting_id']=$this->meeting_id;
        return $data;
    }
}
