<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lang;
use OwenIt\Auditing\Contracts\Auditable;

class MomTemplate extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['template_name_en', 'template_name_ar', 'organization_id','is_default',
        'show_mom_header', 'show_recommendation',
        'show_agenda_list', 'show_timer',
        'show_presenters', 'show_purpose',
        'show_participant_nickname', 'show_participant_job',
        'show_participant_title', 'show_conclusion',
        'conclusion_template_en', 'conclusion_template_ar',
        'member_list_introduction_template_en',
        'member_list_introduction_template_ar',
        'introduction_template_en', 'introduction_template_ar','logo_id','show_vote_results','show_vote_status'];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'mom_templates';

    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'save':
                return array(
                    'template_name_en' => 'required',
                    'template_name_ar' => 'required',
                    'organization_id' => 'required',
                    'show_mom_header' => 'required',
                    'show_agenda_list' => 'required',
                    'show_timer' => 'required',
                    'show_presenters' => 'required',
                    'show_recommendation' => 'required',
                    'show_purpose' => 'required',
                    'show_participant_nickname' => 'required',
                    'show_participant_job' => 'required',
                    'show_participant_title' => 'required',
                    'show_conclusion' => 'required',
                    'show_vote_results' => 'required',
                    'show_vote_status' => 'required',
                    'conclusion_template_en' => 'max:1000',
                    'conclusion_template_ar' => 'max:1000',
                    'member_list_introduction_template_en' => 'required|max:1000',
                    'member_list_introduction_template_ar' => 'required|max:1000',
                    'introduction_template_en' => 'required|max:1000',
                    'introduction_template_ar' => 'required|max:1000',
                );
            case 'update':
                return array(
                    'template_name_en' => 'required',
                    'template_name_ar' => 'required',
                    'organization_id' => 'required',
                    'show_mom_header' => 'required',
                    'show_agenda_list' => 'required',
                    'show_timer' => 'required',
                    'show_presenters' => 'required',
                    'show_recommendation' => 'required',
                    'show_purpose' => 'required',
                    'show_participant_nickname' => 'required',
                    'show_participant_job' => 'required',
                    'show_participant_title' => 'required',
                    'show_conclusion' => 'required',
                    'show_vote_results' => 'required',
                    'show_vote_status' => 'required',
                    'conclusion_template_en' => 'max:1000',
                    'conclusion_template_ar' => 'max:1000',
                    'member_list_introduction_template_en' => 'required|max:1000',
                    'member_list_introduction_template_ar' => 'required|max:1000',
                    'introduction_template_en' => 'required|max:1000',
                    'introduction_template_ar' => 'required|max:1000',
                );
        }
    }

    public function meetings()
    {
        return $this->hasMany('Models\Meeting', 'meeting_mom_template_id');
    }

    public function logoImage()
    {
        return $this->belongsTo('Models\Image','logo_id');
    }

    public static function messages($action)
    {
        switch ($action) {
            case 'update':
                return array(
                    'template_name_ar.required' => ['message' => Lang::get('validation.custom.template_name_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.template_name_ar.required', [], 'ar')],

                    'template_name_en.required' => ['message' => Lang::get('validation.custom.template_name_en.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.template_name_en.required', [], 'ar')],

                    'introduction_template_ar.required' => ['message' => Lang::get('validation.custom.introduction_template_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.introduction_template_ar.required', [], 'ar')],

                    'introduction_template_en.required' => ['message' => Lang::get('validation.custom.introduction_template_en.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.introduction_template_en.required', [], 'ar')],

                    'member_list_introduction_template_ar.required' => ['message' => Lang::get('validation.custom.member_list_introduction_template_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.member_list_introduction_template_ar.required', [], 'ar')],

                    'member_list_introduction_template_en.required' => ['message' => Lang::get('validation.custom.member_list_introduction_template_en.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.member_list_introduction_template_en.required', [], 'ar')]
                );
            case 'save':
                return array(
                    'template_name_ar.required' => ['message' => Lang::get('validation.custom.template_name_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.template_name_ar.required', [], 'ar')],

                    'template_name_en.required' => ['message' => Lang::get('validation.custom.template_name_en.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.template_name_en.required', [], 'ar')],

                    'introduction_template_ar.required' => ['message' => Lang::get('validation.custom.introduction_template_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.introduction_template_ar.required', [], 'ar')],

                    'introduction_template_en.required' => ['message' => Lang::get('validation.custom.introduction_template_en.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.introduction_template_en.required', [], 'ar')],

                    'member_list_introduction_template_ar.required' => ['message' => Lang::get('validation.custom.member_list_introduction_template_ar.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.member_list_introduction_template_ar.required', [], 'ar')],

                    'member_list_introduction_template_en.required' => ['message' => Lang::get('validation.custom.member_list_introduction_template_en.required', [], 'en')
                        , 'message_ar' => Lang::get('validation.custom.member_list_introduction_template_en.required', [], 'ar')]
                );

        }
    }

}
