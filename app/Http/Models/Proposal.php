<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lang;
use OwenIt\Auditing\Contracts\Auditable;

class Proposal extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['proposal_title','proposal_text','organization_id','created_by'];
    protected $table = 'proposals';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'proposal_title' => 'required',
                    'proposal_text' => 'required',
                    'organization_id' => 'sometimes',
                    'created_by' => 'required',
                );
            case 'update':
                return array(
                    'proposal_title' => 'required',
                    'proposal_text' => 'required',
                    'organization_id' => 'sometimes',
                    'created_by' => 'required',
                );
        }
    }

      //Audit trail
      public function transformAudit(array $data):array
      {
          $data['meeting_id']=$this->meeting_id;
          return $data;
      }

      public function user(){
        return $this->belongsTo('Models\User','created_by');
     }

     public function organization(){
        return $this->belongsTo('Models\Organization','organization_id');
     }

}
