<?php

namespace Helpers;

use Carbon\Carbon;

class MicrosoftTeamConfigurationHelper
{

    public function __construct()
    {
    }

    public function prepareData($data)
    {
        $zoomConfigration = [];

        if(isset($data['microsoft_azure_app_id'])) {
            $zoomConfigration['microsoft_azure_app_id'] = $data['microsoft_azure_app_id'];
        }
        if(isset($data['microsoft_azure_tenant_id'])) {
            $zoomConfigration['microsoft_azure_tenant_id'] = $data['microsoft_azure_tenant_id'];
        }
        if(isset($data['microsoft_azure_client_secret'])) {
            $zoomConfigration['microsoft_azure_client_secret'] = $data['microsoft_azure_client_secret'];
        }
        if(isset($data['microsoft_azure_user_id'])) {
            $zoomConfigration['microsoft_azure_user_id'] = $data['microsoft_azure_user_id'];
        }

       return $zoomConfigration;
    }

    public function prepareMicrosoftTeamsMeetingDataAtCreation($meeting,$microsoftTeamConfiguration){
        $meetingData = [];

        $dateFrom = Carbon::parse($meeting->meeting_schedule_from);
        $dateTo = Carbon::parse($meeting->meeting_schedule_to);

        $meetingData['subject'] = isset($meeting->meeting_title_ar)? $meeting->meeting_title_ar : $meeting->meeting_title_en;
        $meetingData['meetingType'] = config('microsoftGraph.meetingType');
        $meetingData['startDateTime'] = Carbon::parse($meeting->meeting_schedule_from)->subHours($meeting->timeZone->diff_hours)->format('Y-m-d\TH:i:s\Z');
        $meetingData['endDateTime'] =  Carbon::parse($meeting->meeting_schedule_to)->subHours($meeting->timeZone->diff_hours)->format('Y-m-d\TH:i:s\Z');
        $meetingData['participants']['organizer']['identity']['user']['id'] = $microsoftTeamConfiguration->microsoft_azure_user_id;

        $meetingData['header'] = $this->getHeaderConfigration($microsoftTeamConfiguration);

        return $meetingData;
    }


    public function getHeaderConfigration($microsoftTeamConfiguration){
        $headerData = [];

        $headerData['microsoft_azure_app_id'] = $microsoftTeamConfiguration->microsoft_azure_app_id;
        $headerData['microsoft_azure_tenant_id'] = $microsoftTeamConfiguration->microsoft_azure_tenant_id;
        $headerData['microsoft_azure_client_secret'] = $microsoftTeamConfiguration->microsoft_azure_client_secret;

        return $headerData;
    }
}
