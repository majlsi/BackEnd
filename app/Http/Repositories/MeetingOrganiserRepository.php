<?php

namespace Repositories;

class MeetingOrganiserRepository extends BaseRepository {


    public function model() {
        return 'Models\MeetingOrganiser';
    }

    public function getMeetingOrganisersForMeeting($meetingId){
        return $this->model->selectRaw('users.*')
            ->leftJoin('users','users.id','meeting_organisers.user_id')
            ->where('meeting_organisers.meeting_id',$meetingId)
            ->get();
    }

    public function getMeetingUsers($meetingId){
        return $this->model->selectRaw('users.id')
        ->Join('users','users.id','meeting_organisers.user_id')
        ->where('meeting_organisers.meeting_id',$meetingId)
        ->whereNotNull('users.chat_user_id')
        ->orderBy('id','DESC')
        ->get();
    }
}   