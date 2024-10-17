<?php

namespace Helpers;

use Services\UserOnlineConfigurationService;


class MeetingVersionHelper
{
    private $userOnlineConfigurationService;

    public function __construct(UserOnlineConfigurationService $userOnlineConfigurationService)
    {
        $this->userOnlineConfigurationService = $userOnlineConfigurationService;
    }

    public function prepareVersionOfMeetingData($data,$meeting,$versionNumebr,$isNew)
    {    
        $newVersionOfMeeting = [];

        if(isset($data['meeting_title_ar'])){
            $newVersionOfMeeting['meeting_title_ar'] = $data['meeting_title_ar'];
        } else if ($isNew){
            $newVersionOfMeeting['meeting_title_ar'] = $meeting['meeting_title_ar'];
        }
        if(isset($data['meeting_title_en'])){
            $newVersionOfMeeting['meeting_title_en'] = $data['meeting_title_en'];
        } else if ($isNew){
            $newVersionOfMeeting['meeting_title_en'] = $meeting['meeting_title_en'];
        }
        if(isset($meeting['meeting_code'])){
            $newVersionOfMeeting['meeting_code'] = $meeting['meeting_code'] . '-'.$versionNumebr;
        }
        if(isset($meeting['meeting_sequence'])){
            $newVersionOfMeeting['meeting_sequence'] = $meeting['meeting_sequence'];
        }
        if(isset($meeting['id'])){
            $newVersionOfMeeting['related_meeting_id'] = $meeting['id'];
        }
        if(isset($data['meeting_type_id'])){
            $newVersionOfMeeting['meeting_type_id'] = $data['meeting_type_id'];
        } else if ($isNew){
            $newVersionOfMeeting['meeting_type_id'] = $meeting['meeting_type_id'];
        }
        if(isset($data['committee_id'])){
            $newVersionOfMeeting['committee_id'] = $data['committee_id'];
        } else if ($isNew){
            $newVersionOfMeeting['committee_id'] = $meeting['committee_id'];
        }
        if(isset($data['time_zone_id'])){
            $newVersionOfMeeting['time_zone_id'] = $data['time_zone_id'];
        } else if ($isNew){
            $newVersionOfMeeting['time_zone_id'] = $meeting['time_zone_id'];
        }
        if(isset($data['proposal_id'])){
            $newVersionOfMeeting['proposal_id'] = $data['proposal_id'];
        } else if ($isNew){
            $newVersionOfMeeting['proposal_id'] = $meeting['proposal_id'];
        }
        if(isset($data['meeting_description_ar'])){
            $newVersionOfMeeting['meeting_description_ar'] = $data['meeting_description_ar'];
        } else if ($isNew){
            $newVersionOfMeeting['meeting_description_ar'] = $meeting['meeting_description_ar'];
        }
        if(isset($data['meeting_description_en'])){
            $newVersionOfMeeting['meeting_description_en'] = $data['meeting_description_en'];
        } else if ($isNew){
            $newVersionOfMeeting['meeting_description_en'] = $meeting['meeting_description_en'];
        }
        if(isset($data['meeting_note_ar'])){
            $newVersionOfMeeting['meeting_note_ar'] = $data['meeting_note_ar'];
        } else if ($isNew){
            $newVersionOfMeeting['meeting_note_ar'] = $meeting['meeting_note_ar'];
        }
        if(isset($data['meeting_note_en'])){
            $newVersionOfMeeting['meeting_note_en'] = $data['meeting_note_en'];
        } else if ($isNew){
            $newVersionOfMeeting['meeting_note_en'] = $meeting['meeting_note_en'];
        }
        if(isset($data['meeting_venue_ar'])){
            $newVersionOfMeeting['meeting_venue_ar'] = $data['meeting_venue_ar'];
        } else if ($isNew){
            $newVersionOfMeeting['meeting_venue_ar'] = $meeting['meeting_venue_ar'];
        }
        if(isset($data['meeting_venue_en'])){
            $newVersionOfMeeting['meeting_venue_en'] = $data['meeting_venue_en'];
        } else if ($isNew){
            $newVersionOfMeeting['meeting_venue_en'] = $meeting['meeting_venue_en'];
        }
        if(isset($meeting['created_by'])){
            $newVersionOfMeeting['created_by'] = $meeting['created_by'];
        }
        if(isset($data['meeting_status_id'])){
            $newVersionOfMeeting['meeting_status_id'] = $data['meeting_status_id'];
            if($data['meeting_status_id'] != $meeting['meeting_status_id'] && in_array($data['meeting_status_id'],[config('meetingStatus.publish'),config('meetingStatus.publishAgenda'),config('meetingStatus.start'),config('meetingStatus.end')]) && !$isNew) {
                $newVersionOfMeeting['is_published'] = true;
            }
        } else if ($isNew){
            $newVersionOfMeeting['meeting_status_id'] = $meeting['meeting_status_id'];
            $newVersionOfMeeting['is_published'] = false;
        }
        if(isset($meeting['organization_id'])){
            $newVersionOfMeeting['organization_id'] = $meeting['organization_id'];
        }
        if(isset($data['document_id'])){
            $newVersionOfMeeting['document_id'] = $data['document_id'];
        } else if ($isNew){
            $newVersionOfMeeting['document_id'] = $meeting['document_id'];
        }
        if(isset($data['is_signature_sent'])){
            $newVersionOfMeeting['is_signature_sent'] = $data['is_signature_sent'];
        } else if ($isNew){
            $newVersionOfMeeting['is_signature_sent'] = $meeting['is_signature_sent'];
        }
        if(isset($data['is_mom_sent'])){
            $newVersionOfMeeting['is_mom_sent'] = $data['is_mom_sent'];
        } else if ($isNew){
            $newVersionOfMeeting['is_mom_sent'] = $meeting['is_mom_sent'];
        }
        if(isset($data['meeting_schedule_from'])){
            $newVersionOfMeeting['meeting_schedule_from'] = $data['meeting_schedule_from'];
        } else if ($isNew){
            $newVersionOfMeeting['meeting_schedule_from'] = $meeting['meeting_schedule_from'];
        }
        if(isset($data['meeting_schedule_to'])){
            $newVersionOfMeeting['meeting_schedule_to'] = $data['meeting_schedule_to'];
        } else if ($isNew){
            $newVersionOfMeeting['meeting_schedule_to'] = $meeting['meeting_schedule_to'];
        }
        if(isset($data['location_lat'])){
            $newVersionOfMeeting['location_lat'] = $data['location_lat'];
        } else if ($isNew){
            $newVersionOfMeeting['location_lat'] = $meeting['location_lat'];
        }
        if(isset($data['location_long'])){
            $newVersionOfMeeting['location_long'] = $data['location_long'];
        } else if ($isNew){
            $newVersionOfMeeting['location_long'] = $meeting['location_long'];
        }
        if(isset($data['zoom_meeting_id'])){
            $newVersionOfMeeting['zoom_meeting_id'] = $data['zoom_meeting_id'];
        } else if ($isNew){
            $newVersionOfMeeting['zoom_meeting_id'] = $meeting['zoom_meeting_id'];
        }
        if(isset($data['zoom_meeting_password'])){
            $newVersionOfMeeting['zoom_meeting_password'] = $data['zoom_meeting_password'];
        } else if ($isNew){
            $newVersionOfMeeting['zoom_meeting_password'] = $meeting['zoom_meeting_password'];
        }
        if(isset($data['zoom_start_url'])){
            $newVersionOfMeeting['zoom_start_url'] = $data['zoom_start_url'];
        } else if ($isNew){
            $newVersionOfMeeting['zoom_start_url'] = $meeting['zoom_start_url'];
        }
        if(isset($data['zoom_join_url'])){
            $newVersionOfMeeting['zoom_join_url'] = $data['zoom_join_url'];
        } else if ($isNew){
            $newVersionOfMeeting['zoom_join_url'] = $meeting['zoom_join_url'];
        }
        if(isset($data['microsoft_teams_meeting_id'])){
            $newVersionOfMeeting['microsoft_teams_meeting_id'] = $data['microsoft_teams_meeting_id'];
        } else if ($isNew){
            $newVersionOfMeeting['microsoft_teams_meeting_id'] = $meeting['microsoft_teams_meeting_id'];
        }
        if(isset($data['microsoft_teams_join_url'])){
            $newVersionOfMeeting['microsoft_teams_join_url'] = $data['microsoft_teams_join_url'];
        } else if ($isNew){
            $newVersionOfMeeting['microsoft_teams_join_url'] = $meeting['microsoft_teams_join_url'];
        }
        if(isset($data['microsoft_teams_join_web_url'])){
            $newVersionOfMeeting['microsoft_teams_join_web_url'] = $data['microsoft_teams_join_web_url'];
        } else if ($isNew){
            $newVersionOfMeeting['microsoft_teams_join_web_url'] = $meeting['microsoft_teams_join_web_url'];
        }
        if(isset($data['microsoft_teams_video_teleconference_id'])){
            $newVersionOfMeeting['microsoft_teams_video_teleconference_id'] = $data['microsoft_teams_video_teleconference_id'];
        } else if ($isNew){
            $newVersionOfMeeting['microsoft_teams_video_teleconference_id'] = $meeting['microsoft_teams_video_teleconference_id'];
        }
        if(isset($data['chat_room_id'])){
            $newVersionOfMeeting['chat_room_id'] = $data['chat_room_id'];
        } else if ($isNew){
            $newVersionOfMeeting['chat_room_id'] = $meeting['chat_room_id'];
        }
        if(isset($data['last_message_date'])){
            $newVersionOfMeeting['last_message_date'] = $data['last_message_date'];
        } else if ($isNew){
            $newVersionOfMeeting['last_message_date'] = $meeting['last_message_date'];
        }
        if(isset($data['last_message_text'])){
            $newVersionOfMeeting['last_message_text'] = $data['last_message_text'];
        } else if ($isNew){
            $newVersionOfMeeting['last_message_text'] = $meeting['last_message_text'];
        }
        if(isset($data['meeting_reminders'])) {
            $newVersionOfMeeting['meeting_reminders'] = $data['meeting_reminders'];
        } else if ($isNew){
            $newVersionOfMeeting['meeting_reminders'] = $meeting['meeting_reminders'];
        }
        if(isset($data['meeting_mom_template_id'])) {
            $newVersionOfMeeting['meeting_mom_template_id'] = $data['meeting_mom_template_id'];
        } else if ($isNew){
            $newVersionOfMeeting['meeting_mom_template_id'] = $meeting['meeting_mom_template_id'];
        }
        if(isset($data['online_configuration_id'])) {
            $newVersionOfMeeting['online_configuration_id'] = $data['online_configuration_id'];
            $onlineConfig = $this->userOnlineConfigurationService->getById($data['online_configuration_id']);
            $newVersionOfMeeting['meeting_online_configuration'] = $this->prepareMeetingOnlineConfiguration($onlineConfig);
        }else if ($isNew){
            $newVersionOfMeeting['online_configuration_id'] = $meeting['online_configuration_id'];
            $meetingOnlineConfiguration = $meeting->meetingOnlineConfigurations()->first();
            if($meetingOnlineConfiguration){
                $newVersionOfMeeting['meeting_online_configuration'] = $this->prepareOnlineConfigurationOfMeeting($meetingOnlineConfiguration);
            }
        }
        if(isset($data['meeting_attendance_percentage'])){
            $newVersionOfMeeting['meeting_attendance_percentage'] = $data['meeting_attendance_percentage'];
        } else if ($isNew){
            $newVersionOfMeeting['meeting_attendance_percentage'] = $meeting['meeting_attendance_percentage'];
        }
        if(isset($data['is_online_configuration_id_removed'])){
            $newVersionOfMeeting['online_configuration_id'] = null;
            $newVersionOfMeeting['microsoft_teams_video_teleconference_id'] = null;
            $newVersionOfMeeting['microsoft_teams_join_web_url'] = null;
            $newVersionOfMeeting['microsoft_teams_join_url'] = null;
            $newVersionOfMeeting['microsoft_teams_meeting_id'] = null;
            $newVersionOfMeeting['zoom_join_url'] = null;
            $newVersionOfMeeting['zoom_start_url'] = null;
            $newVersionOfMeeting['zoom_meeting_password'] = null;
            $newVersionOfMeeting['zoom_meeting_id'] = null;
        }
        if (isset($data['is_microsoft_meeting'])) {
            $newVersionOfMeeting['zoom_join_url'] = null;
            $newVersionOfMeeting['zoom_start_url'] = null;
            $newVersionOfMeeting['zoom_meeting_password'] = null;
            $newVersionOfMeeting['zoom_meeting_id'] = null;
        }
        if(isset($data['is_zoom'])){
            $newVersionOfMeeting['microsoft_teams_video_teleconference_id'] = null;
            $newVersionOfMeeting['microsoft_teams_join_web_url'] = null;
            $newVersionOfMeeting['microsoft_teams_join_url'] = null;
            $newVersionOfMeeting['microsoft_teams_meeting_id'] = null;
        }
        if(isset($meeting['directory_id'])){
            $newVersionOfMeeting['directory_id'] = $meeting['directory_id'];
        }
        if (isset($data['meeting_stakeholders_percentage'])) {
            $newVersionOfMeeting['meeting_stakeholders_percentage'] = $data['meeting_stakeholders_percentage'];
        }
        $newVersionOfMeeting['version_number'] = $versionNumebr;
        return $newVersionOfMeeting;
    }

