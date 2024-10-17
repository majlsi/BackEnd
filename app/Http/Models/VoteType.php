<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class VoteType extends Model  implements Auditable
{

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['vote_type_name_ar','vote_type_name_en'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'vote_types';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'vote_type_name_ar' => 'required',
                );
            case 'update':
                return array(
                    'vote_type_name_ar' => 'required',
                );
        }
    }
}
