<?php

namespace Helpers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;


class PresentationHelper
{


    public function __construct()
    {
    }


    public static function preparePresentAttachmentData($meeting, $presenter, $attachmentId, $meetingAgendaId, $presentationStatusesId = null, $canAccess = true)
    {
        $presentAttachmentData = [];

        $presentAttachmentData['meetingTypeNameEn'] = ($meeting->committee->committee_name_en ? $meeting->committee->committee_name_en : $meeting->committee->committee_name_ar);
        $presentAttachmentData['meetingTitleEn'] = ($meeting->meeting_title_en ? $meeting->meeting_title_en : $meeting->meeting_title_ar);

        $presentAttachmentData['meetingPresenterNameEn'] = ($presenter->name ? $presenter->name : $presenter->name_ar);

        $presentAttachmentData['meetingTypeNameAr'] = $meeting->committee->committee_name_ar;
        $presentAttachmentData['meetingPresenterNameAr'] = ($presenter->name_ar ? $presenter->name_ar : $presenter->name);
        $presentAttachmentData['meetingTitleAr'] = $meeting->meeting_title_ar ? $meeting->meeting_title_ar : $meeting->meeting_title_en;
        if ($presentationStatusesId) {
            $presentAttachmentData['presentationStatusesId'] = $presentationStatusesId;
            $presentAttachmentViewData = $presentAttachmentData;

            $presentAttachmentData['notificationTitleEn'] = PresentationHelper::getPresentAttachmentData('presentAttachment.PresentAttachmentTitleEn', $presentAttachmentViewData);
            $presentAttachmentData['notificationMessageEn'] = PresentationHelper::getPresentAttachmentData('presentAttachment.PresentAttachmentMessageEn', $presentAttachmentViewData);
            $presentAttachmentData['notificationTitleAr'] = PresentationHelper::getPresentAttachmentData('presentAttachment.PresentAttachmentTitleAr', $presentAttachmentViewData);
            $presentAttachmentData['notificationMessageAr'] = PresentationHelper::getPresentAttachmentData('presentAttachment.PresentAttachmentMessageAr', $presentAttachmentViewData);
        }

        $meetingGuests = $meeting->guests;
        $meetingGuestIds = array_column($meetingGuests->toArray(), 'id');
        $presentAttachmentData['meetingGuestIds'] = $meetingGuestIds;

        $meetingParticipants = $meeting->meetingParticipants;
        $meetingParticipantIds = array_column($meetingParticipants->toArray(), 'id');
        $meetingOrganisers = $meeting->meetingOrganisers;
        $meetingOrganiserIds = array_column($meetingOrganisers->toArray(), 'id');
        $meetingMemberIds = array_merge($meetingParticipantIds, $meetingOrganiserIds);
        $presentAttachmentData['meetingMemberIds'] = $meetingMemberIds;
        $presentAttachmentData['meetingOrganisersIds'] = $meetingOrganiserIds;
        $presentAttachmentData['meetingId'] = $meeting->id;
        $presentAttachmentData['attachmentId'] = $attachmentId;
        $presentAttachmentData['meetingAgendaId'] = $meetingAgendaId;

        $presentAttachmentData['presenterUserId'] = $presenter->id != -1 ? $presenter->id : $presenter->meeting_guest_id;
        $presentAttachmentData['presenterUserRoleId'] = $presenter->role_id;

        $presentAttachmentData["forcePresent"] = false;
        if (in_array($presenter->id, $meetingOrganiserIds) || $presenter->id == $meeting->created_by) {
            $presentAttachmentData["forcePresent"] = true;
        }
        $presentAttachmentData["can_access"] = $canAccess;
        return $presentAttachmentData;
    }

    public static function getPresentAttachmentData($viewName, $dataArray = [])
    {
        $presentationData = view($viewName, $dataArray)->render();
        return $presentationData;
    }
}
