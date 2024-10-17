<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CommitteeUser extends Model  implements Auditable
{

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'committee_id','user_id','is_head','committee_user_start_date', 'is_conflict',
        'committee_user_expired_date','evaluation_id','evaluation_reason','disclosure_url'
    ];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'committee_users';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'committee_id' => 'required',
                    'user_id' => 'required',
                );
            case 'update':
                return array(
                    'committee_id' => 'required',
                    'user_id' => 'required',
                );

            case 'putEvaluation':
                return array(
                    'evaluation_id' => 'required',
                    'evaluation_reason' => 'required',
                );
            case 'AddDisclosure':
                return array(
                    'is_conflict' => 'required',
                    'file' => 'required|mimes:jpeg,jpg,png,doc,docx,odt,xls,xlsx,ppt,pptx,pdf|max:' . config('attachment.file_size'),
                );
        }
    }

    public function user(){
        return $this->belongsTo('Models\User', 'user_id');
    }

    public function committee()
    {
        return $this->belongsTo('Models\Committee', 'committee_id');
    }

    public function evaluation(){
        return $this->belongsTo('Models\Evaluation', 'evaluation_id');
    }
}
