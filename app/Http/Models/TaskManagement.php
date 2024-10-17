<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class TaskManagement extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['id', 'assigned_to', 'description', 'task_status_id','created_by',
        'meeting_id', 'meeting_agenda_id', 'start_date', 'number_of_days','serial_number','task_sequence','vote_id' ,'organization_id','committee_id' ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'task_management';

    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'save':
                return array(
                    'assigned_to' => 'required',
                    'description' => 'required|max:3000',
                    'task_status_id' => 'required',
                    'meeting_id' => 'required_without:vote_id',
                    'start_date' => 'required|date',
                    'number_of_days' => 'required|numeric',
                    'created_by' => 'required',
                    'vote_id' => 'required_without:meeting_id',

                );
            case 'update':
                return array(

                    'assigned_to' => 'required',
                    'description' => 'required|max:3000',
                    'task_status_id' => 'required',
                    'meeting_id' => 'required_without:vote_id',
                    'start_date' => 'required|date',
                    'number_of_days' => 'required|numeric',
                    'created_by' => 'required',
                    'vote_id' => 'required_without:meeting_id',
                );
        }
    }

    public function taskMeeting()
    {
        return $this->belongsTo('Models\Meeting', 'meeting_id');
    }

    
    public function decision()
    {
        return $this->belongsTo('Models\Vote', 'vote_id');
    }

    public function taskStatus()
    {
        return $this->belongsTo('Models\TaskStatus', 'task_status_id');
    }

    public function assignee()
    {
        return $this->belongsTo('Models\User', 'assigned_to');
    }

    public function committee()
    {
        return $this->belongsTo('Models\Committee', 'committee_id');
    }

    public function taskStatusHistory()
    {
        return $this->hasMany('Models\TaskActionHistory', 'task_id');
    }

    public function createdBy()
    {
        return $this->belongsTo('Models\User', 'created_by');
    }

    public function organization()
    {
        return $this->belongsTo('Models\organization', 'organization_id');
    }
}
