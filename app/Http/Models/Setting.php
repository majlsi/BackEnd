<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
class Setting extends Model implements Auditable{


    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    protected $fillable  = ['setting_key' , 'setting_value' , 'setting_unit', 'setting_key_ar'];

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'setting_key' => 'required',
                    'setting_value' => 'sometimes|required',
                );
            case 'update':
                return array(
                    'setting_key' => 'required',
                    'setting_value' => 'sometimes|required',
                );
        }
    }
}

