<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class WorksDoneByCommittee extends Model  implements Auditable
{

    use \OwenIt\Auditing\Auditable;
    
    protected $fillable = ['work_done','committee_id','work_done_date'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'works_done_by_committee';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'work_done' => 'required',
                    'committee_id' => 'required',
                    'work_done_date' => 'nullable|date',
                );
            case 'update':
                return array(
                    'work_done' => 'required',
                    'committee_id' => 'required',
                    'work_done_date' => 'nullable|date',
                );
        }
    }

    public function committee(){
        return $this->belongsTo('Models\Committee', 'committee_id');
    }
}
