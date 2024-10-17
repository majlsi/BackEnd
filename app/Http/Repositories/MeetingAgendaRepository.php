<?php

namespace Repositories;

class MeetingAgendaRepository extends BaseRepository {


    public function model() {
        return 'Models\MeetingAgenda';
    }

    public function getMeetingAgendasForMeeting($meetingId){
        return $this->model->selectRaw('meeting_agendas.*')
            ->where('meeting_agendas.meeting_id',$meetingId)
            ->with('agendaPresenters')
            ->with('agendaAttachments')
            ->orderBy('agenda_order')
            ->get();
    }

    public function getAgendaForMeeting($meetingId,$meetingAgendaId){
        return $this->model->selectRaw('meeting_agendas.*')
            ->where('meeting_agendas.meeting_id',$meetingId)
            ->where('meeting_agendas.id',$meetingAgendaId)
            ->with('agendaPresenters')
            ->with('participants')
            ->with('presenters')
            ->with('agendaAttachments')
            ->first();
    }
}   