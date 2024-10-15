<?php

namespace Helpers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Services\OrganizationService;
use Illuminate\Support\Str;

class OrganizationHelper
{

    private $organizationService;

    public function __construct(OrganizationService $organizationService)
    {
        $this->organizationService = $organizationService;
    }

    public function prepareOrganizationDataOnCreate($data){
        $organizationData = [];

        if(isset($data['organization_name_en'])){
            $organizationData['organization_name_en'] = $data['organization_name_en'];
        }
        if(isset($data['organization_name_ar'])){
            $organizationData['organization_name_ar'] = $data['organization_name_ar'];
        }
        if(isset($data['organization_phone'])){
            $organizationData['organization_phone'] = $data['organization_phone'];
        }
        if(isset($data['organization_number_of_users'])){
            $organizationData['organization_number_of_users'] = $data['organization_number_of_users'];
        }
        if (isset($data['organization_code'])) {
            $organizationData['organization_code'] = $data['organization_code'];
        }

        if (isset($data['time_zone_id'])) {
            $organizationData['time_zone_id'] = $data['time_zone_id'];
        }
        if (isset($data['stc_customer_ref'])) {
            $organizationData['stc_customer_ref'] = $data['stc_customer_ref'];
        } else {
            $organizationData['stc_customer_ref'] = 0;
        }

        $organizationData["organization_type_id"]=config('organizationTypes.cloud');
        $organizationData['api_url'] =config('appUrls.api.cloudUrl');
        $organizationData['front_url']=config('appUrls.front.cloudUrl');
        $organizationData['redis_url']=config('appUrls.redis.cloudUrl');
        $organizationData["signature_url"] = config('signature.SIGNATURE_URL');
        $organizationData["signature_username"] = config('signature.SIGNATURE_USERNAME');
        $organizationData["signature_password"] = config('signature.SIGNATURE_PASSWORD');
        $organizationData["has_two_factor_auth"] =  1;
        return $organizationData;
    }

    public function prepareOrganizationUpdateData($data){

        if(isset($data['logo_image'])){
            unset($data['logo_image']);
        }
        if(isset($data["organization_type_id"]) && $data["organization_type_id"] == config('organizationTypes.cloud')){
            $data['api_url'] =config('appUrls.api.cloudUrl');
            $data['front_url']=config('appUrls.front.cloudUrl');
            $data['redis_url']=config('appUrls.redis.cloudUrl');
        }

        return $data;
    }

    public function prepareJobTitlesForOrganizationAdmin($organizationId,$systemJobTitles){
        foreach ($systemJobTitles as $key => $systemJobTitle) {
            $systemJobTitles[$key]['organization_id'] = $organizationId;
            $systemJobTitles[$key]['is_system'] = 0;
        }
        return $systemJobTitles;
    }

    public function prepareNicknamesForOrganizationAdmin($organizationId,$systemNicknames){
        foreach ($systemNicknames as $key => $systemNickname) {
            $systemNicknames[$key]['organization_id'] = $organizationId;
            $systemNicknames[$key]['is_system'] = 0;
        }
        return $systemNicknames;
    }
}
