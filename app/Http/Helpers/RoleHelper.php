<?php

namespace Helpers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Services\RoleService;
use Illuminate\Support\Str;

class RoleHelper
{

    private $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function prepareRolesDataForOrganization($organizationId){
        return $rolesData = [
           /*  ['role_name' => 'Organization Admin', 'role_name_ar' => 'ادمن المنظمة', 'organization_id' => $organizationId], */
            ['role_name' => 'Secretary', 'role_name_ar' => 'سكرتير', 'organization_id' => $organizationId, 'is_meeting_role' => 1,'can_assign' => 1,'role_code' => config('roleCodes.secretary')],
            ['role_name' => 'Board Members', 'role_name_ar' => 'الاعضاء', 'organization_id' => $organizationId, 'is_meeting_role' => 1,'can_assign' => 1,'role_code' => config('roleCodes.boardMembers')],
            ['role_name' => 'Participant' , 'role_name_ar' => 'مشارك', 'organization_id' => $organizationId, 'is_meeting_role' => 1,'can_assign' => 1,'role_code' => config('roleCodes.participant')],
            
        ];
    }
}
