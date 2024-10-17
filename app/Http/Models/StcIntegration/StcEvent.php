<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class StcEvent extends Model  implements Auditable
{

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['id','event_id','event_type','creation_date','tenant','api_version','data','status','error'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'stc_events';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'event_id' => 'required',
                );
            case 'update':
                return array(
                    'event_id' => 'required',
                );
        }
    }
}
