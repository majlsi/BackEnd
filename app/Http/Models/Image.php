<?php

namespace Models;
use Lang;

use \Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Description of ImageModel
 *
 * @author Eman
 */
class Image extends Model implements Auditable
{

    use \OwenIt\Auditing\Auditable;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['original_image_url','image_url','cropper_top','cropper_left','cropper_width','cropper_height','file_id'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'images';

    
    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'original_image_url' => 'required',
                    'image_url' => 'required',
                    
                );
            case 'update':
                return array(
                    'original_image_url' => 'required',
                    'image_url' => 'required',
                );
        }
    }

    public static function messages($action)
    {    
        switch ($action) {
            case 'save':
                return array(
                    'original_image_url.required' => ['message'=>Lang::get('validation.custom.original_image_url.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.original_image_url.required',[],'ar')],

                    'image_url.required' => ['message'=>Lang::get('validation.custom.image_url.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.image_url.required',[],'ar')],
                );
            case 'update': 
                return array(
                    'original_image_url.required' => ['message'=>Lang::get('validation.custom.original_image_url.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.original_image_url.required',[],'ar')],

                    'image_url.required' => ['message'=>Lang::get('validation.custom.image_url.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.image_url.required',[],'ar')],
                );    
        }
    }

    public function file(){
        return $this->belongsTo('Models\File','file_id');
    }
}

