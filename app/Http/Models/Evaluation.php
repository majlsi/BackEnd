<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Evaluation extends Model  implements Auditable
{

    use \OwenIt\Auditing\Auditable;
    
    protected $fillable = ['evaluation_name_en','evaluation_name_ar'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'evaluations';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'evaluation_name_en' => 'required',
                    'evaluation_name_ar' => 'required',
                );
            case 'update':
                return array(
                    'evaluation_name_en' => 'required',
                    'evaluation_name_ar' => 'required',
                );
        }
    }

    public function committeeUsers(){
        return $this->hasMany(CommitteeUser::class);
    }
}
