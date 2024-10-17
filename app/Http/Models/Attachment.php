<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Attachment extends Model implements Auditable
{

    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['attachment_url', 'meeting_id', 'meeting_agenda_id', 'attachment_name', 'presenter_id', 'presenter_meeting_guest_id', 'presentation_notes', 'vote_id', 'file_id', 'is_external_storage'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'attachments';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'attachment_url' => 'required',
                    'attachment_name' => 'required',
                );
            case 'update':
                return array(
                    'attachment_url' => 'required',
                    'attachment_name' => 'required',
                );
        }
    }

    //Audit trail
   public function transformAudit(array $data):array
    {
        $data['meeting_id']=$this->meeting_id;
        return $data;
    }

    public function meetingAgenda(){
        return $this->belongsTo('Models\MeetingAgenda', 'meeting_agenda_id');
    }

    public function meeting(){
        return $this->belongsTo('Models\Meeting', 'meeting_id');
    }

    public function presenter(){
        return $this->belongsTo('Models\User', 'presenter_id');
    }

    public function presenterGuest()
    {
        return $this->belongsTo('Models\MeetingGuest', 'presenter_meeting_guest_id');
    }

    public function vote(){
        return $this->belongsTo('Models\Vote', 'vote_id');
    }

    public function file(){
        return $this->belongsTo('Models\File','file_id');
    }

    public  static function boot() {
        parent::boot();

        static::deleting(function($attachment) {
            //remove related file
            // $attachment->file()->delete();//
        });
    }

}
