<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lang;
use OwenIt\Auditing\Contracts\Auditable;

class Organization extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'organization_name_en', 'organization_name_ar', 'organization_phone', 'is_active', 'organization_number_of_users', 'logo_id', 'time_zone_id', 'system_admin_id', 'organization_code', 'expiry_date_from', 'expiry_date_to', 'organization_type_id', 'api_url', 'front_url', 'redis_url',
        'signature_url', 'signature_username', 'signature_password', 'is_vote_enabled', 'has_two_factor_auth', 'disclosure_url', 'disclosure_file_id', 'directory_quota', 'enable_meeting_archiving', 'subscription_id', 'is_from_stc', 'stc_customer_ref', 'is_stakeholder_enabled', 'stakeholders_count'
    ];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $table = 'organizations';

    public static function rules($action, $id = null)
    {
        switch ($action) {
            case 'save':
                return [
                    'organization_name_ar' => 'required_without:organization_name_en',
                    'organization_name_en' => 'required_without:organization_name_ar',
                    'organization_phone' => 'required',
                    // 'organization_number_of_users' => 'required',
                    // 'time_zone_id' => 'required',
                    // 'organization_code' => 'required|unique:organizations,organization_code,NULL,id,deleted_at,NULL',
                ];
            case 'update':
                return [
                    'organization_name_ar' => 'required_without:organization_name_en',
                    'organization_name_en' => 'required_without:organization_name_ar',
                    'organization_phone' => 'required',
                    'organization_number_of_users' => 'required',
                    'directory_quota' => 'required',
                    // 'time_zone_id' => 'required',
                    'organization_code' => 'nullable|unique:organizations,organization_code,' . $id . ',id,deleted_at,NULL',
                ];
        }
    }

    public static function messages($action)
    {
        switch ($action) {
            case 'save':
                return [
                    'organization_name_ar.required_without' => ['message' => Lang::get('validation.custom.organization_name_ar.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.organization_name_ar.required', [], 'ar')],

                    'organization_name_en.required_without' => ['message' => Lang::get('validation.custom.organization_name_en.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.organization_name_en.required', [], 'ar')],

                    'organization_phone.required' => ['message' => Lang::get('validation.custom.organization_phone.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.organization_phone.required', [], 'ar')],

                    'organization_number_of_users.required' => ['message' => Lang::get('validation.custom.organization_number_of_users.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.organization_number_of_users.required', [], 'ar')],

                    'organization_code.required' => ['message' => Lang::get('validation.custom.organization_code.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.organization_code.required', [], 'ar')],

                    'organization_code.unique' => ['message' => Lang::get('validation.custom.organization_code.unique', [], 'en'), 'message_ar' => Lang::get('validation.custom.organization_code.unique', [], 'ar')],

                    'directory_quota.required' => ['message' => Lang::get('validation.custom.organization_directory_quota.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.organization_directory_quota.required', [], 'ar')],
                ];
            case 'update':
                return [
                    'organization_name_ar.required_without' => ['message' => Lang::get('validation.custom.organization_name_ar.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.organization_name_ar.required', [], 'ar')],

                    'organization_name_en.required_without' => ['message' => Lang::get('validation.custom.organization_name_en.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.organization_name_en.required', [], 'ar')],

                    'organization_phone.required' => ['message' => Lang::get('validation.custom.organization_phone.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.organization_phone.required', [], 'ar')],

                    'organization_number_of_users.required' => ['message' => Lang::get('validation.custom.organization_number_of_users.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.organization_number_of_users.required', [], 'ar')],

                    'organization_code.required' => ['message' => Lang::get('validation.custom.organization_code.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.organization_code.required', [], 'ar')],

                    'organization_code.unique' => ['message' => Lang::get('validation.custom.organization_code.unique', [], 'en'), 'message_ar' => Lang::get('validation.custom.organization_code.unique', [], 'ar')],

                    'directory_quota.required' => ['message' => Lang::get('validation.custom.organization_directory_quota.required', [], 'en'), 'message_ar' => Lang::get('validation.custom.organization_directory_quota.required', [], 'ar')],
                ];
        }
    }

    public function roles()
    {
        return $this->hasMany('Models\Role');
    }

    public function logoImage()
    {
        return $this->belongsTo('Models\Image', 'logo_id');
    }

    public function timeZones()
    {
        return $this->hasMany('Models\TimeZone');
    }

    public function timeZone()
    {
        return $this->belongsTo('Models\TimeZone', 'time_zone_id');
    }

    public function meetingTypes()
    {
        return $this->hasMany('Models\MeetingType');
    }

    public function committees()
    {
        return $this->hasMany('Models\Committee');
    }

    public function users()
    {
        return $this->hasMany('Models\User');
    }

    public function momTemplates()
    {
        return $this->hasMany('Models\MomTemplate');
    }

    public function decisionTypes()
    {
        return $this->hasMany('Models\DecisionType');
    }

    public function jobTitles()
    {
        return $this->hasMany('Models\JobTitle');
    }

    public function nicknames()
    {
        return $this->hasMany('Models\Nickname');
    }

    public function directories()
    {
        return $this->hasMany('Models\Directory');
    }
    public function files()
    {
        return $this->hasMany('Models\File');
    }

    public function disclosureFile()
    {
        return $this->belongsTo('Models\File', 'disclosure_file_id');
    }

    public function systemAdmin()
    {
        return $this->belongsTo('Models\User', 'system_admin_id');
    }
}
