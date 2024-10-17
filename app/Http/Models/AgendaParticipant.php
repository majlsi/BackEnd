<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AgendaParticipant extends Model implements Auditable
{

    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['user_id', 'meeting_guest_id', 'meeting_agenda_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'agenda_participants';

    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'save':
                return array(
                    'user_id' => 'required_if:meeting_guest_id,null|exists:users,id,deleted_at,NULL',
                    'meeting_guest_id' => 'required_if:user_id,null|exists:meeting_guests,id,deleted_at,NULL',
                    'meeting_agenda_id' => 'required|exists:meeting_agendas,id,deleted_at,NULL',
                );
            case 'update':
                return array(
                    'user_id' => 'required_if:meeting_guest_id,null|exists:users,id,deleted_at,NULL',
                    'meeting_guest_id' => 'required_if:user_id,null|exists:meeting_guests,id,deleted_at,NULL',
                    'meeting_agenda_id' => 'required|exists:meeting_agendas,id,deleted_at,NULL',
                );
        }
    }


    public function meetingAgenda()
    {
        return $this->belongsTo('Models\MeetingAgenda', 'meeting_agenda_id');
    }

    //Audit trail
    public function transformAudit(array $data): array
    {
        $data['meeting_id'] = $this->meetingAgenda->meeting->id;
        return $data;
    }
}
