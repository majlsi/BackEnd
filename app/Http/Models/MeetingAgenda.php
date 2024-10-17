<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Exception;
class MeetingAgenda extends Model implements Auditable 
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['agenda_title_ar','agenda_title_en','agenda_time_in_min','meeting_id','agenda_purpose_id','agenda_description_ar','agenda_description_en','presenting_spent_time_in_second','presenting_start_time','is_presented_now','agenda_order','directory_id']; 
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'meeting_agendas';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'agenda_title_ar' => 'sometimes',
                    'agenda_time_in_min' => 'required',
                    'meeting_id' => 'required',
                    'agenda_purpose_id' => 'required',
                );
            case 'update':
                return array(
                    'agenda_title_ar' => 'sometimes',
                    'agenda_time_in_min' => 'required',
                    'meeting_id' => 'required',
                    'agenda_purpose_id' => 'required',
                );
        }
    }

    public function agendaPresenters(){
        return $this->belongsToMany('Models\User', 'agenda_presenters', 'meeting_agenda_id', 'user_id');          
    }
    public function  presentersAgenda(){
        return $this->agendaPresenters();
    }

    public function agendaAttachments(){
        return $this->hasMany('Models\Attachment');
    }
    
    public function agendaVotes(){
        return $this->hasMany('Models\Vote','agenda_id');
    }

    public function agendaUserComments(){
        return $this->hasMany('Models\UserComment','meeting_agenda_id');
    }

    public function presenters(){
        return $this->hasMany('Models\AgendaPresenter');
    }

    public function participants()
    {
        return $this->hasMany('Models\AgendaParticipant');
    }

    public function meeting () {
        return $this->belongsTo('Models\Meeting', 'meeting_id');
    }

    public function agendaPurpose () {
        return $this->belongsTo('Models\AgendaPurpose', 'agenda_purpose_id');
    }

    //Audit trail
    public function transformAudit(array $data):array
    {
        $data['meeting_id']=$this->meeting_id;
        return $data;
    }


    public function directory(){
        return $this->belongsTo('Models\Directory','directory_id');
    }

    protected static function boot() {
        parent::boot();
        
        static::deleting(function($meetingAgenda) {
            $relatedRelations = ['agendaVotes'];
             foreach($relatedRelations as $relatedRelation){
                if ($meetingAgenda->$relatedRelation()->count() > 0){
                    throw new Exception("Model have child records");
                }
            }
            
        });
    }
}
