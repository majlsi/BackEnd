<?php

namespace Helpers;

class ZoomConfigurationHelper
{

    public function __construct()
    {
    }

    public function prepareData($data)
    {
        $zoomConfigration = [];

        if(isset($data['zoom_api_key'])) {
            $zoomConfigration['zoom_api_key'] = $data['zoom_api_key'];
        }
        if(isset($data['zoom_api_secret'])) {
            $zoomConfigration['zoom_api_secret'] = $data['zoom_api_secret'];
        }

        $zoomConfigration['zoom_exp_minutes'] = config('zoom.expMinutes');
        $zoomConfigration['zoom_scheduled_meeting_id'] = config('zoom.scheduledMeetingId');
        $zoomConfigration['zoom_cn_meeting'] = config('zoom.cnMeeting');
        $zoomConfigration['zoom_in_meeting'] = config('zoom.inMeeting');
        $zoomConfigration['zoom_enforce_login_domains'] = config('zoom.enforceLoginDomains');
        $zoomConfigration['zoom_alternative_hosts'] = config('zoom.alternativeHosts');
        $zoomConfigration['zoom_host_video'] = config('zoom.hostVideo');
        $zoomConfigration['zoom_participant_video'] = config('zoom.participantVideo');
        $zoomConfigration['zoom_join_before_host'] = config('zoom.joinBeforeHost');
        $zoomConfigration['zoom_mute_upon_entry'] = config('zoom.muteUponEntry');
        $zoomConfigration['zoom_water_mark'] = config('zoom.watermark');
        $zoomConfigration['zoom_use_pmi'] = config('zoom.usePmi');
        $zoomConfigration['zoom_audio'] = config('zoom.audio');
        $zoomConfigration['zoom_approval_type'] = config('zoom.approvalType');
        $zoomConfigration['zoom_auto_recording'] = config('zoom.autoRecording');
        $zoomConfigration['zoom_meeting_authentication'] = config('zoom.meetingAuthentication');
        $zoomConfigration['zoom_registrants_email_notification'] = config('zoom.registrantsEmailNotification');

       return $zoomConfigration;
    }
}
