<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lang;

/**
 * Description of documentAnnotationModel
 *
 */
class DocumentAnnotation extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['document_user_id','page_number','annotation_text','x_upper_left','y_upper_left','creation_date'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'document_annotations';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'page_number' => 'required',
                    'annotation_text' => 'required',
                    'x_upper_left' => 'required',
                    'y_upper_left' => 'required',
                );
            case 'update':
                return array(
                    'page_number' => 'required',
                    'annotation_text' => 'required',
                    // 'x_upper_left' => 'required',
                    // 'y_upper_left' => 'required',
                );
        }
    }

    public static function messages($action)
    {    
        switch ($action) {
            case 'save':
                return array(
                    'page_number.required' => ['message'=>Lang::get('validation.custom.document_annotation.page_number.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document_annotation.page_number.required',[],'ar')],

                    'annotation_text.required' => ['message'=>Lang::get('validation.custom.document_annotation.annotation_text.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document_annotation.annotation_text.required',[],'ar')],
                    
                    'x_upper_left.required' => ['message'=>Lang::get('validation.custom.document_annotation.x_upper_left.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document_annotation.x_upper_left.required',[],'ar')],
                
                    'y_upper_left.required' => ['message'=>Lang::get('validation.custom.document_annotation.y_upper_left.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document_annotation.y_upper_left.required',[],'ar')],
                );
            case 'update': 
                return array(
                    'page_number.required' => ['message'=>Lang::get('validation.custom.document_annotation.page_number.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document_annotation.page_number.required',[],'ar')],

                    'annotation_text.required' => ['message'=>Lang::get('validation.custom.document_annotation.annotation_text.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document_annotation.annotation_text.required',[],'ar')],
                    
                    'x_upper_left.required' => ['message'=>Lang::get('validation.custom.document_annotation.x_upper_left.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document_annotation.x_upper_left.required',[],'ar')],
                
                    'y_upper_left.required' => ['message'=>Lang::get('validation.custom.document_annotation.y_upper_left.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document_annotation.y_upper_left.required',[],'ar')],
                );    
        }
    }

    public function documentUser(){
        return $this->belongsTo('Models\DocumentUser', 'document_user_id');
    }

}

