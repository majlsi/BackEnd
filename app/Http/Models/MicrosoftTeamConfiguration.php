<?php

namespace Models;

use \Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Lang;

class MicrosoftTeamConfiguration extends Model implements Auditable {

    use \OwenIt\Auditing\Auditable;
    protected $fillable = ['organization_id','microsoft_azure_app_id','microsoft_azure_tenant_id','microsoft_azure_client_secret','microsoft_azure_user_id'];	
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'microsoft_team_configurations';

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'microsoft_azure_app_id' => 'required',
                    'microsoft_azure_tenant_id' => 'required',
                    'microsoft_azure_client_secret' => 'required',
                    'microsoft_azure_user_id' => 'required',
                );
        }
    }

    public static function messages($action)
    {    
        switch ($action) {
            case 'save':
                return array(
                    'microsoft_azure_app_id.required' => ['message'=>Lang::get('validation.custom.microsoft_azure_app_id.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.microsoft_azure_app_id.required',[],'ar')],

                    'microsoft_azure_tenant_id.required' => ['message'=>Lang::get('validation.custom.microsoft_azure_tenant_id.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.microsoft_azure_tenant_id.required',[],'ar')],

                    'microsoft_azure_client_secret.required' => ['message'=>Lang::get('validation.custom.microsoft_azure_client_secret.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.microsoft_azure_client_secret.required',[],'ar')],

                    'microsoft_azure_user_id.required' => ['message'=>Lang::get('validation.custom.microsoft_azure_user_id.required',[],'en')
                    ,'message_ar'=>Lang::get('validation.custom.microsoft_azure_user_id.required',[],'ar')],

                );
            }
    }
}
