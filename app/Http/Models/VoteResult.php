<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class VoteResult extends Model  implements Auditable {

    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['vote_id','user_id','vote_status_id','decision_weight','is_signed', 'signature_comment', 'meeting_guest_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'vote_results';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'vote_id' => 'required',
                    'user_id' => 'required_if:meeting_guest_id,null|nullable|exists:users,id,deleted_at,NULL',
                    'meeting_guest_id' => 'required_if:user_id,null|nullable|exists:meeting_guests,id,deleted_at,NULL',
                    'vote_status_id' => 'required',
                );
            case 'update':
                return array(
                    'vote_id' => 'required',
                    'user_id' => 'required_if:meeting_guest_id,null|nullable|exists:users,id,deleted_at,NULL',
                    'meeting_guest_id' => 'required_if:user_id,null|nullable|exists:meeting_guests,id,deleted_at,NULL',
                    'vote_status_id' => 'required',
                );
            case 'save-decision-result':
                return array(
                    'vote_id' => 'required',
                    'vote_status_id' => 'required',
                );
        }
    }

    public function vote()
    {
        return $this->belongsTo('Models\Vote', 'vote_id');
    }

    //Audit trail
    // public function transformAudit(array $data):array
    // {
    //     $data['meeting_id']=$this->meeting_id;
    //     return $data;
    // }

}
