<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class MeetingOrganiser extends Model implements Auditable 
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['user_id','meeting_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'meeting_organisers';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'user_id' => 'required',
                    'meeting_id' => 'required',
                );
            case 'update':
                return array(
                    'user_id' => 'required',
                    'meeting_id' => 'required',
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
