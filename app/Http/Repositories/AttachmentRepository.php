<?php

namespace Repositories;

class AttachmentRepository extends BaseRepository {


    public function model() {
        return 'Models\Attachment';
    }

    public function getAttachmentsForMeeting($meetingId){
        return $this->model->selectRaw('attachments.*')
            ->leftJoin('meetings','meetings.id','attachments.meeting_id')
            ->where('attachments.meeting_id',$meetingId)
            ->get();
    }

    public function getMeetingPresentationAttachment($meetingId){
        return $this->model->selectRaw('attachments.*')
        ->join('meeting_agendas','meeting_agendas.id','attachments.meeting_agenda_id')
        ->where('meeting_agendas.meeting_id',$meetingId)
        ->whereNotNull('attachments.presenter_id')
        ->first();
    }
}   