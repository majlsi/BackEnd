<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Exception;

class MeetingType extends Model  implements Auditable
{

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['meeting_type_name_ar','meeting_type_name_en','organization_id','is_system','meeting_type_code'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'meeting_types';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'meeting_type_name_ar' => 'required',
                    'is_system' => 'required',
                );
            case 'update':
                return array(
                    'meeting_type_name_ar' => 'required',
                    'is_system' => 'required',
                );
        }
    }
    public function meetings(){
        return $this->hasMany('Models\Meeting','meeting_type_id');
    }

    protected static function boot() {
        parent::boot();
        
        static::deleting(function($meetingType) {
            $relatedRelations = ['meetings'];
             foreach($relatedRelations as $relatedRelation){
                if ($meetingType->$relatedRelation()->count() > 0){
                    throw new Exception("Model have child records");
                }
            }
            
        });
    }
}
