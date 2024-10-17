<?php

namespace Helpers;


class MeetingGuestHelper
{


    public function __construct()
    {
    }

    public static function prepareGuestsDataOnUpsert($data, $meetingId, $roleId, $organizationId)
    {
        foreach ($data as &$guest) {
            $guest["can_sign"] = false;
            $guest["meeting_role_id"] = $roleId;
            $guest["meeting_id"] = $meetingId;
            $guest["organization_id"] = $organizationId;
        }
        return $data;
    }

    public function mapGuestsList($guests)
    {
        foreach ($guests as &$guest) {
            $guest['meeting_guest_id'] = $guest['id'];
            unset($guest['id']);
        }
        return $guests;
    }

    public function prepareGuestsStats($meetingData)
    {
        if(isset($meetingData["guests"]) && count($meetingData["guests"]) > 0){
            $meetingData->totalParticipants += count($meetingData["guests"]);
            foreach ($meetingData["guests"] as $guest) {
                switch($guest["meeting_attendance_status_id"]){
                    case config("meetingAttendanceStatus.absent"):
                        $meetingData->absent++;
                        if(isset($guest["is_accept_absent_by_organiser"]) && $guest["is_accept_absent_by_organiser"] == true){
                            $meetingData->accept_absent++;
                        } else {
                            $meetingData->absent_without_accepted++;
                        }
                        break;
                    case config("meetingAttendanceStatus.mayAttend"):
                        $meetingData->mayAttend++;
                        break;
                    case config("meetingAttendanceStatus.attend"):
                        $meetingData->attend++;
                        break;
                    default:
                        $meetingData->noRespond++;
                        break;
                }
            }
        }
        return $meetingData;
    }
}
