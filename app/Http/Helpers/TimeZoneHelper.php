<?php

namespace Helpers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Services\TimeZoneService;

class TimeZoneHelper
{
    private $timeZoneService;

    public function __construct(TimeZoneService $timeZoneService)
    {
        $this->timeZoneService = $timeZoneService;
    }

    public function prepareTimeZonesForOrganizationAdmin($organizationId){
        $systemTimeZones = $this->timeZoneService->getSystemTimeZones()->toArray();
        foreach ($systemTimeZones as $key => $systemTimeZone) {
            $systemTimeZones[$key]['organization_id'] = $organizationId;
            $systemTimeZones[$key]['is_system'] = 0;
        }
        return $systemTimeZones;
    }
}