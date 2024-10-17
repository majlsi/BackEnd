<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lang;
use OwenIt\Auditing\Contracts\Auditable;

class HtmlMomTemplate extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['html_mom_template_name_en', 'html_mom_template_name_ar',
     'organization_id','html_mom_description_template_en', 'html_mom_description_template_ar'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'html_mom_templates';

    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'save':
                return array(
                    'html_mom_template_name_en' => 'required',
                    'html_mom_template_name_ar' => 'required',
                    'organization_id' => 'required',
                    'html_mom_description_template_en' => 'required|max:1000',
                    'html_mom_description_template_ar' => 'required|max:1000',
                );
            case 'update':
                return array(
                    'html_mom_template_name_en' => 'required',
                    'html_mom_template_name_ar' => 'required',
                    'organization_id' => 'required',
                    'html_mom_description_template_en' => 'required|max:1000',
                    'html_mom_description_template_ar' => 'required|max:1000',
                );
        }
    }

    public static function messages($action)
    {
        switch ($action) {
            case 'update':
                return array(
                    'html_mom_template_name_en.required' => ['message' => Lang::get('validation.custom.html_mom_template_name_en.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.html_mom_template_name_en.required', [], 'ar')],

                    'html_mom_template_name_ar.required' => ['message' => Lang::get('validation.custom.html_mom_template_name_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.html_mom_template_name_ar.required', [], 'ar')],

                    'html_mom_description_template_en.required' => ['message' => Lang::get('validation.custom.html_mom_description_template_en.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.html_mom_description_template_en.required', [], 'ar')],

                    'html_mom_description_template_ar.required' => ['message' => Lang::get('validation.custom.html_mom_description_template_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.html_mom_description_template_ar.required', [], 'ar')]
                );
            case 'save':
                return array(
                    'html_mom_template_name_en.required' => ['message' => Lang::get('validation.custom.html_mom_template_name_en.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.html_mom_template_name_en.required', [], 'ar')],

                    'html_mom_template_name_ar.required' => ['message' => Lang::get('validation.custom.html_mom_template_name_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.html_mom_template_name_ar.required', [], 'ar')],

                    'html_mom_description_template_en.required' => ['message' => Lang::get('validation.custom.html_mom_description_template_en.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.html_mom_description_template_en.required', [], 'ar')],

                    'html_mom_description_template_ar.required' => ['message' => Lang::get('validation.custom.html_mom_description_template_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.html_mom_description_template_ar.required', [], 'ar')]
                );

        }
    }

}
