<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class DecisionType extends Model  implements Auditable
{

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['decision_type_name_ar','decision_type_name_en','organization_id','is_system'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'decision_types';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'decision_type_name_ar' => 'required',
                    'organization_id' => 'required',
                );
            case 'update':
                return array(
                    'decision_type_name_ar' => 'required',
                    'organization_id' => 'required',
                );
        }
    }

    public function organization()
    {
        return $this->belongsTo('Models\Organization', 'organization_id');
    }
}
