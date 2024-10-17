<?php

namespace Repositories;

class MeetingParticipantAlternativeRepository extends BaseRepository
{


    public function model()
    {
        return 'Models\MeetingParticipantAlternative';
    }


    public function getPagedAbsence($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection)
    {
        $query = $this->getPagedAbsenceQuery($searchObj);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getPagedAbsenceQuery($searchObj)
    {

        if (isset($searchObj->meeting_schedule_from) && isset($searchObj->meeting_schedule_to)) {
            $this->model = $this->model->whereRaw(" NOT (date(meetings.meeting_schedule_from) > ? OR date(meetings.meeting_schedule_to) < ?)", array($searchObj->meeting_schedule_to, $searchObj->meeting_schedule_from));
        } else  if (isset($searchObj->meeting_schedule_from)) {
            $this->model = $this->model->whereDate('meetings.meeting_schedule_from', '>=', $searchObj->meeting_schedule_from);
        } else  if (isset($searchObj->meeting_schedule_to)) {
            $this->model = $this->model->whereDate('meetings.meeting_schedule_to', '<=', $searchObj->meeting_schedule_to);
        }


        if (isset($searchObj->meeting_title)) {
            $this->model = $this->model->whereRaw("(meeting_title_ar like ? OR meeting_title_en like ?)", array('%' . $searchObj->meeting_title . '%', '%' . $searchObj->meeting_title . '%'));
        }

        $q = $this->model->selectRaw('meetings.*,users.*,meeting_participant_alternatives.*')
            ->join('meeting_participants', 'meeting_participants.id', 'meeting_participant_alternatives.meeting_participant_id')
            ->join('users', 'meeting_participants.user_id', 'users.id')
            ->join('meeting_organisers', 'meeting_organisers.meeting_id', 'meeting_participants.meeting_id')
            ->join('meetings', 'meetings.id', 'meeting_participants.meeting_id')
            ->whereNull('meetings.version_number');

        if (isset($searchObj->current_user_id)) {
            $q = $q->where('meeting_organisers.user_id', $searchObj->current_user_id);
        }

        return  $q;
    }
}
