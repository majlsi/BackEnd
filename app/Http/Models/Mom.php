<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Mom extends Model  implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['mom_title_ar','mom_title_en','mom_summary','meeting_id','language_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'moms';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'mom_title_ar' => 'sometimes',
                    'mom_summary'=> 'sometimes',
                    //'meeting_id' => 'required',
                );
            case 'update':
                return array(
                    'mom_title_ar' => 'sometimes',
                    'mom_summary'=> 'sometimes',
                    //'meeting_id' => 'required',
                );
        }
    }


    public function attachments(){
        return $this->hasMany('Models\Attachment','mom_id');
    }

    //Audit trail
    public function transformAudit(array $data):array
    {
        $data['meeting_id']=$this->meeting_id;
        return $data;
    }

}
