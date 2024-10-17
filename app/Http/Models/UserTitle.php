<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Exception;

class UserTitle extends Model  implements Auditable
{

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['user_title_name_ar','user_title_name_en','organization_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'user_titles';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'user_title_name_ar' => 'required',
                    'organization_id' => 'required',
                );
            case 'update':
                return array(
                    'user_title_name_ar' => 'required',
                    'organization_id' => 'required',
                );
        }
    }
    public function users(){
        return $this->hasMany('Models\User','user_title_id');
    }

    protected static function boot() {
        parent::boot();
        
        static::deleting(function($organizationUserTitle) {
            $relatedRelations = ['users'];
             foreach($relatedRelations as $relatedRelation){
                if ($organizationUserTitle->$relatedRelation()->count() > 0){
                    throw new Exception("Model have child records");
                }
            }
            
        });
    }
}
