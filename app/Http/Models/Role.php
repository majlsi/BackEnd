<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Exception;
class Role extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    
    protected $fillable  = ['role_name' , 'role_name_ar','organization_id','is_meeting_role','can_assign','role_code','is_system','is_organization'];

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'role_name_ar' => 'required',
                );
            case 'update':
                return array(
                    'role_name_ar' => 'required',
                );
        }
    }
    
   public function users(){
       return $this->hasMany('Models\User');
   }
   
   public function rights(){
       return $this->hasMany('Models\RoleRight');
   }

   protected static function boot() {
    parent::boot();
    
    static::deleting(function($role) {
        $relatedRelations = ['users'];
         foreach($relatedRelations as $relatedRelation){
            if ($role->$relatedRelation()->count() > 0){
                throw new Exception("Model have child records");
            }
        }
        
    });
}
}

