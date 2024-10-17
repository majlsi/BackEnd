<?php

    return [
        'apiBaseURL' => env('ZOOM_API_BASE_URL','https://api.zoom.us/v2/'),
        'expMinutes' => env('ZOOM_EXP_MINUTES',90),
        'success_code' => env('ZOOM_SUCCESS_CODE',200),
        'success_created_code' => env('ZOOM_SUCCESS_CREATED_CODE',201),
        'success_updated_code' => env('ZOOM_SUCCESS_UPDATED_CODE',204),
        'userId' => env('ZOOM_USER_ID','nnEanr5YRHKSCKBgvUaFqQ'),
        'scheduledMeetingId' => env('ZOOM_SCHEDULED_MEETING_ID',2),
        'hostVideo' => env('ZOOM_HOST_VIDEO',true),
        'participantVideo' => env('ZOOM_PARTICIPANT_VIDEO',true),
        'cnMeeting' => env('ZOOM_CN_MEETING',false),
        'inMeeting' => env('ZOOM_IN_MEETING',false),
        'joinBeforeHost' => env('ZOOM_JOIN_BEFORE_HOST',true),
        'muteUponEntry' => env('ZOOM_MUTE_UPON_ENTRY',false),
        'watermark' => env('ZOOM_WATER_MARK',false),
        'usePmi' => env('ZOOM_USE_PMI',false),
        'audio' => env('ZOOM_AUDIO','both'),
        'registrationType' => env('ZOOM_REGISTRATION_TYPE',1),
        'approvalType' => env('ZOOM_APPROVAL_TYPE',0),
        'autoRecording' => env('ZOOM_AUTO_RECORDING','none'),
        'meetingAuthentication' => env('ZOOM_ENFORCE_LOGIN',false),
        'enforceLoginDomains' => env('ZOOM_ENFORCE_LOGIN_DOMAINS',''),
        'alternativeHosts' => env('ZOOM_ALTERNATIVE_HOSTS',''),
        'registrantsEmailNotification' => env('ZOOM_REGISTRANTS_EMAIL_NOTIFICATION',true),
        'globalDialInCountries' => env('ZOOM_GLOBAL_DIAL_IN_COUNTRIES',['']),
        'roleInPurchaseProcess' => env('ZOOM_ROLE_IN_PURCHASE_PROCESS','Influencer'),
        'noOfEmployees' => env('ZOOM_NO_OF_EMPLOYEES','1-20'),
        'comments' => env('ZOOM_COMMENTS',''),
        'title' => env('ZOOM_TITLE',''),
        'value' => env('ZOOM_VALUE',''),
        'endMeetingAction' => env('ZOOM_END_MEETING_ACTION','end'),
    ];
