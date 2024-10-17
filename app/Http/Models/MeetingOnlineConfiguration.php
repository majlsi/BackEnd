<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Lang;

class MeetingOnlineConfiguration extends Model implements Auditable{

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['meeting_id','online_meeting_app_id','microsoft_azure_app_id','microsoft_azure_tenant_id','microsoft_azure_client_secret',
            'microsoft_azure_user_id','zoom_api_key','zoom_api_secret','zoom_exp_minutes','zoom_scheduled_meeting_id','zoom_host_video','zoom_participant_video','zoom_cn_meeting',
            'zoom_in_meeting','zoom_join_before_host','zoom_mute_upon_entry','zoom_water_mark','zoom_use_pmi','zoom_audio','zoom_approval_type','zoom_auto_recording',
            'zoom_meeting_authentication','zoom_enforce_login_domains','zoom_alternative_hosts','zoom_registrants_email_notification'];
    						
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'meeting_online_configurations';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'online_meeting_app_id' => 'required',
                );
            case 'update':
                return array(
                    'online_meeting_app_id' => 'required',
                );
        }
    }

    public static function messages($action)
    {    
        switch ($action) {
            case 'save':
                return array(

                );
            }
    }

    public function meeting(){
        return $this->belongsTo('Models\Meeting', 'meeting_id');
    }
}
