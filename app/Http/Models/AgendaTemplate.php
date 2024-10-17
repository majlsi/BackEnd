<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lang;
use OwenIt\Auditing\Contracts\Auditable;

class AgendaTemplate extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['agenda_template_name_en', 'agenda_template_name_ar',
     'organization_id','agenda_description_template_en', 'agenda_description_template_ar'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'agenda_templates';

    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'save':
                return array(
                    'agenda_template_name_en' => 'required',
                    'agenda_template_name_ar' => 'required',
                    'organization_id' => 'required',
                    'agenda_description_template_en' => 'required|max:1000',
                    'agenda_description_template_ar' => 'required|max:1000',
                );
            case 'update':
                return array(
                    'agenda_template_name_en' => 'required',
                    'agenda_template_name_ar' => 'required',
                    'organization_id' => 'required',
                    'agenda_description_template_en' => 'required|max:1000',
                    'agenda_description_template_ar' => 'required|max:1000',
                );
        }
    }

    public static function messages($action)
    {
        switch ($action) {
            case 'update':
                return array(
                    'agenda_template_name_en.required' => ['message' => Lang::get('validation.custom.agenda_template_name_en.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.agenda_template_name_en.required', [], 'ar')],

                    'agenda_template_name_ar.required' => ['message' => Lang::get('validation.custom.agenda_template_name_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.agenda_template_name_ar.required', [], 'ar')],

                    'agenda_description_template_en.required' => ['message' => Lang::get('validation.custom.agenda_description_template_en.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.agenda_description_template_en.required', [], 'ar')],

                    'agenda_description_template_ar.required' => ['message' => Lang::get('validation.custom.agenda_description_template_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.agenda_description_template_ar.required', [], 'ar')]
                );
            case 'save':
                return array(
                    'agenda_template_name_en.required' => ['message' => Lang::get('validation.custom.agenda_template_name_en.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.agenda_template_name_en.required', [], 'ar')],

                    'agenda_template_name_ar.required' => ['message' => Lang::get('validation.custom.agenda_template_name_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.agenda_template_name_ar.required', [], 'ar')],

                    'agenda_description_template_en.required' => ['message' => Lang::get('validation.custom.agenda_description_template_en.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.agenda_description_template_en.required', [], 'ar')],

                    'agenda_description_template_ar.required' => ['message' => Lang::get('validation.custom.agenda_description_template_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.agenda_description_template_ar.required', [], 'ar')]
                );

        }
    }

}
