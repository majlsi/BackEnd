<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Lang;

class ZoomConfiguration extends Model implements Auditable{

    use \OwenIt\Auditing\Auditable;
    protected $fillable = ['organization_id','zoom_api_key','zoom_api_secret','zoom_exp_minutes','zoom_scheduled_meeting_id',
            'zoom_host_video','zoom_participant_video','zoom_cn_meeting','zoom_in_meeting','zoom_join_before_host',
            'zoom_mute_upon_entry','zoom_water_mark','zoom_use_pmi','zoom_audio','zoom_approval_type',
            'zoom_auto_recording','zoom_meeting_authentication','zoom_enforce_login_domains','zoom_alternative_hosts',
            'zoom_registrants_email_notification'];
    						
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'zoom_configurations';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'zoom_api_key' => 'required',
                    'zoom_api_secret' => 'required',
                    // 'zoom_audio' => 'required',
                    // 'zoom_approval_type' => 'required',
                    // 'zoom_auto_recording' => 'required',
                );
        }
    }

    public static function messages($action)
    {    
        switch ($action) {
            case 'save':
                return array(
                    'zoom_api_key.required' => ['message'=>Lang::get('validation.custom.zoom_api_key.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.zoom_api_key.required',[],'ar')],

                    'zoom_api_secret.required' => ['message'=>Lang::get('validation.custom.zoom_api_secret.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.zoom_api_secret.required',[],'ar')],

                    'zoom_audio.required' => ['message'=>Lang::get('validation.custom.zoom_audio.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.zoom_audio.required',[],'ar')],

                    'zoom_approval_type.required' => ['message'=>Lang::get('validation.custom.zoom_approval_type.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.zoom_approval_type.required',[],'ar')],

                    'zoom_auto_recording.required' => ['message'=>Lang::get('validation.custom.zoom_auto_recording.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.zoom_auto_recording.required',[],'ar')],

                );
            }
    }
}
