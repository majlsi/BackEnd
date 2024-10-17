<?php

namespace Repositories;

class MeetingParticipantRepository extends BaseRepository {


    public function model() {
        return 'Models\MeetingParticipant';
    }

    public function getMeetingParticipantsForMeeting($meetingId){
        return $this->model->selectRaw('users.*,meeting_participants.meeting_role_id,meeting_participants.meeting_attendance_status_id,
            meeting_participants.is_accept_absent_by_organiser')
            ->leftJoin('users','users.id','meeting_participants.user_id')
            ->where('meeting_participants.meeting_id',$meetingId)
            ->get();
    }

    public function getMeetingParticipant($meetingId,$userId){
        return $this->model->selectRaw('meeting_participants.*,meeting_attendance_statuses.meeting_attendance_status_name_ar,meeting_attendance_statuses.meeting_attendance_status_name_en')
            ->where('meeting_id',$meetingId)
            ->where('user_id',$userId)
            ->leftJoin('meeting_attendance_statuses','meeting_attendance_statuses.id','meeting_participants.meeting_attendance_status_id')
            ->first();
    }

    public function getMeetingParticipantsMayAttand($meetingId){
        return $this->model->selectRaw('users.*,meeting_participants.meeting_role_id')
            ->where('meeting_id',$meetingId)
            ->leftJoin('users','users.id','meeting_participants.user_id')
            ->whereIn('meeting_attendance_status_id',[config('meetingAttendanceStatus.attend'),config('meetingAttendanceStatus.mayAttend')])
            ->get();
    }

    public function resetSign ($meetingId){
        $this->model->where('meeting_id',$meetingId)
            ->update(["is_signature_sent" => 0, "is_signed" => null, "is_signature_sent_individualy" => 0]);

    }

     public function getMeetingUsers($meeting_id){
        return $this->model->selectRaw('users.id')
                ->Join('users','users.id','meeting_participants.user_id')
                ->where('meeting_participants.meeting_id',$meeting_id)
                ->whereNotNull('users.chat_user_id')
                ->orderBy('id','DESC')
                ->get();
    }

}   