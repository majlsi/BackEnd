<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Exception;

class TimeZone extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    
    protected $fillable = ['description_ar','description_en','organization_id','is_system','diff_hours','time_zone_code'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'time_zones';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'description_ar' => 'required',
                    'is_system' => 'required',
                    'diff_hours'  => 'required',
                    'time_zone_code' => 'required',
                );
            case 'update':
                return array(
                    'description_ar' => 'required',
                    'is_system' => 'required',
                    'diff_hours'  => 'required',
                    'time_zone_code' => 'required',
                );
        }
    }
    public function organizations(){
        return $this->hasMany('Models\Organization','time_zone_id');
    }

    public function meetings(){
        return $this->hasMany('Models\Meeting','time_zone_id');
    }

    protected static function boot() {
        parent::boot();
        
        static::deleting(function($timeZone) {
            $relatedRelations = ['meetings','organizations'];
             foreach($relatedRelations as $relatedRelation){
                if ($timeZone->$relatedRelation()->count() > 0){
                    throw new Exception("Model have child records");
                }
            }
            
        });
    }
}
