<?php

namespace Helpers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Services\MeetingService;
use Services\OrganizationService;
use Services\CommitteeService;
use Illuminate\Support\Str;
use Helpers\SecurityHelper;
use Carbon\Carbon;
use Services\TimeZoneService;
use Services\UserOnlineConfigurationService;

class MeetingHelper
{

    private $meetingService;
    private $organizationService;
    private $committeeService;
    private $securityHelper;
    private $timeZoneService;
    private $userOnlineConfigurationService;

    public function __construct(MeetingService $meetingService, OrganizationService $organizationService,
                                CommitteeService $committeeService, SecurityHelper $securityHelper, TimeZoneService $timeZoneService,
                                UserOnlineConfigurationService $userOnlineConfigurationService)
    {
        $this->meetingService = $meetingService;
        $this->organizationService = $organizationService;
        $this->committeeService = $committeeService;
        $this->securityHelper = $securityHelper;
        $this->timeZoneService = $timeZoneService;
        $this->userOnlineConfigurationService = $userOnlineConfigurationService;
    }

    public function prepareMeetingDataOnCreate($data,$organizationId){

        $organization =  $this->organizationService->getById($organizationId);
        $committee = $this->committeeService->getById($data['committee_id']);
        $meetingTimeZone = $this->timeZoneService->getById($data['time_zone_id']);

        $lastMeetingSequenceForOrganization = $this->meetingService->getLastMeetingSequenceForOrganization($organizationId);
   

        if(!$lastMeetingSequenceForOrganization){
            $lastMeetingSequenceForOrganization['meeting_sequence'] = 0;
        }else {
            $lastMeetingSequenceForOrganization = $lastMeetingSequenceForOrganization->toArray();
        }

        unset($data['meeting_reminders']);
        if(isset($data['meeting_schedule_to'])){
            $data['meeting_schedule_to'] = new Carbon($data['meeting_schedule_to']['year'].'-'.$data['meeting_schedule_to']['month'].'-'.$data['meeting_schedule_to']['day'].' '.$data['meeting_schedule_to']['hour'].':'.$data['meeting_schedule_to']['minute'].':'.$data['meeting_schedule_to']['second']);
        }
        if(isset($data['meeting_schedule_from'])){
            $data['meeting_schedule_from'] = new Carbon($data['meeting_schedule_from']['year'].'-'.$data['meeting_schedule_from']['month'].'-'.$data['meeting_schedule_from']['day'].' '.$data['meeting_schedule_from']['hour'].':'.$data['meeting_schedule_from']['minute'].':'.$data['meeting_schedule_from']['second']);
        }
        $data['meeting_status_id'] = config('meetingStatus.draft');
        $data['organization_id'] = $organizationId;

        // add meeting online config
        if(isset($data['online_configuration_id'])){
            $onlineConfig = $this->userOnlineConfigurationService->getById($data['online_configuration_id']);
            $data['meeting_online_configuration'] = $this->prepareMeetingOnlineConfiguration($onlineConfig);
        }
        // generate meeting code
        $date = new Carbon();
        $meetingName = $organization['organization_code'] . '-';
        if (!config('customSetting.removeCommitteeCode') || $committee['committee_code'] != null) {
            $meetingName = $meetingName . $committee['committee_code'] . '-';
        }
        $meetingName = $meetingName . $date->format('d') . $date->format('m') . $date->format('y') . '-' . sprintf('%03d', $lastMeetingSequenceForOrganization['meeting_sequence'] + 1);
        $data['meeting_code'] = $meetingName;
        $data['meeting_sequence'] = $lastMeetingSequenceForOrganization['meeting_sequence']+1;
        $data['created_by'] = $this->securityHelper->getCurrentUser()->id;
        return $data;
    }

