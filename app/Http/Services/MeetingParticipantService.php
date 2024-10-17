<?php

namespace Services;

use Helpers\MeetingParticipantHelper;
use Repositories\MeetingParticipantRepository;
use Repositories\MeetingParticipantAlternativeRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;
use Repositories\MeetingGuestRepository;

class MeetingParticipantService extends BaseService
{
    private $meetingParticipantAlternativeRepository;
    private MeetingGuestRepository $meetingGuestRepository;
    private MeetingParticipantHelper $meetingParticipantHelper;

    public function __construct(
        DatabaseManager $database,
        MeetingParticipantRepository $repository,
        MeetingParticipantAlternativeRepository $meetingParticipantAlternativeRepository,
        MeetingGuestRepository $meetingGuestRepository,
        MeetingParticipantHelper $meetingParticipantHelper
    )
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->meetingParticipantAlternativeRepository = $meetingParticipantAlternativeRepository;
        $this->meetingGuestRepository = $meetingGuestRepository;
        $this->meetingParticipantHelper = $meetingParticipantHelper;
    }

    public function prepareCreate(array $data)
    {
        return $this->repository->create($data);
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function getMeetingParticipantsForMeeting($meetingId)
    {
        $guests = $this->meetingGuestRepository->getMeetingGuests($meetingId)->toArray();
        $participants = $this->repository->getMeetingParticipantsForMeeting($meetingId);
        return $this->meetingParticipantHelper->prepareMeetingParticipants($participants,$guests);
    }

    public function getMeetingParticipant($meetingId, $userId)
    {
        return $this->repository->getMeetingParticipant($meetingId, $userId);
    }

    public function getMeetingGuest($meetingId, $guestId)
    {
        return $this->meetingGuestRepository->getMeetingGuest($meetingId, $guestId);
    }

    public function changeStatus($meetingParticipantId, $status,$isAcceptAbsentByOrganiser = null)
    {
        return $this->repository->update(["meeting_attendance_status_id" => $status,'is_accept_absent_by_organiser' => $isAcceptAbsentByOrganiser], $meetingParticipantId);
    }

    public function getMeetingParticipantsMayAttand($meetingId)
    {
        return $this->repository->getMeetingParticipantsMayAttand($meetingId);
    }

    public function  addReplacement($replacementId, $absentMeetingPaticipant)
    {
        $replacement = [];
        $replacement['user_id'] = $replacementId;
        $replacement['meeting_role_id'] = $absentMeetingPaticipant['meeting_role_id'];
        $replacement['participant_order'] = $absentMeetingPaticipant['order'];
        return $this->repository->create($replacement);
    }

    public function setMeetingAttendanceStatusForOneParticipant($meetingId,$lastVersionOfMeeting, $userId,$meetingAttendanceStatusId,$success,$reasonData,$isAcceptAbsentByOrganiser){
        $masterMeetingPaticipant = $this->repository->getMeetingParticipant($meetingId, $userId);
        $versionMeetingPaticipant = $this->repository->getMeetingParticipant($lastVersionOfMeeting->id, $userId);
    
        if ($masterMeetingPaticipant) {
            $success = true;
            $this->changeStatus($masterMeetingPaticipant->id, $meetingAttendanceStatusId,$isAcceptAbsentByOrganiser);
            if($reasonData){
                $reason = $this->meetingParticipantAlternativeRepository->findByField('meeting_participant_id', $masterMeetingPaticipant->id)->first();
                if($reason){
                    $this->meetingParticipantAlternativeRepository->update($reasonData,$reason->id);
                } else {
                    $reasonData["meeting_participant_id"] = $masterMeetingPaticipant->id;
                    $this->meetingParticipantAlternativeRepository->create($reasonData);
                }
            } else {
                $reason = $this->meetingParticipantAlternativeRepository->findByField('meeting_participant_id', $masterMeetingPaticipant->id)->first();
                if($reason){
                    $this->meetingParticipantAlternativeRepository->delete($reason->id);
                }
            }
        }
        if ($versionMeetingPaticipant) {
            $success = true;
            $this->changeStatus($versionMeetingPaticipant->id, $meetingAttendanceStatusId,$isAcceptAbsentByOrganiser);
            if($reasonData){
                $reason = $this->meetingParticipantAlternativeRepository->findByField('meeting_participant_id', $versionMeetingPaticipant->id)->first();
                if($reason){
                    $this->meetingParticipantAlternativeRepository->update($reasonData,$reason->id);
                } else {
                    $reasonData["meeting_participant_id"] = $versionMeetingPaticipant->id;
                    $this->meetingParticipantAlternativeRepository->create($reasonData);
                }
            } else {
                $reason = $this->meetingParticipantAlternativeRepository->findByField('meeting_participant_id', $versionMeetingPaticipant->id)->first();
                if($reason){
                    $this->meetingParticipantAlternativeRepository->delete($reason->id);
                }
            }
        }
        return $success;
    }
}
