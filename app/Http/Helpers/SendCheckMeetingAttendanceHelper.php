<?php

namespace Helpers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;


class SendCheckMeetingAttendanceHelper
{
    

    public function __construct()
    {
    
    }



    public static function prepareData($meeting,$meetingParticipants){
        $notificationData = [];
        $notificationData['meetingTypeNameEn'] = ($meeting->committee->committee_name_en ? $meeting->committee->committee_name_en : $meeting->committee->committee_name_ar);
        $notificationData['meetingTitleEn'] = ($meeting->meeting_title_en ? $meeting->meeting_title_en : $meeting->meeting_title_ar);
      
        $notificationData['meetingCreatorNameEn'] = ($meeting->creator->name ? $meeting->creator->name : $meeting->creator->name_ar);
      
        $notificationData['meetingTypeNameAr'] = $meeting->committee->committee_name_ar;
        $notificationData['meetingCreatorNameAr'] = ($meeting->creator->name_ar ? $meeting->creator->name_ar : $meeting->creator->name);;
        $notificationData['meetingTitleAr'] = $meeting->meeting_title_ar ? $meeting->meeting_title_ar : $meeting->meeting_title_en;
        
        $notificationViewData = $notificationData;
        $notificationViewData['meetingStatusAr'] = 'انتهاء';
        $notificationViewData['meetingStatusEn'] = 'ended';
        $notificationData['notificationTitleEn'] = SendCheckMeetingAttendanceHelper::getNotificationData('notification.NotificationTitleEn', $notificationViewData);
        $notificationData['notificationMessageEn'] = 'Pressing this button will that means you approved this meeting and signed electronically';
        $notificationData['notificationTitleAr'] = SendCheckMeetingAttendanceHelper::getNotificationData('notification.NotificationTitleAr', $notificationViewData);
        $notificationData['notificationMessageAr'] = 'الضغط على هذا الزر يعد موافقة على مجلس الاجتماع والتوقيع الالكترونى عليه';

        $meetingParticipants = $meetingParticipants;
    
        $meetingParticipantIds = array_column($meetingParticipants->toArray(),'id');
        $meetingOrganisers = $meeting->meetingOrganisers;
        $meetingOrganiserIds = array_column($meetingOrganisers->toArray(),'id');
        $notificationData['meetingMemberIds'] = $meetingParticipantIds;
        $notificationData['meetingId'] = $meeting->id;

        return $notificationData;

    } 

    public static function getNotificationData($viewName, $dataArray)
    {
        $notification = view($viewName, $dataArray)->render();
        return $notification;
    }
   
}
