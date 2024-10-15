<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Lang;

class UserOnlineConfiguration extends Model implements Auditable {

    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    protected $fillable = ['user_id','is_active','configuration_name_ar','configuration_name_en','zoom_configuration_id',
            'microsoft_configuration_id','online_meeting_app_id'];
    						
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'user_online_configurations';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'user_id' => 'required',
                    'configuration_name_ar' => 'sometimes',
                    'online_meeting_app_id' => 'required',
                    'configuration_name_en' => 'sometimes',
                    'microsoft_team_configuration' => 'required_if:online_meeting_app_id,' .config('onlineMeetingApp.microsoftTeams'),
                    'zoom_configuration' => 'required_if:online_meeting_app_id,'. config('onlineMeetingApp.zoom')
                );
            case 'update':
                return array(
                    'user_id' => 'required',
                    'configuration_name_ar' => 'sometimes',
                    'online_meeting_app_id' => 'required',
                    'configuration_name_en' => 'sometimes',
                    'microsoft_team_configuration' => 'required_if:online_meeting_app_id,' .config('onlineMeetingApp.microsoftTeams'),
                    'zoom_configuration' => 'required_if:online_meeting_app_id,'. config('onlineMeetingApp.zoom')
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

    public function zoomConfiguration(){
        return $this->belongsTo('Models\ZoomConfiguration');
    }

    public function microsoftTeamConfiguration(){
        return $this->belongsTo('Models\MicrosoftTeamConfiguration','microsoft_configuration_id');
    }

    public function user(){
        return $this->belongsTo('Models\User', 'user_id');
    }
}