    public function prepareMasterMeetingDataAtUpdate($meeting,$data)
    {
        $meetingData = [];
        
        if($meeting->meeting_status_id == config('meetingStatus.draft') || (isset($data['meeting_status_id']) && $data['meeting_status_id'] == config('meetingStatus.draft'))){
            $meetingData = $data;
        } else {
            if (isset($data['meeting_status_id'])){
                $meetingData['meeting_status_id'] = $data['meeting_status_id'];
            }
            if (isset($data['document_id'])){
                $meetingData['document_id'] = $data['document_id'];
            }
            if(isset($data['is_signature_sent'])){
                $meetingData['is_signature_sent'] = $data['is_signature_sent'];
            }
            if(isset($data['is_mom_sent'])){
                $meetingData['is_mom_sent'] = $data['is_mom_sent'];
            }
            if(isset($data['last_message_text'])){
                $meetingData['last_message_text'] = $data['last_message_text'];
            }
            if(isset($data['last_message_date'])){
                $meetingData['last_message_date'] = $data['last_message_date'];
            }
            if(isset($data['chat_room_id'])){
                $meetingData['chat_room_id'] = $data['chat_room_id'];
            }
            // if(isset($data['microsoft_teams_video_teleconference_id'])){
            //     $meetingData['microsoft_teams_video_teleconference_id'] = $data['microsoft_teams_video_teleconference_id'];
            // }
            // if(isset($data['microsoft_teams_join_web_url'])){
            //     $meetingData['microsoft_teams_join_web_url'] = $data['microsoft_teams_join_web_url'];
            // }
            // if(isset($data['microsoft_teams_join_url'])){
            //     $meetingData['microsoft_teams_join_url'] = $data['microsoft_teams_join_url'];
            // }
            // if(isset($data['zoom_meeting_id'])){
            //     $meetingData['zoom_meeting_id'] = $data['zoom_meeting_id'];
            // }
            // if(isset($data['zoom_meeting_password'])){
            //     $meetingData['zoom_meeting_password'] = $data['zoom_meeting_password'];
            // }
            // if(isset($data['microsoft_teams_meeting_id'])){
            //     $meetingData['microsoft_teams_meeting_id'] = $data['microsoft_teams_meeting_id'];
            // }
            // if(isset($data['zoom_join_url'])){
            //     $meetingData['zoom_join_url'] = $data['zoom_join_url'];
            // }
            // if(isset($data['zoom_start_url'])){
            //     $meetingData['zoom_start_url'] = $data['zoom_start_url'];
            // }   
            if(isset($data['meeting_mom_template_id'])){
                $meetingData['meeting_mom_template_id'] = $data['meeting_mom_template_id'];
            }
            if (isset($data['meeting_stakeholders_percentage'])) {
                $meetingData['meeting_stakeholders_percentage'] = $data['meeting_stakeholders_percentage'];
            }
          
        }

        return $meetingData;
    }

