<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Vote extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'vote_subject_ar', 'vote_subject_en', 'agenda_id', 'meeting_id', 'vote_type_id', 'vote_schedule_from',
        'vote_schedule_to', 'is_started', 'decision_type_id', 'decision_due_date', 'creator_id', 'is_secret',
        'committee_id', 'vote_description', 'vote_result_status_id', 'creation_date', 'document_id'
    ];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'votes';

    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'save':
            case 'update':
                return [
                    '*.vote_subject_ar' => 'sometimes',
                    '*.meeting_id' => 'required',
                    '*.agenda_id' => 'required',
                    '*.vote_type_id' => 'required',
                    '*.vote_participants' => 'required|array|min:1',
                    "*.vote_participants.*.user_id" => "sometimes|nullable|numeric",
                    "*.vote_participants.*.meeting_guest_id" => "sometimes|nullable|numeric",
                ];
            case 'save-circular-decision':
            case 'update-circular-decision':
                return [
                    'vote_subject_ar' => 'sometimes',
                    'vote_schedule_from' => 'required',
                    'vote_schedule_to' => 'required',
                    'decision_type_id' => 'required',
                    'vote_description' => 'required',
                    'committee_id' => 'required',
                    'is_secret' => 'required',
                    'vote_users_ids' => 'required',
                ];
        }
    }

    public function voteResults()
    {
        return $this->hasMany('Models\VoteResult', 'vote_id');
    }

    public function voters()
    {
        return $this->belongsToMany('Models\User', 'vote_results', 'vote_id', 'user_id');
    }

    public function attachments()
    {
        return $this->hasMany('Models\Attachment');
    }

    public function meeting()
    {
        return $this->belongsTo('Models\Meeting', 'meeting_id');
    }

    public function tasks()
    {
        return $this->hasMany('Models\TaskManagement');
    }

    public function creator()
    {
        return $this->belongsTo('Models\User', 'creator_id');
    }


    public function committee()
    {
        return $this->belongsTo('Models\Committee', 'committee_id');
    }

    public function voteParticipants()
    {
        return $this->hasMany('Models\VoteParticipants', 'vote_id', 'id');
    }

    //Audit trail
    public function transformAudit(array $data): array
    {
        $data['meeting_id'] = $this->meeting_id;

        return $data;
    }
}
