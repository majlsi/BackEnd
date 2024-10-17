<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Exception;

class Nickname extends Model  implements Auditable
{

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['nickname_ar','nickname_en','organization_id','is_system'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'nicknames';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'nickname_ar' => 'required',
                    'organization_id' => 'required',
                );
            case 'update':
                return array(
                    'nickname_ar' => 'required',
                    'organization_id' => 'required',
                );
        }
    }
    
    public function users(){
        return $this->hasMany('Models\User','nickname_id');
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