    public function prepareMeetingDataOnUpdate($data){
        if(isset($data['meeting_schedule_to'])){
            $data['meeting_schedule_to'] = new Carbon($data['meeting_schedule_to']['year'].'-'.$data['meeting_schedule_to']['month'].'-'.$data['meeting_schedule_to']['day'].' '.$data['meeting_schedule_to']['hour'].':'.$data['meeting_schedule_to']['minute'].':'.$data['meeting_schedule_to']['second']);
            $data['meeting_schedule_to']= $data['meeting_schedule_to']->toDateTimeString();
        
        }
        if(isset($data['meeting_schedule_from'])){
            $data['meeting_schedule_from'] = new Carbon($data['meeting_schedule_from']['year'].'-'.$data['meeting_schedule_from']['month'].'-'.$data['meeting_schedule_from']['day'].' '.$data['meeting_schedule_from']['hour'].':'.$data['meeting_schedule_from']['minute'].':'.$data['meeting_schedule_from']['second']);
            $data['meeting_schedule_from']= $data['meeting_schedule_from']->toDateTimeString();
        }

        if(isset($data['online_configuration_id'])){
            $onlineConfig = $this->userOnlineConfigurationService->getById($data['online_configuration_id']);
            $data['meeting_online_configuration'] = $this->prepareMeetingOnlineConfiguration($onlineConfig);
        } else {
            $data['online_configuration_id'] = null;
            $data['is_online_configuration_id_removed'] = true;
        }

        unset($data['zoom_meeting_id']);
        unset($data['zoom_meeting_password']);
        unset($data['zoom_start_url']);
        unset($data['zoom_join_url']);
        unset($data['chat_room_id']);
        unset($data['meeting_code']);
        unset($data['related_meeting_id']);
        unset($data['version_number']);
        unset($data['is_published']);

        return $data;
    }

    public function prepareMeetingPublishedEmailData($meeting){
        $emailData=[];

        $emailData["meeting_schedule_from"]=$meeting["meeting_schedule_from"];
        $emailData["meeting_schedule_to"]=$meeting["meeting_schedule_to"];
        $emailData["meeting_title_ar"]=$meeting["meeting_title_ar"]? $meeting["meeting_title_ar"] : $meeting["meeting_title_en"];
        $emailData["meeting_venue_ar"]=$meeting["meeting_venue_ar"]? $meeting["meeting_venue_ar"] : $meeting["meeting_venue_en"];

        if($meeting['meeting_title_en'] == null || empty($meeting['meeting_title_en'])){
            $emailData["meeting_title_en"] =$meeting["meeting_title_ar"];
        }
        else{
            $emailData["meeting_title_en"]=$meeting["meeting_title_en"];
        }

        if($meeting['meeting_venue_en'] == null || empty($meeting['meeting_venue_en'])){
            $emailData["meeting_venue_en"] =$meeting["meeting_venue_ar"];
        }
        else{
            $emailData["meeting_venue_en"]=$meeting["meeting_venue_en"];
        }
        return $emailData;
        
    }

    public function prepareMeetingSignatureEmailData($meeting){
        $emailData=[];

        $emailData["meeting_title_ar"]=$meeting["meeting_title_ar"]? $meeting["meeting_title_ar"] : $meeting["meeting_title_en"];
        $emailData["id"]=$meeting["id"];

        if($meeting['meeting_title_en'] == null || empty($meeting['meeting_title_en'])){
            $emailData["meeting_title_en"] =$meeting["meeting_title_ar"];
        }
        else{
            $emailData["meeting_title_en"]=$meeting["meeting_title_en"];
        }
        return $emailData;
        
    }


    public function  addCanViewAttendeeToResults($meetingList ,$canViewAttendee){
        foreach ($meetingList->Results as $key => $value) {
            $value["can_view_attendee"]=$canViewAttendee;
        }
        return $meetingList;
    }

