<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lang;
use OwenIt\Auditing\Contracts\Auditable;
use Exception;

class Meeting extends Model implements Auditable {

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['id','meeting_title_ar','meeting_title_en','meeting_type_id','committee_id','meeting_code','is_signature_sent','document_id',
                           'time_zone_id','meeting_description_ar','meeting_description_en','meeting_note_ar','meeting_note_en','meeting_venue_ar','meeting_venue_en',
                           'meeting_status_id','is_mom_sent','meeting_mom_template_id','organization_id','meeting_schedule_from','meeting_schedule_to','created_by','meeting_sequence','proposal_id', 'location_lat', 'location_long','zoom_meeting_id','zoom_meeting_password','zoom_start_url','zoom_join_url',
                            'chat_room_id','last_message_text','last_message_date','microsoft_teams_meeting_id','microsoft_teams_join_url','microsoft_teams_join_web_url', 'related_meeting_id','version_number','is_published','online_configuration_id','meeting_attendance_percentage','is_mom_pdf','mom_pdf_url','mom_pdf_file_name','mom_pdf_file_id','directory_id', 'meeting_stakeholders_percentage'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $hidden = [
        //'zoom_meeting_id', 'zoom_meeting_password', 'zoom_start_url', 'zoom_join_url'
    ];
    protected $table = 'meetings';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'meeting_title_ar' => 'sometimes',
                    //'meeting_type_id' => 'required',
                    'time_zone_id' => 'required',
                    'organization_id' => 'required',
                    'meeting_description_ar' => 'sometimes',
                    'meeting_venue_ar' => 'sometimes',
                    'meeting_schedule_from' => 'required|date|before_or_equal:meeting_schedule_to',
                    'meeting_schedule_to' => 'required|date|after_or_equal:meeting_schedule_from',
                    'meeting_status_id' => 'required',
                    'committee_id'=> 'required',
                    'meeting_code' => 'required|unique_with:meetings,organization_id,NULL,id,deleted_at,NULL',
                    'created_by' => 'required',
                   
                );
            case 'update':
                return array(
                    'meeting_title_ar' => 'sometimes',
                    //'meeting_type_id' => 'required',
                    'time_zone_id' => 'required',
                    'organization_id' => 'required',
                    'meeting_description_ar' => 'sometimes',
                    'meeting_venue_ar' => 'sometimes',
                    'meeting_schedule_from' => 'required|date|before_or_equal:meeting_schedule_to',
                    'meeting_schedule_to' => 'required|date|after_or_equal:meeting_schedule_from',
                    'meeting_status_id' => 'required',
                    'committee_id'=> 'required',
                    'meeting_code' => 'sometimes|unique_with:meetings,organization_id,NULL,'.$id.',deleted_at,NULL',
                    'created_by' => 'required',
        
                );
                case 'signature-callback':
                    return array(
                        'document_id' => 'required',
                        'email' => 'required',
                        'is_signed' => 'required',
                        'comment' => 'sometimes',       
                    );
        }
    }

    public function meetingReminders()
    {
        return $this->belongsToMany('Models\Reminder', 'meeting_reminders','meeting_id', 'reminder_id');
    }

    public function meetingOrganisers()
    {
        return $this->belongsToMany('Models\User', 'meeting_organisers','meeting_id', 'user_id');
    }

    public function meetingParticipants(){
        return $this->belongsToMany('Models\User', 'meeting_participants','meeting_id', 'user_id')->withPivot('meeting_attendance_status_id','is_signature_sent','is_signed','id','participant_order','is_signature_sent_individualy','send_mom','can_sign','signature_comment')->orderBy('participant_order');
    }

    public function meetingAgendas(){
        return $this->hasMany('Models\MeetingAgenda')->orderBy('agenda_order');
    }

    public function meetingAttachments(){
        return $this->hasMany('Models\Attachment');
    }

    public function meetingVotes(){
        return $this->hasMany('Models\Vote','meeting_id');
    }

    public function meetingCommittee(){
        return $this->belongsTo('Models\Committee', 'committee_id');
    }

    public function participants(){
        return $this->hasMany('Models\MeetingParticipant')->orderBy('participant_order');
    }

    public function reminders()
    {
        return $this->hasMany('Models\MeetingReminder');
    }

    public function organisers(){
        return $this->hasMany('Models\MeetingOrganiser');
    }

    public function meetingStatusHistory(){
        return $this->hasMany('Models\MeetingStatusHistory');
    }
    

    public function timeZone(){
        return $this->belongsTo('Models\TimeZone', 'time_zone_id');
    }

    public function organization(){
        return $this->belongsTo('Models\Organization', 'organization_id');
    }

    public function meetingType(){
        return $this->belongsTo('Models\MeetingType', 'meeting_type_id');
    }

    public function creator(){
        return $this->belongsTo('Models\User', 'created_by');
    }

    public function meetingMoms(){
        return $this->hasMany('Models\Mom','meeting_id');
    }

    public function meetingTasks(){
        return $this->hasMany('Models\TaskManagement');
    }

    public function userOnlineConfiguration(){
        return $this->belongsTo('Models\UserOnlineConfiguration','online_configuration_id');
    }

    public function meetingOnlineConfigurations(){
        return $this->hasMany('Models\MeetingOnlineConfiguration');
    }

    public function committee(){
        return $this->belongsTo('Models\Committee', 'committee_id');
    }

    public function momFile(){
        return $this->belongsTo('Models\File','mom_pdf_file_id');
    }

    //Audit trail
    public function transformAudit(array $data):array
    {
        $data['meeting_id']=$this->id;
        return $data;
    }


    public function directory(){
        return $this->belongsTo('Models\Directory','directory_id');
    }

    public function guests()
    {
        return $this->hasMany('Models\MeetingGuest')->orderBy('order');
    }

    public function approvals()
    {
        return $this->hasMany('Models\Approval')->orderBy('id');
    }
    public function meetingRecommendations()
    {
        return $this->hasMany('Models\MeetingRecommendation')->orderBy('id');
    }
}