    public function prepareMasterMeetingData($versionOfMeeting){
        $masterMeetingdata = [];

        $masterMeetingdata['meeting_title_ar'] = $versionOfMeeting->meeting_title_ar;
        $masterMeetingdata['meeting_title_en'] = $versionOfMeeting->meeting_title_en;
        $masterMeetingdata['meeting_type_id'] = $versionOfMeeting->meeting_type_id;
        $masterMeetingdata['committee_id'] = $versionOfMeeting->committee_id;
        $masterMeetingdata['time_zone_id'] = $versionOfMeeting->time_zone_id;
        $masterMeetingdata['proposal_id'] = $versionOfMeeting->proposal_id;
        $masterMeetingdata['meeting_description_ar'] = $versionOfMeeting->meeting_description_ar;
        $masterMeetingdata['meeting_description_en'] = $versionOfMeeting->meeting_description_en;
        $masterMeetingdata['meeting_note_ar'] = $versionOfMeeting->meeting_note_ar;
        $masterMeetingdata['meeting_note_en'] = $versionOfMeeting->meeting_note_en;
        $masterMeetingdata['meeting_venue_ar'] = $versionOfMeeting->meeting_venue_ar;
        $masterMeetingdata['meeting_venue_en'] = $versionOfMeeting->meeting_venue_en;
        $masterMeetingdata['meeting_schedule_from'] = $versionOfMeeting->meeting_schedule_from;
        $masterMeetingdata['meeting_schedule_to'] = $versionOfMeeting->meeting_schedule_to;
        $masterMeetingdata['location_lat'] = $versionOfMeeting->location_lat;
        $masterMeetingdata['location_long'] = $versionOfMeeting->location_long;
        $masterMeetingdata['meeting_mom_template_id'] = $versionOfMeeting->meeting_mom_template_id;
        $masterMeetingdata['microsoft_teams_video_teleconference_id'] = $versionOfMeeting->microsoft_teams_video_teleconference_id;
        $masterMeetingdata['microsoft_teams_join_web_url'] = $versionOfMeeting->microsoft_teams_join_web_url;
        $masterMeetingdata['microsoft_teams_join_url'] = $versionOfMeeting->microsoft_teams_join_url;
        $masterMeetingdata['zoom_meeting_id'] = $versionOfMeeting->zoom_meeting_id;
        $masterMeetingdata['zoom_meeting_password'] = $versionOfMeeting->zoom_meeting_password;
        $masterMeetingdata['microsoft_teams_meeting_id'] = $versionOfMeeting->microsoft_teams_meeting_id;
        $masterMeetingdata['zoom_join_url'] = $versionOfMeeting->zoom_join_url;
        $masterMeetingdata['zoom_start_url'] = $versionOfMeeting->zoom_start_url;
        $masterMeetingdata['online_configuration_id'] = $versionOfMeeting->online_configuration_id;
        $masterMeetingdata['meeting_attendance_percentage'] = $versionOfMeeting->meeting_attendance_percentage;

        return $masterMeetingdata;
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

    public function prepareOnlineConfigurationOfMeeting($onlineConfig){
        $data = [];

        $data['online_meeting_app_id'] = $onlineConfig->online_meeting_app_id;
        $data['microsoft_azure_app_id'] = $onlineConfig->microsoft_azure_app_id;
        $data['microsoft_azure_tenant_id'] = $onlineConfig->microsoft_azure_tenant_id;
        $data['microsoft_azure_client_secret'] = $onlineConfig->microsoft_azure_client_secret;
        $data['microsoft_azure_user_id'] = $onlineConfig->microsoft_azure_user_id;
        $data['zoom_api_key'] = $onlineConfig->zoom_api_key;
        $data['zoom_api_secret'] = $onlineConfig->zoom_api_secret;
        $data['zoom_exp_minutes'] = $onlineConfig->zoom_exp_minutes;
        $data['zoom_scheduled_meeting_id'] = $onlineConfig->zoom_scheduled_meeting_id;
        $data['zoom_host_video'] = $onlineConfig->zoom_host_video;
        $data['zoom_participant_video'] = $onlineConfig->zoom_participant_video;
        $data['zoom_cn_meeting'] = $onlineConfig->zoom_cn_meeting;
        $data['zoom_in_meeting'] = $onlineConfig->zoom_in_meeting;
        $data['zoom_join_before_host'] = $onlineConfig->zoom_join_before_host;
        $data['zoom_mute_upon_entry'] = $onlineConfig->zoom_mute_upon_entry;
        $data['zoom_water_mark'] = $onlineConfig->zoom_water_mark;
        $data['zoom_audio'] = $onlineConfig->zoom_audio;
        $data['zoom_approval_type'] = $onlineConfig->zoom_approval_type;
        $data['zoom_use_pmi'] = $onlineConfig->zoom_use_pmi;
        $data['zoom_auto_recording'] = $onlineConfig->zoom_auto_recording;
        $data['zoom_meeting_authentication'] = $onlineConfig->zoom_meeting_authentication;
        $data['zoom_enforce_login_domains'] = $onlineConfig->zoom_enforce_login_domains;
        $data['zoom_alternative_hosts'] = $onlineConfig->zoom_alternative_hosts;
        $data['zoom_registrants_email_notification'] = $onlineConfig->zoom_registrants_email_notification;

        return $data;
    }
}