    public function prepareMeetingOnlineConfiguration($onlineConfig){
        $data = [];

        $data['online_meeting_app_id'] = $onlineConfig->online_meeting_app_id;
        if($onlineConfig->online_meeting_app_id == config('onlineMeetingApp.microsoftTeams')){
            $data['microsoft_azure_app_id'] = $onlineConfig->microsoftTeamConfiguration->microsoft_azure_app_id;
            $data['microsoft_azure_tenant_id'] = $onlineConfig->microsoftTeamConfiguration->microsoft_azure_tenant_id;
            $data['microsoft_azure_client_secret'] = $onlineConfig->microsoftTeamConfiguration->microsoft_azure_client_secret;
            $data['microsoft_azure_user_id'] = $onlineConfig->microsoftTeamConfiguration->microsoft_azure_user_id;
            $data['zoom_api_key'] = null;
            $data['zoom_api_secret'] = null;
            $data['zoom_exp_minutes'] = null;
            $data['zoom_scheduled_meeting_id'] = null;
            $data['zoom_host_video'] = 0;
            $data['zoom_participant_video'] = 0;
            $data['zoom_cn_meeting'] = 0;
            $data['zoom_in_meeting'] = 0;
            $data['zoom_join_before_host'] = 0;
            $data['zoom_mute_upon_entry'] = 0;
            $data['zoom_water_mark'] = 0;
            $data['zoom_use_pmi'] = 0;
            $data['zoom_audio'] = null;
            $data['zoom_approval_type'] = null;
            $data['zoom_auto_recording'] = null;
            $data['zoom_meeting_authentication'] = 0;
            $data['zoom_enforce_login_domains'] = null;
            $data['zoom_alternative_hosts'] = null;
            $data['zoom_registrants_email_notification'] = 0;
        } else if($onlineConfig->online_meeting_app_id == config('onlineMeetingApp.zoom')){
            $data['microsoft_azure_app_id'] = null;
            $data['microsoft_azure_tenant_id'] = null;
            $data['microsoft_azure_client_secret'] = null;
            $data['microsoft_azure_user_id'] = null;
            $data['zoom_api_key'] = $onlineConfig->zoomConfiguration->zoom_api_key;
            $data['zoom_api_secret'] = $onlineConfig->zoomConfiguration->zoom_api_secret;
            $data['zoom_exp_minutes'] = $onlineConfig->zoomConfiguration->zoom_exp_minutes;
            $data['zoom_scheduled_meeting_id'] = $onlineConfig->zoomConfiguration->zoom_scheduled_meeting_id;
            $data['zoom_host_video'] = $onlineConfig->zoomConfiguration->zoom_host_video;
            $data['zoom_participant_video'] = $onlineConfig->zoomConfiguration->zoom_participant_video;
            $data['zoom_cn_meeting'] = $onlineConfig->zoomConfiguration->zoom_cn_meeting;
            $data['zoom_in_meeting'] = $onlineConfig->zoomConfiguration->zoom_in_meeting;
            $data['zoom_join_before_host'] = $onlineConfig->zoomConfiguration->zoom_join_before_host;
            $data['zoom_mute_upon_entry'] = $onlineConfig->zoomConfiguration->zoom_mute_upon_entry;
            $data['zoom_water_mark'] = $onlineConfig->zoomConfiguration->zoom_water_mark;
            $data['zoom_use_pmi'] = $onlineConfig->zoomConfiguration->zoom_use_pmi;
            $data['zoom_audio'] = $onlineConfig->zoomConfiguration->zoom_audio;
            $data['zoom_approval_type'] = $onlineConfig->zoomConfiguration->zoom_approval_type;
            $data['zoom_auto_recording'] = $onlineConfig->zoomConfiguration->zoom_auto_recording;
            $data['zoom_meeting_authentication'] = $onlineConfig->zoomConfiguration->zoom_meeting_authentication;
            $data['zoom_enforce_login_domains'] = $onlineConfig->zoomConfiguration->zoom_enforce_login_domains;
            $data['zoom_alternative_hosts'] = $onlineConfig->zoomConfiguration->zoom_alternative_hosts;
            $data['zoom_registrants_email_notification'] = $onlineConfig->zoomConfiguration->zoom_registrants_email_notification;
        }

        return $data;
    }    
}
