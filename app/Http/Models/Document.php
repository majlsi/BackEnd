<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lang;

/**
 * Description of DocumentModel
 *
 */
class Document extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['added_by','organization_id','document_subject_ar','document_description_ar',
                           'document_url','file_id','document_name','committee_id','review_start_date','review_end_date','document_status_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'documents';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'document_subject_ar' => 'required',
                    'document_url' => 'required',
                    'document_description_ar' => 'required',
                    'document_name' => 'required',
                    'committee_id' => 'required',
                    'review_start_date' => 'required',
                    'review_end_date' => 'required',
                    'document_users_ids' => 'required',
                );
            case 'update':
                return array(
                    'document_subject_ar' => 'required',
                    'document_description_ar' => 'required',
                    'committee_id' => 'required',
                    'review_start_date' => 'required',
                    'review_end_date' => 'required',
                    'document_users_ids' => 'required',
                );
        }
    }

    public static function messages($action)
    {    
        switch ($action) {
            case 'save':
                return array(
                    'document_description_ar.required' => ['message'=>Lang::get('validation.custom.document.document_description_ar.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document.document_description_ar.required',[],'ar')],

                    'document_url.required' => ['message'=>Lang::get('validation.custom.document.document_url.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document.document_url.required',[],'ar')],
                    
                    'document_subject_ar.required' => ['message'=>Lang::get('validation.custom.document.document_subject_ar.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document.document_subject_ar.required',[],'ar')],
                
                    'document_name.required' => ['message'=>Lang::get('validation.custom.document.document_name.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document.document_name.required',[],'ar')],
                
                    'committee_id.required' => ['message'=>Lang::get('validation.custom.document.committee_id.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document.committee_id.required',[],'ar')],

                    'review_start_date.required' => ['message'=>Lang::get('validation.custom.document.review_start_date.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document.review_start_date.required',[],'ar')],
                
                    'review_end_date.required' => ['message'=>Lang::get('validation.custom.document.review_end_date.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document.review_end_date.required',[],'ar')],
                
                    'document_users_ids.required' => ['message'=>Lang::get('validation.custom.document.document_users.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document.document_users.required',[],'ar')],
                );
            case 'update': 
                return array(
                    'document_description_ar.required' => ['message'=>Lang::get('validation.custom.document.document_description_ar.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document.document_description_ar.required',[],'ar')],

                    'document_url.required' => ['message'=>Lang::get('validation.custom.document.document_url.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document.document_url.required',[],'ar')],

                    'document_subject_ar.required' => ['message'=>Lang::get('validation.custom.document.document_subject_ar.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document.document_subject_ar.required',[],'ar')],
                    
                    'document_name.required' => ['message'=>Lang::get('validation.custom.document.document_name.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document.document_name.required',[],'ar')],
                
                    'committee_id.required' => ['message'=>Lang::get('validation.custom.document.committee_id.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document.committee_id.required',[],'ar')],
                
                    'review_start_date.required' => ['message'=>Lang::get('validation.custom.document.review_start_date.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document.review_start_date.required',[],'ar')],

                    'review_end_date.required' => ['message'=>Lang::get('validation.custom.document.review_end_date.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document.review_end_date.required',[],'ar')],
                
                    'document_users_ids.required' => ['message'=>Lang::get('validation.custom.document.document_users.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.document.document_users.required',[],'ar')],
                );    
        }
    }

    public function organization(){
        return $this->belongsTo('Models\Organization', 'organization_id');
    }

    public function committee(){
        return $this->belongsTo('Models\Committee', 'committee_id');
    }

    public function documentStatus(){
        return $this->belongsTo('Models\DocumentStatus', 'document_status_id');
    }

    public function creator(){
        return $this->belongsTo('Models\User', 'added_by');
    }

    public function reviewres(){
        return $this->belongsToMany('Models\User','document_users','document_id', 'user_id');
    }

    public function documentUsers(){
        return $this->hasMany('Models\DocumentUser','document_id');
    }

    public function file(){
        return $this->belongsTo('Models\File','file_id');
    }
}

