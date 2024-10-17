<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Lang;
use Exception;

class DocumentUser extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    //use SoftDeletes;


    protected $fillable = ['document_id','user_id','document_status_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'document_users';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'document_id' => 'required',
                    'user_id' => 'required',
                );
        }
    }

    public function user(){
        return $this->belongsTo('Models\User', 'user_id');
    }

    public function document(){
        return $this->belongsTo('Models\Document','document_id');
    }

    public function documentAnnotations(){
        return $this->hasMany('Models\DocumentAnnotation');
    }
}