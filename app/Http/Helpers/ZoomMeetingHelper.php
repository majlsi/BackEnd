<?php

namespace Helpers;

use Carbon\Carbon;

class ZoomMeetingHelper
{

    public function __construct()
    {
    }

    public function prepareZoomMeetingDataAtCreation($meeting,$zoomConfiguration)
    {
        $meetingData = [];

        $dateFrom = Carbon::parse($meeting->meeting_schedule_from);
        $dateTo = Carbon::parse($meeting->meeting_schedule_to);
        $meetingData['topic'] = isset($meeting->meeting_title_ar)? $meeting->meeting_title_ar : $meeting->meeting_title_en;
        $meetingData['type'] = $zoomConfiguration->zoom_scheduled_meeting_id;
        $meetingData['start_time'] = Carbon::parse($meeting->meeting_schedule_from)->subHours($meeting->timeZone->diff_hours)->format('Y-m-d\TH:i:s\Z');
        $meetingData['duration'] = $dateTo->diffInMinutes($dateFrom);
        $meetingData['timezone'] = $meeting->organization->time_zone_code;
        $meetingData['password'] = rand(1000, 9999);
        $meetingData['agenda'] = isset($meeting->meeting_description_ar)? $meeting->meeting_description_ar : $meeting->meeting_description_en;
        $meetingData['settings']['host_video'] = $zoomConfiguration->zoom_host_video;
        $meetingData['settings']['participant_video'] = $zoomConfiguration->zoom_participant_video;
        $meetingData['settings']['cn_meeting'] = $zoomConfiguration->zoom_cn_meeting;
        $meetingData['settings']['in_meeting'] = $zoomConfiguration->zoom_in_meeting;
        $meetingData['settings']['join_before_host'] = $zoomConfiguration->zoom_join_before_host;
        $meetingData['settings']['mute_upon_entry'] = $zoomConfiguration->zoom_mute_upon_entry;
        $meetingData['settings']['watermark'] = $zoomConfiguration->zoom_water_mark;
        $meetingData['settings']['use_pmi'] = $zoomConfiguration->zoom_use_pmi;
        $meetingData['settings']['approval_type'] = $zoomConfiguration->zoom_approval_type;
        $meetingData['settings']['audio'] = $zoomConfiguration->zoom_audio;
        $meetingData['settings']['auto_recording'] = $zoomConfiguration->zoom_auto_recording;
        $meetingData['settings']['meeting_authentication'] = $zoomConfiguration->zoom_meeting_authentication;
        $meetingData['settings']['enforce_login_domains'] = config('zoom.enforceLoginDomains');
        $meetingData['settings']['alternative_hosts'] = $zoomConfiguration->zoom_alternative_hosts;
        $meetingData['settings']['registrants_email_notification'] = $zoomConfiguration->zoom_registrants_email_notification;
        $meetingData['settings']['global_dial_in_countries'] = config('zoom.globalDialInCountries');

        $meetingData['header'] = $this->getHeaderZoomConfigration($zoomConfiguration);

        return $meetingData;
    }

    public function prepareZoomMeetingDataAtUpdate($meeting,$zoomConfiguration)
    {
        $meetingData = [];

        $dateFrom = Carbon::parse($meeting->meeting_schedule_from);
        $dateTo = Carbon::parse($meeting->meeting_schedule_to);

        $meetingData['topic'] = isset($meeting->meeting_title_ar)? $meeting->meeting_title_ar : $meeting->meeting_title_en;
        $meetingData['start_time'] = Carbon::parse($meeting->meeting_schedule_from)->subHours($meeting->timeZone->diff_hours)->format('Y-m-d\TH:i:s\Z');
        $meetingData['duration'] = $dateTo->diffInMinutes($dateFrom);
        $meetingData['agenda'] = isset($meeting->meeting_description_ar)? $meeting->meeting_description_ar : $meeting->meeting_description_en;

        $meetingData['header'] = $this->getHeaderZoomConfigration($zoomConfiguration);

        return $meetingData;
    }

    public function prepareRegisterParticipantMeetingData($user) {
        $participantData = [];

        $participantData['email'] = $user['email'];
        $participantData['first_name'] = isset($user['name_ar'])? $user['name_ar'] : $user['name'];

        $participantData['job_title'] = isset($user['role']['role_name_ar'])? $user['role']['role_name_ar'] : $user['role']['role_name'];
        $participantData['role_in_purchase_process'] = config('zoom.roleInPurchaseProcess');
        $participantData['no_of_employees'] = config('zoom.noOfEmployees');
        $participantData['comments'] = config('zoom.comments');
        $participantData['custom_questions']['title'] = config('zoom.title');
        $participantData['custom_questions']['value'] = config('zoom.value');

        return $participantData;
    }

    public function getHeaderZoomConfigration($zoomConfiguration){
        $headerData = [];

        $headerData['zoom_api_key'] = $zoomConfiguration->zoom_api_key;
        $headerData['zoom_api_secret'] = $zoomConfiguration->zoom_api_secret;
        $headerData['zoom_exp_minutes'] = $zoomConfiguration->zoom_exp_minutes;

        return $headerData;
    }
}