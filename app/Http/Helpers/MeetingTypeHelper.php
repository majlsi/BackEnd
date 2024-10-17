<?php

namespace Helpers;

use Services\MeetingTypeService;

class MeetingTypeHelper
{
    private $meetingTypeService;

    public function __construct(MeetingTypeService $meetingTypeService)
    {
        $this->meetingTypeService = $meetingTypeService;
    }

    public function prepareMeetingTypesForOrganizationAdmin($organizationId){
        $systemMeetingTypes = $this->meetingTypeService->getSystemMeetingTypes()->toArray();
        foreach ($systemMeetingTypes as $key => $systemMeetingType) {
            $systemMeetingTypes[$key]['organization_id'] = $organizationId;
            $systemMeetingTypes[$key]['is_system'] = 0;
        }
        return $systemMeetingTypes;
    }
}
