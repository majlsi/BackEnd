<?php

namespace Repositories;

class MeetingGuestRepository extends BaseRepository
{
    /**
     * Determine the model of the repository
     *
     */
    public function model()
    {
        return 'Models\MeetingGuest';
    }

    public function getMeetingGuests($meetingId)
    {
        return $this->model->selectRaw("distinct meeting_guests.*, 
            meeting_attendance_statuses.meeting_attendance_status_name_ar, meeting_attendance_statuses.meeting_attendance_status_name_en,
            meeting_attendance_statuses.meeting_attendance_action_name_ar, meeting_attendance_statuses.meeting_attendance_action_name_en,
            meeting_attendance_statuses.icon_class_name, meeting_attendance_statuses.color_class_name")
            ->join("meetings", function ($join) {
                $join->on('meetings.id', '=',  'meeting_guests.meeting_id')
                    ->orOn('meetings.related_meeting_id', '=',  'meeting_guests.meeting_id');
            })
            ->leftJoin("meeting_attendance_statuses", function ($join) {
                $join->on('meeting_attendance_statuses.id', '=',  'meeting_guests.meeting_attendance_status_id');
            })
            ->where("meetings.id", $meetingId)
            ->orWhere("meetings.related_meeting_id", $meetingId)
            ->whereNull("meeting_guests.deleted_at")
            ->get();
    }

    public function getMeetingGuest($meetingId, $guestId)
    {
        return $this->model->selectRaw('meeting_guests.*,meeting_attendance_statuses.meeting_attendance_status_name_ar,meeting_attendance_statuses.meeting_attendance_status_name_en')
        ->where('meeting_id', $meetingId)
            ->where('meeting_guests.id', $guestId)
            ->leftJoin('meeting_attendance_statuses', 'meeting_attendance_statuses.id', 'meeting_guests.meeting_attendance_status_id')
            ->first();
    }

    public function GetGuestByMeetingIdAndEmail($meetingId, $email)
    {
        return $this->model->where("meeting_guests.meeting_id", $meetingId)
            ->Where("meeting_guests.email", $email)
            ->first();
    }

    public function getByChatGuestId($chatUserId)
    {
        return $this->model->where('meeting_guests.chat_user_id', $chatUserId)
            ->first();
    }
}
