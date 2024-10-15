<?php

namespace Services;

use Carbon\Carbon;
use Exception;
use Helpers\EventHelper;
use Helpers\MeetingGuestHelper;
use Helpers\MeetingStatusHistoryHelper;
use Helpers\NotificationHelper;
use Helpers\PresentationHelper;
use Helpers\MeetingVersionHelper;
use Helpers\StorageHelper;
use Helpers\SecurityHelper;
use Helpers\UploadHelper;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\File;
use Repositories\MeetingAgendaRepository;
use Repositories\MeetingParticipantAlternativeRepository;
use Repositories\MeetingParticipantRepository;
use Repositories\RoleRepository;
use Repositories\MeetingRepository;
use Repositories\MeetingStatusHistoryRepository;
use Repositories\MeetingStatusRepository;
use Repositories\VoteRepository;
use Repositories\AttachmentRepository;
use Repositories\MeetingOnlineConfigurationRepository;
use Repositories\CommitteeUserRepository;
use Repositories\FileRepository;
use Repositories\DirectoryRepository;
use Repositories\VoteParticipantRepository;
use stdClass;
use \Illuminate\Database\Eloquent\Model;
use Jobs\HandleAttachments;
use Jobs\SystemNotification;
use Repositories\ApprovalRepository;
use Repositories\MeetingGuestRepository;
use Repositories\MeetingRecommendationRepository;
use Illuminate\Support\Facades\Blade;

class MeetingService extends BaseService
{

    private $meetingStatusHistoryHelper;
    private $meetingStatusHistoryRepository;
    private $eventHelper;
    private $notificationHelper;
    private $meetingStatusRepository;
    private $presentationHelper;
    private $voteRepository;
    private $meetingAgendaRepository;
    private $meetingParticipantAlternativeRepository;
    private $meetingParticipantRepository;
    private $meetingVersionHelper;
    private $attachmentRepository;
    private $meetingOnlineConfigurationRepository;
    private $committeeUserRepository;
    private $roleRepository;
    private $storageHelper;
    private $fileRepository;
    private $directoryRepository;
    private VoteParticipantRepository $voteParticipantRepository;
    private MeetingGuestRepository $meetingGuestRepository;
    private MeetingGuestHelper $meetingGuestHelper;
    private ApprovalRepository $approvalRepository;
    private SecurityHelper $securityHelper;
    private MeetingRecommendationRepository $meetingRecommendationRepository;

    public function __construct(DatabaseManager $database, MeetingRepository $repository,
        MeetingStatusHistoryHelper $meetingStatusHistoryHelper,
        MeetingStatusHistoryRepository $meetingStatusHistoryRepository,
        EventHelper $eventHelper,
        NotificationHelper $notificationHelper,
        MeetingStatusRepository $meetingStatusRepository,
        PresentationHelper $presentationHelper,
        VoteRepository $voteRepository,
        MeetingAgendaRepository $meetingAgendaRepository,
        MeetingParticipantRepository $meetingParticipantRepository,
        MeetingParticipantAlternativeRepository $meetingParticipantAlternativeRepository,
        MeetingVersionHelper $meetingVersionHelper,
        AttachmentRepository $attachmentRepository,
        MeetingOnlineConfigurationRepository $meetingOnlineConfigurationRepository,
        CommitteeUserRepository $committeeUserRepository,
        RoleRepository $roleRepository,
        StorageHelper $storageHelper,
        FileRepository $fileRepository,
        DirectoryRepository $directoryRepository,
        MeetingGuestRepository $meetingGuestRepository,
        MeetingGuestHelper $meetingGuestHelper,
        VoteParticipantRepository $voteParticipantRepository,
        ApprovalRepository $approvalRepository,
        SecurityHelper $securityHelper,
        MeetingRecommendationRepository $meetingRecommendationRepository,
    ) {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->meetingStatusHistoryHelper = $meetingStatusHistoryHelper;
        $this->meetingStatusHistoryRepository = $meetingStatusHistoryRepository;
        $this->eventHelper = $eventHelper;
        $this->notificationHelper = $notificationHelper;
        $this->meetingStatusRepository = $meetingStatusRepository;
        $this->presentationHelper = $presentationHelper;
        $this->voteRepository = $voteRepository;
        $this->meetingAgendaRepository = $meetingAgendaRepository;
        $this->meetingParticipantAlternativeRepository = $meetingParticipantAlternativeRepository;
        $this->meetingParticipantRepository = $meetingParticipantRepository;
        $this->meetingVersionHelper = $meetingVersionHelper;
        $this->attachmentRepository = $attachmentRepository;
        $this->meetingOnlineConfigurationRepository = $meetingOnlineConfigurationRepository;
        $this->committeeUserRepository = $committeeUserRepository;
        $this->roleRepository = $roleRepository;
        $this->fileRepository = $fileRepository;
        $this->storageHelper = $storageHelper;
        $this->directoryRepository = $directoryRepository;
        $this->meetingGuestRepository = $meetingGuestRepository;
        $this->meetingGuestHelper = $meetingGuestHelper;
        $this->voteParticipantRepository = $voteParticipantRepository;
        $this->approvalRepository = $approvalRepository;
        $this->securityHelper = $securityHelper;
        $this->meetingRecommendationRepository = $meetingRecommendationRepository;
    }

    public function prepareCreate(array $data)
    {
        $meetingData = $data['meeting'];
        $meetingRemindersData = $data['meeting_reminders'];

        // create meeting
        $meeting = $this->craeteMeeting($meetingData,$meetingRemindersData);
        $meeting = $this->getById($meeting->id);

        $statusLogData = $this->meetingStatusHistoryHelper->prepareLogData($meetingData["meeting_status_id"], $meeting->id);
        $this->meetingStatusHistoryRepository->create($statusLogData);
        // create version of this meeting
        $versionOfMeeting = $this->meetingVersionHelper->prepareVersionOfMeetingData($meetingData,$meeting,1,true);
        $this->craeteMeeting($versionOfMeeting,$meetingRemindersData);

        return $meeting;
    }

    public function prepareUpdate(Model $model, array $data)
    {

        $meeting = $this->getById($model->id);
        //update meeting
        $meetingData = $this->meetingVersionHelper->prepareMasterMeetingDataAtUpdate($meeting,$data);
        if(count($meetingData) > 0){
            $this->updateMeeting($meeting,$meetingData);
            if(isset($meetingData['meeting_status_id']) && $meetingData['meeting_status_id'] != $meeting->meeting_status_id && in_array($meetingData['meeting_status_id'],[config('meetingStatus.publish'),config('meetingStatus.publishAgenda'),config('meetingStatus.start'),config('meetingStatus.end')])){
                $this->publishMeetingVersion($meeting->id);
            }
        }
        if(!isset($meetingData['meeting_status_id']) || (isset($meetingData['meeting_status_id']) && $meetingData['meeting_status_id'] == $meeting->meeting_status_id)){
            // update meeting un-published version
            $versionOfMeeting = $this->repository->getUnpublishedVersionOfMeeting($meeting->id);
            if($versionOfMeeting){
                $versionOfMeetingData = $this->meetingVersionHelper->prepareVersionOfMeetingData($data,$meeting,$versionOfMeeting->version_number,false);
                $this->updateMeeting($versionOfMeeting,$versionOfMeetingData);
            } else {
                $meeting = $this->getById($model->id);
                // create version of this meeting
                $lastVersionOfMeeting = $this->repository->getLastVersionOfMeeting($meeting->id);
                $versionOfMeeting = $this->createVersionOfMeetingFromMasterMeeting($meeting,$lastVersionOfMeeting);
                $versionOfMeetingData = $this->meetingVersionHelper->prepareVersionOfMeetingData($data,$meeting,$versionOfMeeting->version_number,false);
                $this->updateMeeting($versionOfMeeting,$versionOfMeetingData);
            }
        } else {
            $versionOfMeeting = $this->repository->getLastVersionOfMeeting($meeting->id);
            $versionOfMeetingData = $this->meetingVersionHelper->prepareVersionOfMeetingData($data,$meeting,$versionOfMeeting->version_number,false);
            $this->updateMeeting($versionOfMeeting,$versionOfMeetingData);
        }
    }

    public function addCommitteeUsersAsParticipants($meeting)
    {
        $participantRoleId = $this->roleRepository->getRoleByCode(config('roleCodes.participant'))->id;
        $participants = $this->committeeUserRepository->getCommitteeUsersWhosActiveNow($meeting->meetingCommittee->id);
        $participantsIds = [];
        $stakeholderRoleId = $this->roleRepository->getRoleByCode(config('roleCodes.stakeholder'))->id;

        foreach ($participants as $key => $participant) {
            $participantsIds[$key]['user_id'] = $participant['user_id'];
            $roleId = $participant->user->role_id != config('roles.organizationAdmin') ? $participant->user->role_id : $participantRoleId;
            $participantsIds[$key]['meeting_role_id'] = $roleId;
            $participantsIds[$key]['participant_order'] = $key + 1;
            $participantsIds[$key]['can_sign'] = $roleId != $stakeholderRoleId;
            $participantsIds[$key]['send_mom'] = true;
        }

        $this->createMeetingParticipants($meeting->id, $participantsIds);

    }

    public function addCommitteOrganiserAsMeetingOrganiser($meeting)
    {
        $committeOrganiser = $meeting->meetingCommittee->committeeOrganiser;
        if ($committeOrganiser) {
            $meeting->organisers()->create(["user_id" => $committeOrganiser->id]);
        }
    }

    public function prepareDelete(int $id)
    {
        $meeting = $this->getById($id);
        $meeting->reminders()->delete();
        $meeting->organisers()->delete();
        $meeting->participants()->delete();
        $this->repository->delete($id);
    }

    public function getMeetingDetails(
        $id,
        $user = null,
        $allowedGuestsVoteParticipants = null,
        $allowedUsersVoteParticipants = null
    )
    {
        $guest = null;
        if (isset($user->meeting_id)) {
            $guest = $this->meetingGuestRepository->GetGuestByMeetingIdAndEmail($user->meeting_id, $user->email);
        }
        $meetingData = $this->repository->getMeetingDetails(
            $id,
            $allowedGuestsVoteParticipants,
            $allowedUsersVoteParticipants
        );
        $meeting = $meetingData->toArray();
        $remindersIds = array_column($meeting['meeting_reminders'], 'reminder_id');
        $meeting['meeting_reminders'] = $remindersIds;
        $meeting['meeting_schedule_to'] = Carbon::parse($meeting['meeting_schedule_to']);
        $meeting['meeting_schedule_from'] = Carbon::parse($meeting['meeting_schedule_from']);

        setlocale(LC_ALL, 'ar_AE.utf8');
        $meeting["meeting_month_ar"] = Carbon::parse($meeting['meeting_schedule_from'])->formatLocalized('%B');
        $meeting["meeting_day_name_ar"] = Carbon::parse($meeting['meeting_schedule_from'])->formatLocalized('%A');
        setlocale(LC_ALL, 'en_EN.utf8');

        $meeting["meeting_schedule_from_date"] = json_decode($meeting['meeting_schedule_from_date']);
        $meeting["meeting_schedule_to_date"] = json_decode($meeting['meeting_schedule_to_date']);

        $meeting["meeting_schedule_from_time"] = json_decode($meeting['meeting_schedule_from_time']);
        $meeting["meeting_schedule_to_time"] = json_decode($meeting['meeting_schedule_to_time']);

        foreach ($meeting['meeting_agendas'] as $key => &$meetingAgenda) {
            $meeting['meeting_agendas'][$key]['agenda_presenters'] = array_column($meeting['meeting_agendas'][$key]['agenda_presenters'], 'id');
            $meetingAgenda['can_access'] = true;
            if(isset($guest->id)){
                $meetingAgenda['can_access'] = in_array($guest->id, array_column($meetingAgenda["participants"], 'meeting_guest_id'));
            } else if(isset($user->id)){
                $meetingAgenda['can_access'] = in_array($user->id, array_column($meetingAgenda["participants"], 'user_id'));
            }
        }

        foreach ($meeting['meeting_votes'] as $key => $meetingVote) {
            $meeting['meeting_votes'][$key]['vote_schedule_from_date'] = json_decode($meeting['meeting_votes'][$key]['vote_schedule_from_date']);
            $meeting['meeting_votes'][$key]['vote_schedule_to_date'] = json_decode($meeting['meeting_votes'][$key]['vote_schedule_to_date']);
            $meeting['meeting_votes'][$key]['vote_schedule_from_time'] = json_decode($meeting['meeting_votes'][$key]['vote_schedule_from_time']);
            $meeting['meeting_votes'][$key]['vote_schedule_to_time'] = json_decode($meeting['meeting_votes'][$key]['vote_schedule_to_time']);
            $meeting['meeting_votes'][$key]['decision_due_date'] = json_decode($meeting['meeting_votes'][$key]['decision_due_date']);
        }
        $organizersIds = array_column($meetingData->organisers->toArray(), 'user_id');
        $meeting['can_edit_meeting'] = (in_array($user?->id, $organizersIds) || $user?->id == $meeting["created_by"]) && !in_array($meetingData->meeting_status_id, [config('meetingStatus.cancel'), config('meetingStatus.end'), config('meetingStatus.sendRecommendation')]);
        $meeting['can_edit_recommendation_meeting'] = (in_array($user?->id, $organizersIds) || $user?->id == $meeting["created_by"]) && !in_array($meetingData->meeting_status_id, [config('meetingStatus.cancel'), config('meetingStatus.sendRecommendation')]);
        $meeting['show_send_sign_button'] = (in_array($user?->id, $organizersIds) || $user?->id == $meeting["created_by"]) && $meetingData->meeting_status_id == config('meetingStatus.sendRecommendation') && $meetingData->is_mom_sent;
        $meeting['show_attendance_percentage_warning'] = $meeting['meeting_attendance_percentage'] && (($meeting['attend'] /$meeting['totalParticipants'] *100) < $meeting['meeting_attendance_percentage'])? true : false;
        $meeting['guests'] = $this->meetingGuestHelper->mapGuestsList($this->meetingGuestRepository->getMeetingGuests($id));
        $meeting['meeting_moms'] = [];
        $approvals = $this->approvalRepository->getMeetingApprovals($meeting['id']);
        foreach ($approvals as $key => $approval) {
            $approval["created_by_obj"] = $approval->approvalSender;
        }
        $meeting['approvals'] = $approvals;
        $meeting['meetingRecommendations'] = $this->meetingRecommendationRepository->getMeetingRecommendationsForMeeting($meeting['id']);

        return $meeting;
    }

    public function getPagedList($filter, $organizationId, $userRoleCode, $userId)
    {
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "id";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "ASC";
        }
        return $this->repository->getPagedMeetings($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $organizationId, $userRoleCode, $userId);
    }

    public function createOrganisersForMeetingVersion($meetingId, $organisersIds){
        // update master meeting
        $masterMeeting = $this->getById($meetingId);
        if($masterMeeting->meeting_status_id == config('meetingStatus.draft')){
            $this->createMeetingOrganisers($meetingId, $organisersIds);
        }
        // update version of meeting
        $versionOfMeeting = $this->repository->getUnpublishedVersionOfMeeting($meetingId);
        if($versionOfMeeting){
            return $this->createMeetingOrganisers($versionOfMeeting->id, $organisersIds);
        } else {
            $masterMeeting = $this->getById($meetingId);
            // create version of this meeting
            $lastVersionOfMeeting = $this->repository->getLastVersionOfMeeting($meetingId);
            $versionOfMeeting = $this->createVersionOfMeetingFromMasterMeeting($masterMeeting,$lastVersionOfMeeting);
            // update version of meeting
            return $this->createMeetingOrganisers($versionOfMeeting->id, $organisersIds);
        }
    }

    public function createMeetingOrganisers($meetingId, $organisersIds)
    {
        $meeting = $this->getById($meetingId);
        $meeting->organisers()->delete();
        return $meeting->organisers()->createMany($organisersIds);
    }

    public function createParticipantsForMeetingVersion($meetingId, $participantsIds){
        // update master meeting
        $masterMeeting = $this->getById($meetingId);
        if($masterMeeting->meeting_status_id == config('meetingStatus.draft')){
            $this->createMeetingParticipants($meetingId, $participantsIds);
        }
        // update version of meeting
        $versionOfMeeting = $this->repository->getUnpublishedVersionOfMeeting($meetingId);
        if($versionOfMeeting){
            return $this->createMeetingParticipants($versionOfMeeting->id, $participantsIds);
        } else {
            $masterMeeting = $this->getById($meetingId);
            // create version of this meeting
            $lastVersionOfMeeting = $this->repository->getLastVersionOfMeeting($meetingId);
            $versionOfMeeting = $this->createVersionOfMeetingFromMasterMeeting($masterMeeting,$lastVersionOfMeeting);
            // update version of meeting
            return $this->createMeetingParticipants($versionOfMeeting->id, $participantsIds);
        }
    }

    public function createMeetingParticipants($meetingId, $participantsIds)
    {
        $meeting = $this->getById($meetingId);
        if (count($meeting->participants) == 0) {
            return $meeting->participants()->createMany($participantsIds);
        }
        $currentParticipantsIds = array_column($meeting->participants->toArray(), 'user_id');
        $incomingParticipantData = array_column($participantsIds, 'user_id');

        //Update Existing participant
        $existingParticipantsIds = array_values(array_intersect($incomingParticipantData, $currentParticipantsIds));

        foreach ($participantsIds as $participant) {
            $participantData = $meeting->participants()->where('user_id', $participant["user_id"])->get();
            if (count($participantData) > 0) {
                $this->meetingParticipantRepository->update($participant, $participantData[0]["id"]);
            }
        }

        //Add new participants
        $diffToBeAdded = array_values(array_diff($incomingParticipantData, $currentParticipantsIds));
        $addedParticipants = array_filter($participantsIds, function ($participant) use ($diffToBeAdded) {
            if (in_array($participant['user_id'], $diffToBeAdded)) {
                return $participant;
            }
        });
        $meeting->participants()->createMany(array_values($addedParticipants));

        //Delete removed participants
        $diffToBeDeleted = array_values(array_diff($currentParticipantsIds, $incomingParticipantData));
        $absentParticipants = array_values(array_filter($meeting->participants->toArray(), function ($participant) use ($diffToBeDeleted) {
            if (in_array($participant['user_id'], $diffToBeDeleted) && ($participant['meeting_attendance_status_id'] == config('meetingAttendanceStatus.absent'))) {
                return $participant;
            }
        }));

        //delete ebsent participants reason
        if (count($absentParticipants) > 0) {
            foreach ($absentParticipants as $absentParticipant) {
                $reason = $this->meetingParticipantAlternativeRepository->findByField('meeting_participant_id', $absentParticipant["id"])->first();
                if($reason){
                    $this->meetingParticipantAlternativeRepository->delete($reason->id);
                }
            }
        }

        // delete vote results if master meeting
        if(!$meeting->version_number){
            foreach ($meeting->meetingVotes as $key => $meetingVote) {
                $meetingVote->voteResults()->whereIn('user_id', $diffToBeDeleted)->delete();
            }
        }
        $meeting->participants()->whereIn('user_id', $diffToBeDeleted)->delete();

    }

    public function createAttachmentsForMeetingVersion($meetingId, $attachmentsData){
        // update master meeting
        $masterMeeting = $this->getById($meetingId);
        // if($masterMeeting->meeting_status_id == config('meetingStatus.draft')){
        //     $this->createAttachments($meetingId, $attachmentsData);
        // }
        // update version of meeting
        $versionOfMeeting = $this->repository->getUnpublishedVersionOfMeeting($meetingId);
        if($versionOfMeeting){
            return $this->createAttachments($versionOfMeeting->id, $attachmentsData);
        } else {
            $masterMeeting = $this->getById($meetingId);
            // create version of this meeting
            $lastVersionOfMeeting = $this->repository->getLastVersionOfMeeting($meetingId);
            $versionOfMeeting = $this->createVersionOfMeetingFromMasterMeeting($masterMeeting,$lastVersionOfMeeting);
            // update version of meeting
            return $this->createAttachments($versionOfMeeting->id, $attachmentsData);
        }
    }

    public function createAttachments($meetingId, $attachmentsData)
    {
        $meeting = $this->getById($meetingId);
        $meetingAttachments = $meeting->meetingAttachments;
        $fileIds = [];
        foreach ($meetingAttachments as $meetingAttachment) {
            $path = public_path() . '/uploads/attachments/' . $meetingAttachment->id;
            if (File::isDirectory($path)) {
                // File::makeDirectory($path, 0777, true, true);

                File::deleteDirectory($path);

            }
            if($meetingAttachment->file_id){
                $fileIds[] = $meetingAttachment->file_id;
            }
        }
        foreach($attachmentsData as $index => $attachment){
            $storageFile =  $this->storageHelper->mapSystemFile($attachment['attachment_name'],$attachment['attachment_url'],$index ,$meeting->creator);
            $attachmentFile = $this->fileRepository->create($storageFile);
            $attachmentsData[$index]['file_id']  =  $attachmentFile->id;
        }
        $meeting->meetingAttachments()->delete();
        $this->fileRepository->deleteFiles($fileIds);
        $newMeetingAttachments = $meeting->meetingAttachments()->createMany($attachmentsData);
        // UploadHelper::convertAttachmentsToImages($newMeetingAttachments);
        return $newMeetingAttachments;

    }

    public function getMeetingCommitteeUsers($meetingId, $name)
    {
        return $this->repository->getMeetingCommitteeUsers($meetingId, $name);
    }

    public function getMeetingRemindersForEmail()
    {
        return $this->repository->getMeetingRemindersForEmail();
    }

    public function getLastMeetingSequenceForOrganization($organizationId)
    {
        return $this->repository->getLastMeetingSequenceForOrganization($organizationId);
    }

    public function getCurrentPreviousList($filter, $organizationId, $userId, $dashboardTab)
    {
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "created_at";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        return $this->repository->getCurrentPreviousList($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $organizationId, $userId, $dashboardTab);
    }

    public function checkScheduleConflict($participantIds, $meetingId, $startData, $endDate)
    {
        return $this->repository->checkScheduleConflict($participantIds, $meetingId, $startData, $endDate);
    }

    public function getMeetingsForUserByMonth($userId, $month, $year, $organizationId)
    {
        $numOfDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $start = "$year-$month-01";
        $end = "$year-$month-$numOfDays";
        return $this->repository->getMeetingsForUserByMonth($userId, $start, $end, $organizationId);
    }
    public function checkCommitteChange($meetingId, $newCommitteeId)
    {
        $meeting = $this->getById($meetingId);
        if (count($meeting->participants->toArray()) == 0) {
            return true;
        } else if (count($meeting->participants->toArray()) > 0 && ($meeting->committee_id != $newCommitteeId)) {
            return false;
        } else {
            return true;
        }

    }

    public function getMeetingAllData($meetingId, $user, $attachmentId = null)
    {
        $guest = null;
        if (isset($user->meeting_id)) {
            $guest = $this->meetingGuestRepository->GetGuestByMeetingIdAndEmail($user->meeting_id, $user->email);;
        }
        setlocale(LC_ALL, 'ar_AE.utf8');
        $allowedGuestsVoteParticipants = null;
        $allowedUsersVoteParticipants = null;
        if ($user->id == -1) {
            $allowedGuestsVoteParticipants = $user->meeting_guest_id;
        } else {
            $allowedUsersVoteParticipants = $user->id;
        }
        $meetingAllData = $this->repository->getMeetingAllData($meetingId, $user->id, $allowedGuestsVoteParticipants, $allowedUsersVoteParticipants);
        $meetingAllData->meetingAgendas = $this->prepareAgendaMinsAsAgendaComments($meetingAllData['id'],$meetingAllData->meetingAgendas);
        $meetingAllData["meeting_month_ar"] = Carbon::parse($meetingAllData['meeting_schedule_from'])->formatLocalized('%B');
        $meetingAllData["meeting_day_name_ar"] = Carbon::parse($meetingAllData['meeting_schedule_from'])->formatLocalized('%A');

        $meetingAllData["is_started"] = $meetingAllData["meeting_status_id"] == config('meetingStatus.start');
        $meetingAllData["show_mom"] = $meetingAllData["meeting_status_id"] == config('meetingStatus.end') && $meetingAllData['is_mom_sent'];
        $meetingAllData["grouped_meeting_participants"] = $meetingAllData["meetingParticipants"]->whereIn('meeting_attendance_status_id', [config('meetingAttendanceStatus.attend'), config('meetingAttendanceStatus.absent')])->groupBy('meeting_attendance_status_id');

        $meetingAllData["current_agenda"] = $meetingAllData["meetingAgendas"]->filter(function ($value) use ($attachmentId) {
            return in_array($attachmentId, array_column($value["agendaAttachments"]->toArray(), "id"));
        })->first();
        $meetingAgendaOrganisersIds = array_column($meetingAllData->meetingOrganisers->toArray(), 'id');
        $meetingAllData["can_change_meeting_status"] = in_array($user->id, $meetingAgendaOrganisersIds) || $user->id == $meetingAllData["created_by"];
        $meetingAllData["can_view_meeting_statistic"] = in_array($user->id, $meetingAgendaOrganisersIds) || $user->id == $meetingAllData["created_by"];
        $meetingAllData["can_change_attendance"] = in_array($user->id, $meetingAgendaOrganisersIds) || $user->id == $meetingAllData["created_by"];
        foreach ($meetingAllData->meetingAgendas as $key => &$meetingAgenda) {
            $meetingAgendaPresenterIds = array_column($meetingAgenda->presentersAgenda->toArray(), 'id');
            if (in_array($user->id, $meetingAgendaPresenterIds) || (in_array($user->id, $meetingAgendaOrganisersIds)) || $user->id == $meetingAllData->created_by) {
                $meetingAgenda->agendaAttachments = $meetingAgenda->agendaAttachments->map(function ($item, $key) {
                    if ($item->presenter_id) {
                        $item->can_end = true;
                    } else {
                        $item->can_end = false;
                    }
                    return $item->can_present = true;
                });
            } else if (isset($user->meeting_guest_id) && in_array($user->meeting_guest_id, array_column($meetingAgenda->presenters->toArray(), 'meeting_guest_id'))) {
                $meetingAgenda->agendaAttachments = $meetingAgenda->agendaAttachments->map(function ($item, $key) {
                    return $item->can_present = true;
                });
            } else {
                $meetingAgenda->agendaAttachments = $meetingAgenda->agendaAttachments->map(function ($item, $key) {
                    return $item->can_present = false;
                });

            }

            if ((in_array($user->id, $meetingAgendaOrganisersIds) || $user->id == $meetingAllData->created_by) && !in_array($meetingAllData["meeting_status_id"], [config('meetingStatus.cancel'), config('meetingStatus.end')])) {
                $meetingAllData["can_start_vote"] = true;
                $meetingAllData["can_add_vote"] = true;
            } else {
                $meetingAllData["can_start_vote"] = false;
                $meetingAllData["can_add_vote"] = false;
            }

            $meetingAgenda['can_access'] = true;
            if (isset($guest->id)) {
                $meetingAgenda['can_access'] = in_array($guest->id, array_column($meetingAgenda->toArray()["participants"], 'meeting_guest_id'));
            } else if (isset($user->id)) {
                $meetingAgenda['can_access'] = in_array($user->id, array_column($meetingAgenda->toArray()["participants"], 'user_id'));
            }
        }
        $meetingAllData['attendPercentage'] =ceil(($meetingAllData['attend'] /$meetingAllData['totalParticipants']) *100) .'%';
        $meetingAllData['show_attendance_percentage_warning'] = $meetingAllData['meeting_attendance_percentage'] && (($meetingAllData['attend'] /$meetingAllData['totalParticipants'] *100) < $meetingAllData['meeting_attendance_percentage'])? true : false;
        $meetingAllData['guests'] = $this->meetingGuestHelper->mapGuestsList($this->meetingGuestRepository->getMeetingGuests($meetingId));
        $meetingAllData['participantStatistics']= $this->prepareParticipantStatistics($meetingAllData);
        $meetingAllData['meeting_moms'] = [];

        $meetingAllData['approvals'] = $this->approvalRepository->getMeetingApprovals($meetingId);
        foreach ($meetingAllData['approvals'] as $key => $approval) {
            if (!(in_array($user->id, $meetingAgendaOrganisersIds)
                || in_array($user->id, array_column($approval->members->toArray(), 'member_id'))
                || $meetingAllData->created_by == $user->id
            )) {
                unset($meetingAllData['approvals'][$key]);
            }
        }
        $meetingAllData['meetingRecommendations'] = $this->meetingRecommendationRepository->getMeetingRecommendationsForMeeting($meetingId);
        return $meetingAllData;
    }

    public function getMeetingDataForPdfTemplate($meetingId)
    {
        $meetingAllData = $this->repository->getMeetingDataForPdfTemplate($meetingId);
        $meetingAllData['meeting_schedule_date_from'] = Carbon::parse($meetingAllData['meeting_schedule_from'])->format('Y-m-d');
        $meetingAllData['meeting_schedule_time_from'] = Carbon::parse($meetingAllData['meeting_schedule_from'])->format('g:i A');
        $meetingAllData['introduction_template_ar'] = Blade::render($meetingAllData["introduction_template_ar"], ['data' => $meetingAllData]);
        $meetingAllData['introduction_template_en'] = Blade::render($meetingAllData["introduction_template_en"], ['data' => $meetingAllData]);
        $meetingAllData['isRecommendationVisible'] = config('customSetting.meetingRecommendationsFeature');
        return $meetingAllData;
    }

    public function sendNotificationToMeeting($meeting, $meetingStatusId)
    {
        try {
            $notificationData = $this->notificationHelper->prepareNotificationDataOnPublishing($meeting, $meetingStatusId);
            $this->eventHelper->fireEvent($notificationData, 'App\Events\SendNotificationEvent');
        } catch (\Exception $e) {
            report($e);
        }

    }

    public function getMeetingTimeAndAgendasTime($meetingId)
    {
        return $this->repository->getMeetingTimeAndAgendasTime($meetingId);
    }

    public function getOrganizationNumOfMeetings($organizationId)
    {
        return $this->repository->getOrganizationNumOfMeetings($organizationId);
    }

    public function getOrganizationMeetingStatistics($organizationId)
    {
        $meetingStatistics = $this->repository->getOrganizationMeetingStatistics($organizationId);
        $statisticsData = $this->adjustStatisticsArray($meetingStatistics);
        return $statisticsData;
    }

    public function getCommitteeMeetingStatistics($committee_id)
    {
        $meetingStatistics = $this->repository->getCommitteeMeetingStatistics($committee_id);
        $statisticsData = $this->adjustStatisticsArray($meetingStatistics);
        return $statisticsData;
    }

    private function adjustStatisticsArray($meetingStatistics)
    {
        $meetingStatisticsDataAr = [];
        $meetingStatisticsDataEn = [];
        $meetingStatuses = $this->meetingStatusRepository->all();
        $meetingStatisticsStatusIds = array_column($meetingStatistics->toArray(), 'meeting_status_id');
        for ($i = 0; $i < count($meetingStatuses); $i++) {
            $dataAr = [];
            $dataEn = [];
            if ($meetingStatuses[$i]->id != \config('meetingStatus.draft') && in_array($meetingStatuses[$i]->id, $meetingStatisticsStatusIds)) {
                $selectedMeetingStatistics = $meetingStatistics->where('meeting_status_id', $meetingStatuses[$i]->id)->first();

                $dataAr['name'] = $selectedMeetingStatistics->meeting_status_name_ar;
                $dataAr['value'] = $selectedMeetingStatistics->num_of_meetings;

                $dataEn['name'] = $selectedMeetingStatistics->meeting_status_name_en;
                $dataEn['value'] = $selectedMeetingStatistics->num_of_meetings;

                array_push($meetingStatisticsDataAr, $dataAr);
                array_push($meetingStatisticsDataEn, $dataEn);

            } elseif ($meetingStatuses[$i]->id != \config('meetingStatus.draft') && !in_array($meetingStatuses[$i]->id, $meetingStatisticsStatusIds)) {
                $dataAr['name'] = $meetingStatuses[$i]->meeting_status_name_ar;
                $dataAr['value'] = 0;

                $dataEn['name'] = $meetingStatuses[$i]->meeting_status_name_en;
                $dataEn['value'] = 0;
                array_push($meetingStatisticsDataAr, $dataAr);
                array_push($meetingStatisticsDataEn, $dataEn);
            }

        }

        if (count($meetingStatistics) == 0) {
            $statisticsData = ['statisticsDataAr' => $meetingStatisticsDataAr, 'statisticsDataEn' => $meetingStatisticsDataEn, 'is_no_data' => true];

        } else {
            $statisticsData = ['statisticsDataAr' => $meetingStatisticsDataAr, 'statisticsDataEn' => $meetingStatisticsDataEn, 'is_no_data' => false];

        }

        return $statisticsData;
    }

    public function getNumberOfParticipantMeetings($userId, $organizationId)
    {
        return $this->repository->getNumberOfParticipantMeetings($userId, $organizationId);

    }

    public function getParticipantMeetingStatistics($userId, $organizationId)
    {
        $meetingStatistics = $this->repository->getParticipantMeetingStatistics($userId, $organizationId);
        $statisticsData = $this->adjustStatisticsArray($meetingStatistics);
        return $statisticsData;

    }

    public function getCurrentPresentingAttachment($meeting, $user = null)
    {
        $guest = null;
        if (isset($user->meeting_id)) {
            $guest = $this->meetingGuestRepository->GetGuestByMeetingIdAndEmail($user->meeting_id, $user->email);;
        }
        $meetingAgendas = $meeting->meetingAgendas;
        foreach ($meetingAgendas as $meetingAgenda) {
            $canAccess = false;
            if (isset($guest->id)) {
                $canAccess = in_array($guest->id, array_column($meetingAgenda["participants"]->toArray(), 'meeting_guest_id'));
            } else if (isset($user->id)) {
                $canAccess = in_array($user->id, array_column($meetingAgenda["participants"]->toArray(), 'user_id'));
            }

            $meetingAgendaAttachments = $meetingAgenda->agendaAttachments;
            foreach ($meetingAgendaAttachments as $meetingAgendaAttachment) {
                if ($meetingAgendaAttachment->presenter_id) {
                    $attachmentPresenter = $meetingAgendaAttachment->presenter;
                    $presentationStatusId = config('presentationStatuses.present');
                    return $this->presentationHelper->preparePresentAttachmentData($meeting, $attachmentPresenter, $meetingAgendaAttachment->id, $meetingAgendaAttachment->meeting_agenda_id, $presentationStatusId, $canAccess);
                }
            }
        }

        return null;
    }

    public function deleteAllMeetingDetails($meeting)
    {
        $this->openTransaction();

        try {
            $meeting->reminders()->delete();
            $meeting->meetingOrganisers()->detach();
            $meeting->meetingParticipants()->detach();
            $meeting->meetingAttachments()->delete();
            $meeting->meetingStatusHistory()->delete();
            $meetingAgendas = $meeting->meetingAgendas;
            foreach ($meetingAgendas as $agenda) {
                $agenda->agendaPresenters()->detach();
                $agenda->agendaAttachments()->delete();
                $agenda->agendaUserComments()->delete();
                $agendaVotes = $agenda->agendaVotes;

                foreach ($agendaVotes as $agendaVote) {
                    $agendaVote->voteResults()->delete();
                    $this->voteRepository->delete($agendaVote->id);

                }
                // $agendaMoms = $agenda->agendaMins;
                // foreach ($agendaMoms as $agendaMom) {
                //     $agendaMom->attachments()->delete();
                //     $this->momRepository->delete($agendaMom->id);

                // }

                $this->meetingAgendaRepository->delete($agenda->id);
            }

            $this->repository->delete($meeting->id);
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }

        $this->closeTransaction();

    }

    public function getMeetingsToSignWithNextParticipant()
    {
        return $this->repository->getMeetingsToSignWithNextParticipant();
    }

    public function checkNextParticipantToSend($meetings)
    {
        $results = [];
        foreach ($meetings as $key => $meeting) {
            $refusedParticipantExists = $this->checkRefusedToSignParticipants($meeting);
            if ($refusedParticipantExists == false) {
                if ($meeting->is_signature_sent == 1) {
                    $allSentSigned = $meeting->meetingParticipants
                        ->where('pivot.is_signature_sent', 1)
                        ->where('pivot.can_sign', 1)
                        ->where('pivot.is_signature_sent_individualy', 0);

                    if ($allSentSigned->count() > 0) {
                        $lastSentSigned = $allSentSigned->last();

                        if (isset($lastSentSigned->pivot->is_signed)) {
                            $participant = $meeting->meetingParticipants
                                ->where('pivot.is_signature_sent', 0)
                                ->where('pivot.can_sign', 1)
                                ->where('pivot.is_signature_sent_individualy', 0)
                                ->where('pivot.participant_order', '>', $lastSentSigned["pivot"]["participant_order"]);
                            if ($participant->first()) {
                                $meetingParticipant = $participant->first();
                                $results[] = ["meeting" => $meeting, "participant" => $meetingParticipant];
                            }
                        }
                    }
                }
            } else {
                $this->resetSign($meeting);
            }

        }
        return $results;
    }

    public function checkRefusedToSignParticipants($meeting)
    {
        $refusedToSign = $meeting->meetingParticipants
            ->where('pivot.is_signature_sent', 1)
            ->filter(function ($value) {
                return (isset($value['pivot']["is_signed"]) && $value['pivot']["is_signed"] == 0);
            });
        if (count($refusedToSign) > 0) {
            return true;
        }
        return false;
    }

    public function resetSign($meeting)
    {
        $this->repository->update(["is_signature_sent" => 0, "is_mom_sent" => 0], $meeting->id);
        $this->meetingParticipantRepository->resetSign($meeting->id);
        return;
    }
    public function getByDocumentId($documentId)
    {
        return $this->repository->findByField('document_id', $documentId)->first();
    }

    public function getMeetingData($meetingId){
        return $this->repository->getMeetingData($meetingId);
    }

    public function updateChatRoomId($meetingId, array $data) {
        $this->repository->update($data, $meetingId);
    }

    public function getMeetingByChatRoomId($chatRoomId){
        return $this->repository->getMeetingByChatRoomId($chatRoomId);
    }

    public function getMeetingsChatsPagedList($filter,$organizationId,$userId){
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "id";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "ASC";
        }
        return $this->repository->getMeetingsChatsPagedList($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $organizationId, $userId);
    }

        public function getUserMeetingsPagedList($filter,$organizationId,$userId){

            if (isset($filter->SearchObject)) {
                $params = (object) $filter->SearchObject;
            } else {
                $params = new stdClass();
            }
            if (!property_exists($filter, "SortBy")) {
                $filter->SortBy = "id";
            }
            if (!property_exists($filter, "SortDirection")) {
                $filter->SortDirection = "DESC";
            }
            return $this->repository->getUserMeetingsPagedList($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $organizationId, $userId);
        }

     public function UpdateChatMetaData($last_message_text,$meeting_id,$user)
    {
        $this->repository->update(["last_message_text" => $last_message_text, "last_message_date" => Carbon::now()->addHours($user->organization->timeZone->diff_hours)], $meeting_id);
    }

    public function getMeetingUsersMembersError($meeting,$chatGroupUsersIds){
        $meetingUsers = array_column($meeting->participants->toArray(), 'user_id');
        $meetingUsers = array_merge($meetingUsers,array_column($meeting->meetingOrganisers->toArray(), 'user_id'));
        $meetingUsers[] = $meeting->creator->id;

        return count(array_diff($chatGroupUsersIds,$meetingUsers)) > 0? true : false;
    }

    private function prepareAgendaMinsAsAgendaComments($meetingId,$meetingAgendas){
        foreach ($meetingAgendas as $index => $meetingAgenda) {
            $agendaMins = [];
            foreach ($meetingAgenda->agendaUserComments as $key => $agendaUserComment) {
                $data = [
                    'agenda_id' => $agendaUserComment->meeting_agenda_id,
                    'attachments' => [],
                    'created_at' => Carbon::parse($agendaUserComment->created_at)->format('Y-m-d H:i:s'),
                    'deleted_at' => Carbon::parse($agendaUserComment->deleted_at)->format('Y-m-d H:i:s'),
                    'id' => $agendaUserComment->id,
                    'meeting_id' => $meetingId,
                    'mom_summary_ar' => $agendaUserComment->comment_text,
                    'mom_summary_en' => $agendaUserComment->comment_text,
                    'mom_title_ar' => $meetingAgenda['agenda_title_ar'],
                    'mom_title_en' => $meetingAgenda['agenda_title_en'],
                    'updated_at' => Carbon::parse($agendaUserComment->updated_at)->format('Y-m-d H:i:s')
                ];
                array_push($agendaMins,$data);
            }
            $meetingAgendas[$index]->agendaMins = $agendaMins;
        }
        return $meetingAgendas;
    }

    private function craeteMeeting($meetingData,$meetingRemindersData)
    {
        $meetingReminders = [];
        $meetingOnlineConfiguration = [];
        if(isset($meetingData['meeting_online_configuration'])){
            $meetingOnlineConfiguration = $meetingData['meeting_online_configuration'];
            unset($meetingData['meeting_online_configuration']);
        }
        $meeting = $this->repository->create($meetingData);
        if(count($meetingOnlineConfiguration) > 0){
            $meetingOnlineConfiguration['meeting_id'] = $meeting->id;
            $meetingOnlineConfig = $this->meetingOnlineConfigurationRepository->create($meetingOnlineConfiguration);
        }
        if (count($meetingRemindersData) > 0) {
            foreach ($meetingRemindersData as $key => $reminder) {
                $meetingReminders[$key]['reminder_id'] = $reminder;
            }
        }
        $meeting->reminders()->createMany($meetingReminders);
        $this->addCommitteeUsersAsParticipants($meeting);
        $this->addCommitteOrganiserAsMeetingOrganiser($meeting);

        return $meeting;
    }

    private function updateMeeting($meeting,$data)
    {
        if (isset($data['meeting_reminders'])) {
            $meetingRemindersData = $data['meeting_reminders'];
            $meetingReminders = [];
            if (count($meetingRemindersData) > 0) {
                foreach ($meetingRemindersData as $key => $reminder) {
                    $meetingReminders[$key]['reminder_id'] = $reminder;
                }
            }
            $meeting->reminders()->delete();
            $meeting->reminders()->createMany($meetingReminders);
            unset($data['meeting_reminders']);
        }

        if (isset($data["meeting_status_id"]) && $meeting->meeting_status_id != $data["meeting_status_id"]) {
            $statusLogData = $this->meetingStatusHistoryHelper->prepareLogData($data["meeting_status_id"], $meeting->id);
            $this->meetingStatusHistoryRepository->create($statusLogData);
            if (in_array($data["meeting_status_id"], [config('meetingStatus.cancel'), config('meetingStatus.end')])) {
                $this->voteRepository->endAllMeetingVotes($meeting->id);
            }
        }
        if(isset($data['meeting_online_configuration'])) {
            if($meeting->meetingOnlineConfigurations->count() > 0){ // update
                $meetingOnlineConfiguration = $meeting->meetingOnlineConfigurations()->first();
                $this->meetingOnlineConfigurationRepository->update($data['meeting_online_configuration'],$meetingOnlineConfiguration->id);
            } else { // create
                $data['meeting_online_configuration']['meeting_id'] = $meeting->id;
                $meetingOnlineConfig = $this->meetingOnlineConfigurationRepository->create($data['meeting_online_configuration']);
                unset($data['meeting_online_configuration']);
            }
        }

        $oldCommitteeId = $meeting->committee_id;

        $this->repository->update($data, $meeting->id);

        if (isset($data["committee_id"]) && $oldCommitteeId != $data["committee_id"]) {
            $this->addCommitteeUsersAsParticipants($meeting);
            $this->addCommitteOrganiserAsMeetingOrganiser($meeting);
        }
    }

    public function publishMeetingVersion($meetingId){
        $masterMeeting = $this->getById($meetingId);
        // update master meeting with last version
        $versionOfMeeting = $this->repository->getUnpublishedVersionOfMeeting($meetingId);
        if ($versionOfMeeting) {
            // update meeting data
            $masterMaatingData = $this->meetingVersionHelper->prepareMasterMeetingData($versionOfMeeting);
            $meetingOnlineConfiguration = $versionOfMeeting->meetingOnlineConfigurations()->first();
            if($meetingOnlineConfiguration){
                $onlineConfig = $meetingOnlineConfiguration;
                $data = $this->meetingVersionHelper->prepareOnlineConfigurationOfMeeting($onlineConfig);
                if($masterMeeting->meetingOnlineConfigurations()->count() > 0){ // update 
                    $meetingOnlineConfiguration = $masterMeeting->meetingOnlineConfigurations()->first();
                    $this->meetingOnlineConfigurationRepository->update($data,$meetingOnlineConfiguration->id);
                } else { // create
                    $data['meeting_id'] = $masterMeeting->id;
                    $meetingOnlineConfig = $this->meetingOnlineConfigurationRepository->create($data);
                }
            }
            $this->repository->update($masterMaatingData, $meetingId);
            // update meeting reminders
            $this->updateMeetingReminders($masterMeeting,$versionOfMeeting);
            // update meeting organisers
            $this->updateMeetingOrganisers($masterMeeting,$versionOfMeeting);
            // update meeting participants
            $this->updateMeetingParticipants($masterMeeting,$versionOfMeeting);
            // update meeting attachemnts
            $this->updateMeetingAttachments($masterMeeting,$versionOfMeeting);
            // update meeting agendas
            $this->updateMeetingAgendas($masterMeeting,$versionOfMeeting);
            // update meeting approvals
            $this->updateMeetingApprovals($masterMeeting, $versionOfMeeting);
            // update meeting Recommendations
            $this->updateMeetingRecommendations($masterMeeting, $versionOfMeeting);

            $this->repository->update(['is_published' => true], $versionOfMeeting->id);
        }
    }

    private function updateMeetingReminders($targetMeeting,$meeting){
        $meetingRemindersData = $meeting->reminders;
        $meetingReminders = [];
        if ($meetingRemindersData->count() > 0) {
            foreach ($meetingRemindersData as $key => $meetingReminder) {
                $meetingReminders[$key]['reminder_id'] = $meetingReminder->reminder_id;
            }
        }
        $targetMeeting->reminders()->delete();
        $targetMeeting->reminders()->createMany($meetingReminders);
    }

    private function updateMeetingOrganisers($targetMeeting,$meeting){
        $organisersIds = [];
        foreach ($meeting->organisers as $key => $meetingOrganizer) {
            $organisersIds[$key]['user_id'] = $meetingOrganizer->user_id;
        }
        $this->createMeetingOrganisers($targetMeeting->id, $organisersIds);
    }

    public function updateMeetingParticipants($targetMeeting,$meeting){
        $participantsIds = [];
        foreach ($meeting->participants as $key => $meetingParticipant) {
            $participantsIds[$key]['user_id'] = $meetingParticipant->user_id;
            $participantsIds[$key]['meeting_role_id'] = $meetingParticipant->meeting_role_id;
            $participantsIds[$key]['participant_order'] = $meetingParticipant->participant_order;
            $participantsIds[$key]['can_sign'] = $meetingParticipant->can_sign;
            $participantsIds[$key]['send_mom'] = $meetingParticipant->send_mom;
            $participantsIds[$key]['meeting_attendance_status_id'] = $meetingParticipant->meeting_attendance_status_id;
            $participantsIds[$key]['is_accept_absent_by_organiser'] = $meetingParticipant->is_accept_absent_by_organiser;
        }
        $this->createMeetingParticipants($targetMeeting->id, $participantsIds);
    }

    private function updateMeetingAttachments($targetMeeting,$meeting){
        $attachmentsData = [];
        $oldFiles = array_column($targetMeeting->meetingAttachments->toArray(), 'file_id');
        $newFiles = array_column($meeting->meetingAttachments->toArray(), 'file_id');
        $deletedFiles = array_filter($oldFiles,function($file_id)use($newFiles){
            return (!in_array($file_id,$newFiles));
        });
        foreach ($meeting->meetingAttachments as $key => $meetingAttachment) {
            $attachmentsData[$key]['attachment_name'] = $meetingAttachment->attachment_name;
            $attachmentsData[$key]['attachment_url'] = $meetingAttachment->attachment_url;
            $attachmentsData[$key]['file_id']  =  $meetingAttachment->file_id;
        }
        $targetMeeting->meetingAttachments()->delete();
        $this->fileRepository->deleteFiles($deletedFiles);
        $targetMeeting->meetingAttachments()->createMany($attachmentsData);
    }

    private function updateMeetingAgendas($targetMeeting,$meeting){
        $numberOfUpdatedAgendas = 0;
        $haveNewAgendasAdded = false;
        $haveDeletedAgendas = false;
        $targetMeetingAgendas = $targetMeeting->meetingAgendas()->orderBy('id')->get()->toArray();
        $meetingAgendas = $meeting->meetingAgendas()->orderBy('id')->get()->toArray();
        if(count($targetMeetingAgendas) == count($meetingAgendas)){
            $numberOfUpdatedAgendas = count($targetMeetingAgendas);
        } else if (count($targetMeetingAgendas) < count($meetingAgendas)){
            $numberOfUpdatedAgendas = count($targetMeetingAgendas);
            $haveNewAgendasAdded = true;
        } else if (count($targetMeetingAgendas) > count($meetingAgendas)){
            $numberOfUpdatedAgendas = count($meetingAgendas);
            $haveDeletedAgendas = true;
        }
        if ($numberOfUpdatedAgendas > 0) { // update meeting agendas
            for ($i=0; $i < $numberOfUpdatedAgendas; $i++) { 
                $targetAgenda = [];
                $agendaParticipants = [];
                $agendaPresenters = [];
                $meetingAgenda = $this->meetingAgendaRepository->find($meetingAgendas[$i]['id']);
                $targetMeetingAgenda = $this->meetingAgendaRepository->find($targetMeetingAgendas[$i]['id']);

                $targetAgenda['agenda_title_ar'] = $meetingAgenda['agenda_title_ar'];
                $targetAgenda['agenda_title_en'] = $meetingAgenda['agenda_title_en'];
                $targetAgenda['agenda_time_in_min'] = $meetingAgenda['agenda_time_in_min'];
                $targetAgenda['agenda_purpose_id'] = $meetingAgenda['agenda_purpose_id'];
                $targetAgenda['agenda_description_ar'] = $meetingAgenda['agenda_description_ar'];
                $targetAgenda['agenda_description_en'] = $meetingAgenda['agenda_description_en'];

                if (isset($meetingAgenda['participants'])) {
                    if (count($meetingAgenda['participants']) > 0) {
                        foreach ($meetingAgenda['participants'] as $key => $participant) {
                            $agendaParticipants[$key]['user_id'] = $participant["user_id"];
                            $agendaParticipants[$key]['meeting_guest_id'] = $participant["meeting_guest_id"];
                        }
                    }
                }
                
                if (isset($meetingAgenda['presenters'])) {
                    if (count($meetingAgenda['presenters']) > 0) {
                        foreach ($meetingAgenda['presenters'] as $key => $presenter) {
                            $agendaPresenters[$key]['user_id'] = $presenter["user_id"];
                            $agendaPresenters[$key]['meeting_guest_id'] = $presenter["meeting_guest_id"];
                        }
                    }
                }

                $this->meetingAgendaRepository->update($targetAgenda,$targetMeetingAgenda['id']);
                $targetMeetingAgenda->presenters()->delete();
                $targetMeetingAgenda->presenters()->createMany($agendaPresenters);
                $targetMeetingAgenda->participants()->delete();
                $targetMeetingAgenda->participants()->createMany($agendaParticipants);

                // update agenda attachments
                $this->updateMeetingAgendaAttachments($targetMeetingAgenda,$meetingAgenda);
                // update agenda votes
                $this->updateMeetingAgendaVotess($targetMeetingAgenda,$meetingAgenda);
            }
        }
        if ($haveNewAgendasAdded) { // add new  meeting agenda
            for ($i=$numberOfUpdatedAgendas; $i < count($meetingAgendas); $i++) { 
                $meetingAgenda = $this->meetingAgendaRepository->find($meetingAgendas[$i]['id']);
                $this->createMeetingAgenda($targetMeeting,$meetingAgenda);
            }
        }
        if ($haveDeletedAgendas) { // delete meeting agendas
            for ($i=$numberOfUpdatedAgendas; $i < count($targetMeetingAgendas); $i++) { 
                $targetMeetingAgenda = $this->meetingAgendaRepository->find($targetMeetingAgendas[$i]['id']);
                $this->deleteMeetingAgenda($targetMeetingAgenda);
            }
        }
    }

    public function getUnpublishedVersionOfMeeting($meetingId){
        return $this->repository->getUnpublishedVersionOfMeeting($meetingId);
    }

    public function createVersionOfMeetingFromMasterMeeting($masterMeeting, $lastVersionOfMeeting, $customApprovalId = null, $data = null)
    {
        $meetingOnlineConfiguration = [];
        $versionOfMeeting = $this->meetingVersionHelper->prepareVersionOfMeetingData([],$masterMeeting,$lastVersionOfMeeting? ($lastVersionOfMeeting->version_number + 1) : 1,true);
        $meeting = $this->repository->create($versionOfMeeting);
        if(isset($versionOfMeeting['meeting_online_configuration'])){
            $meetingOnlineConfiguration = $versionOfMeeting['meeting_online_configuration'];
            unset($versionOfMeeting['meeting_online_configuration']);
        }
        if(count($meetingOnlineConfiguration) > 0){
            $meetingOnlineConfiguration['meeting_id'] = $meeting->id;
            $meetingOnlineConfig = $this->meetingOnlineConfigurationRepository->create($meetingOnlineConfiguration);
        }
        $this->updateMeetingParticipants($meeting,$masterMeeting);
        $this->updateMeetingOrganisers($meeting,$masterMeeting);
        $this->updateMeetingReminders($meeting,$masterMeeting);
        $this->updateMeetingAttachments($meeting,$masterMeeting);
        $this->updateMeetingAgendas($meeting,$masterMeeting);
        $this->updateMeetingApprovals($meeting, $masterMeeting, $customApprovalId, $data, $lastVersionOfMeeting);
        $this->updateMeetingRecommendations($meeting, $masterMeeting);
        return $meeting;
    }

    public function getLastVersionOfMeeting($meetingId){
        return $this->repository->getLastVersionOfMeeting($meetingId);
    }

    private function deleteMeetingAgenda($targetMeetingAgenda){
        $targetMeetingAgenda->presenters()->delete();
        $targetMeetingAgenda->agendaAttachments()->delete();
        $targetMeetingAgenda->agendaUserComments()->delete();
        foreach ($targetMeetingAgenda->agendaVotes as $agendaVote) {
            $agendaVote->voteResults()->delete();
            $this->voteRepository->delete($agendaVote->id);
        }
        $this->meetingAgendaRepository->delete($targetMeetingAgenda->id);
    }

    private function createMeetingAgenda($targetMeeting,$meetingAgenda){
        $targetAgenda = [];
        $agendaParticipants = [];
        $agendaPresenters = [];
        $agendaAttachmentsData = [];
        $agendaVotesData = [];
        $agendaVotesParticipantData = [];

        $targetAgenda['agenda_title_ar'] = $meetingAgenda['agenda_title_ar'];
        $targetAgenda['agenda_title_en'] = $meetingAgenda['agenda_title_en'];
        $targetAgenda['agenda_time_in_min'] = $meetingAgenda['agenda_time_in_min'];
        $targetAgenda['agenda_purpose_id'] = $meetingAgenda['agenda_purpose_id'];
        $targetAgenda['agenda_description_ar'] = $meetingAgenda['agenda_description_ar'];
        $targetAgenda['agenda_description_en'] = $meetingAgenda['agenda_description_en'];
        $targetAgenda['meeting_id'] = $targetMeeting->id;

        if (isset($meetingAgenda['participants'])) {
            if (count($meetingAgenda['participants']) > 0) {
                foreach ($meetingAgenda['participants'] as $key => $participant) {
                    $agendaParticipants[$key]['user_id'] = $participant["user_id"];
                    $agendaParticipants[$key]['meeting_guest_id'] = $participant["meeting_guest_id"];
                }
            }
        }

        if (isset($meetingAgenda['presenters'])) {
            if (count($meetingAgenda['presenters']) > 0) {
                foreach ($meetingAgenda['presenters'] as $key => $presenter) {
                    $agendaPresenters[$key]['user_id'] = $presenter["user_id"];
                    $agendaPresenters[$key]['meeting_guest_id'] = $presenter["meeting_guest_id"];
                }
            }
        }
        foreach ($meetingAgenda->agendaAttachments as $key => $agendaAttachment) {
            $agendaAttachmentsData[$key]['attachment_name'] = $agendaAttachment->attachment_name;
            $agendaAttachmentsData[$key]['attachment_url'] = $agendaAttachment->attachment_url;
        }
        foreach ($meetingAgenda->agendaVotes as $key => $agendaVote) {
            $agendaVotesData[$key]['meeting_id'] = $targetMeeting->id;
            $agendaVotesData[$key]['vote_subject_ar'] = $agendaVote->vote_subject_ar;
            $agendaVotesData[$key]['vote_subject_en'] = $agendaVote->vote_subject_en;
            $agendaVotesData[$key]['vote_schedule_from'] = $agendaVote->vote_schedule_from;
            $agendaVotesData[$key]['vote_schedule_to'] = $agendaVote->vote_schedule_to;
            $agendaVotesData[$key]['decision_due_date'] = $agendaVote->decision_due_date;
            $agendaVotesData[$key]['decision_type_id'] = $agendaVote->decision_type_id;
            $agendaVotesData[$key]['vote_type_id'] = $agendaVote->vote_type_id;
            $agendaVotesData[$key]['vote_result_status_id'] = $agendaVote->vote_result_status_id;
            $agendaVotesData[$key]['is_secret'] = $agendaVote->is_secret;
            foreach ($agendaVote->voteParticipants as $index => $voteParticipation) {
                $agendaVotesParticipantData[$index]["user_id"] = $voteParticipation->user_id;
                $agendaVotesParticipantData[$index]["meeting_guest_id"] = $voteParticipation->meeting_guest_id;
            }
        }
        $meetingAgendaModel = $this->meetingAgendaRepository->create($targetAgenda);
        $meetingAgendaModel->presenters()->createMany($agendaPresenters);
        $meetingAgendaModel->participants()->createMany($agendaParticipants);
        if (count($agendaAttachmentsData) != 0) {
            $newAgendaAttachments = $meetingAgendaModel->agendaAttachments()->createMany($agendaAttachmentsData);
            HandleAttachments::dispatch($newAgendaAttachments);
        }
        foreach ($agendaVotesData as $key => $agendaVoteData) {
            $agendaVoteData['agenda_id'] = $meetingAgendaModel->id;
            $vote = $this->voteRepository->create($agendaVoteData);
            /***************************/
            foreach ($agendaVotesParticipantData as $key => $agendaVotesParticipant) {
                $agendaVotesParticipant['vote_id'] = $vote->id;
                $this->voteParticipantRepository->create($agendaVotesParticipant);
            }
            /***************************/
            $this->addVoteResultsForVote($vote);
            // fire event for add meeting decision
            if (!$targetMeeting->version_number) {
                SystemNotification::dispatch($vote,$targetMeeting->creator,config('meetingDecision.addDecision'));
            }
        }
    }

    private function updateMeetingAgendaAttachments($targetMeetingAgenda,$meetingAgenda){
        $numberOfUpdatedAttachments = 0;
        $haveNewAttachmentsAdded = false;
        $haveDeletedAttachments = false;
        $targetAgendaAttachments = $targetMeetingAgenda->agendaAttachments()->orderBy('id')->get()->toArray();
        $agendaAttachments = $meetingAgenda->agendaAttachments()->orderBy('id')->get()->toArray();
        
        if(count($targetAgendaAttachments) == count($agendaAttachments)){
            $numberOfUpdatedAttachments = count($targetAgendaAttachments);
        } else if (count($targetAgendaAttachments) < count($agendaAttachments)){
            $numberOfUpdatedAttachments = count($targetAgendaAttachments);
            $haveNewAttachmentsAdded = true;
        } else if (count($targetAgendaAttachments) > count($agendaAttachments)){
            $numberOfUpdatedAttachments = count($agendaAttachments);
            $haveDeletedAttachments = true;
        }
        if($numberOfUpdatedAttachments > 0){ // update attachments
            for ($i=0; $i < $numberOfUpdatedAttachments; $i++) { 
                $attachmentData = [];
                $attachmentData['attachment_name'] = $agendaAttachments[$i]['attachment_name'];
                $attachmentData['attachment_url'] = $agendaAttachments[$i]['attachment_url'];
                $attachmentData['file_id'] = $agendaAttachments[$i]['file_id'];
                $this->attachmentRepository->update($attachmentData,$targetAgendaAttachments[$i]['id']);
                $attachment = $this->attachmentRepository->find($targetAgendaAttachments[$i]['id']);
                HandleAttachments::dispatch([$attachment]);
            }
        }
        if($haveNewAttachmentsAdded){ //add attachments
            $agendaAttachmentsData = [];
            for ($i=$numberOfUpdatedAttachments; $i < count($agendaAttachments); $i++) { 
                $agendaAttachmentsData[$i-$numberOfUpdatedAttachments]['attachment_name'] = $agendaAttachments[$i]['attachment_name'];
                $agendaAttachmentsData[$i-$numberOfUpdatedAttachments]['attachment_url'] = $agendaAttachments[$i]['attachment_url'];
                $agendaAttachmentsData[$i-$numberOfUpdatedAttachments]['file_id'] = $agendaAttachments[$i]['file_id'];
            }
            if (count($agendaAttachmentsData) != 0) {
                $newAgendaAttachments = $targetMeetingAgenda->agendaAttachments()->createMany($agendaAttachmentsData);
                HandleAttachments::dispatch($newAgendaAttachments);
            }
        }
        if($haveDeletedAttachments){ // delete attachments
            for ($i=$numberOfUpdatedAttachments; $i < count($targetAgendaAttachments); $i++) { 
                if(isset($targetAgendaAttachments[$i]['file_id'])){
                    $this->fileRepository->delete($targetAgendaAttachments[$i]['file_id']);
                }
                $this->attachmentRepository->delete($targetAgendaAttachments[$i]['id']);
            }
        }
    }

    private function updateMeetingAgendaVotess($targetMeetingAgenda, $meetingAgenda)
    {
        $numberOfUpdatedVotes = 0;
        $haveNewVotesAdded = false;
        $haveDeletedVotes = false;
        $targetAgendaVotesDb = $targetMeetingAgenda->agendaVotes()->orderBy('id')->get();
        $targetAgendaVotes = $targetAgendaVotesDb->toArray();
        $agendaVotesDb = $meetingAgenda->agendaVotes()->orderBy('id')->get();
        $agendaVotes = $agendaVotesDb->toArray();
        if(count($targetAgendaVotes) == count($agendaVotes)){
            $numberOfUpdatedVotes = count($targetAgendaVotes);
        } else if (count($targetAgendaVotes) < count($agendaVotes)){
            $numberOfUpdatedVotes = count($targetAgendaVotes);
            $haveNewVotesAdded = true;
        } else if (count($targetAgendaVotes) > count($agendaVotes)){
            $numberOfUpdatedVotes = count($agendaVotes);
            $haveDeletedVotes = true;
        }
        if($numberOfUpdatedVotes > 0){ // update votes
            for ($i = 0; $i < $numberOfUpdatedVotes; $i++) {
                $voteData = [];
                $voteData['vote_type_id'] = $agendaVotes[$i]['vote_type_id'];
                $voteData['vote_subject_ar'] = $agendaVotes[$i]['vote_subject_ar'];
                $voteData['vote_subject_en'] = $agendaVotes[$i]['vote_subject_en'];
                $voteData['vote_schedule_from'] = $agendaVotes[$i]['vote_schedule_from'];
                $voteData['vote_schedule_to'] = $agendaVotes[$i]['vote_schedule_to'];
                $voteData['decision_type_id'] = $agendaVotes[$i]['decision_type_id'];
                $voteData['decision_due_date'] = $agendaVotes[$i]['decision_due_date'];
                $voteData['is_secret'] = $agendaVotes[$i]['is_secret'];
                /************************************************************************************************************************/
                $voteParticipation =  ($agendaVotesDb[$i])->voteParticipants()->orderBy('id')->get()->toArray();
                $targetVoteParticipation =  ($targetAgendaVotesDb[$i])->voteParticipants()->orderBy('id')->get()->toArray();
                foreach ($targetVoteParticipation as $item) {
                    $this->voteParticipantRepository->delete($item['id']);
                }
                foreach ($voteParticipation as $item) {
                    $voteParticipationData['vote_id'] = $targetAgendaVotes[$i]['id'];
                    $voteParticipationData['user_id'] = $item['user_id'];
                    $voteParticipationData['meeting_guest_id'] = $item['meeting_guest_id'];
                    $this->voteParticipantRepository->create($voteParticipationData);
                }
                /****************************************************************************************************/
                $this->voteRepository->update($voteData,$targetAgendaVotes[$i]['id']);
                $this->updateVoteResultsForVote($this->voteRepository->find($targetAgendaVotes[$i]['id'],array('*')));
                // fire event for update meeting decision
                // if (!$targetMeetingAgenda->meeting->version_number) {
                //     $masterVote = $this->voteRepository->find($targetAgendaVotes[$i]['id'],array('*'));
                //     SystemNotification::dispatch($masterVote,$targetMeetingAgenda->meeting->creator,config('meetingDecision.editDecision'));
                // }
            }
        }
        if($haveNewVotesAdded){ //add votes
            $votesData = [];
            for ($i=$numberOfUpdatedVotes; $i < count($agendaVotes); $i++) { 
                $votesData[$i-$numberOfUpdatedVotes]['meeting_id'] = $targetMeetingAgenda['meeting_id'];
                $votesData[$i-$numberOfUpdatedVotes]['agenda_id'] = $targetMeetingAgenda['id'];
                $votesData[$i-$numberOfUpdatedVotes]['vote_type_id'] = $agendaVotes[$i]['vote_type_id'];
                $votesData[$i-$numberOfUpdatedVotes]['vote_subject_ar'] = $agendaVotes[$i]['vote_subject_ar'];
                $votesData[$i-$numberOfUpdatedVotes]['vote_subject_en'] = $agendaVotes[$i]['vote_subject_en'];
                $votesData[$i-$numberOfUpdatedVotes]['vote_schedule_from'] = $agendaVotes[$i]['vote_schedule_from'];
                $votesData[$i-$numberOfUpdatedVotes]['vote_schedule_to'] = $agendaVotes[$i]['vote_schedule_to'];
                $votesData[$i-$numberOfUpdatedVotes]['decision_type_id'] = $agendaVotes[$i]['decision_type_id'];
                $votesData[$i-$numberOfUpdatedVotes]['decision_due_date'] = $agendaVotes[$i]['decision_due_date'];
                $votesData[$i-$numberOfUpdatedVotes]['vote_result_status_id'] = $agendaVotes[$i]['vote_result_status_id'];
                $votesData[$i-$numberOfUpdatedVotes]['is_secret'] = $agendaVotes[$i]['is_secret'];
            }
            if (count($votesData) != 0) {
                foreach ($votesData as $key => $voteData) {
                    $vote = $this->voteRepository->create($voteData);
                    $this->addVoteResultsForVote($vote);
                    // fire event for add meeting decision
                    if (!$targetMeetingAgenda->meeting->version_number) {
                        SystemNotification::dispatch($vote,$targetMeetingAgenda->meeting->creator,config('meetingDecision.addDecision'));
                    }
                }
            }
        }
        if($haveDeletedVotes){ // delete votes
            for ($i=$numberOfUpdatedVotes; $i < count($targetAgendaVotes); $i++) { 
                $vote = $this->voteRepository->find($targetAgendaVotes[$i]['id'],array('*'));
                $vote->voteResults()->delete();
                $vote->voteParticipants()->delete();
                $this->voteRepository->delete($targetAgendaVotes[$i]['id']);
            }
        }
    }

    public function updateMeetingIsPublishedFlag($meetingId){
		$this->repository->update(['is_published' => 0],$meetingId);
    }

    public function changeMeetingMomTemplate ($meetingId,$newMomTemplateId){
        $this->repository->update(['meeting_mom_template_id' => $newMomTemplateId],$meetingId);
        // $this->repository->updateAllVersions($meetingId,$newMomTemplateId);
    }
    public function changeMeetingMomPdf ($meeting,$data){
        // create file for mom pdf
        $result = explode('/',$data['mom_pdf_url']);
        $fileName = $result[count($result) -1];
        $storageFile =  $this->storageHelper->mapSystemFile($fileName,$data['mom_pdf_url'],0 ,$meeting->creator);
        if($meeting->mom_pdf_file_id){ // edit file data
            $this->fileRepository->update($storageFile,$meeting->mom_pdf_file_id);
            $momPdfFileId  =  $meeting->mom_pdf_file_id;
        } else { // add new file
            $attachmentFile = $this->fileRepository->create($storageFile);
            $momPdfFileId  =  $attachmentFile->id;
        }
        $this->repository->update(['is_mom_pdf' => $data['is_mom_pdf'],'mom_pdf_url'=> $data['mom_pdf_url'],'mom_pdf_file_name'=> $data['mom_pdf_file_name'], 'mom_pdf_file_id' => $momPdfFileId],$meeting->id);
        // $this->repository->updateAllVersions($meetingId,$newMomTemplateId);
    }

    private function  prepareParticipantStatistics($meetingData)
    {
        $meetingData = $this->meetingGuestHelper->prepareGuestsStats($meetingData);
        $statisticsDataAr = [];
        $statisticsDataEn = [];
        $statisticsDataAr[0]['name'] = 'الحاضرين';
        $statisticsDataAr[0]['value'] = $meetingData->attend;

        $statisticsDataAr[1]['name'] = 'المعتذرين';
        $statisticsDataAr[1]['value'] = $meetingData->absent_without_accepted;

        $statisticsDataAr[2]['name'] = 'اعتذار مقبول';
        $statisticsDataAr[2]['value'] = $meetingData->accept_absent;

        $statisticsDataAr[3]['name'] = 'ربمايحضر';
        $statisticsDataAr[3]['value'] = $meetingData->mayAttend;

        $statisticsDataAr[4]['name'] = 'لم يتم يرد';
        $statisticsDataAr[4]['value'] = $meetingData->noRespond;

        $statisticsDataEn[0]['name'] = 'Coming';
        $statisticsDataEn[0]['value'] = $meetingData->attend;

        $statisticsDataEn[1]['name'] = 'Absent';
        $statisticsDataEn[1]['value'] = $meetingData->absent_without_accepted;

        $statisticsDataEn[2]['name'] = 'Accept absent';
        $statisticsDataEn[2]['value'] = $meetingData->accept_absent;

        $statisticsDataEn[3]['name'] = 'May Attend';
        $statisticsDataEn[3]['value'] = $meetingData->mayAttend;

        $statisticsDataEn[4]['name'] = 'No Response';
        $statisticsDataEn[4]['value'] = $meetingData->noRespond;

        // if ($numOfNewOrganizationRequests->num_of_new_organization_requests == 0 &&
        //     $numOfActiveOrganizations->num_of_active_organization == 0 &&
        //     $numOfInActiveOrganizations->num_of_inactive_organization == 0) {
        //     $statisticsData = ['statisticsDataAr' => $statisticsDataAr, 'statisticsDataEn' => $statisticsDataEn, 'is_no_data' => true];

        // } else {
            $statisticsData = ['statisticsDataAr' => $statisticsDataAr, 'statisticsDataEn' => $statisticsDataEn, 'is_no_data' => false];

        // }

        return $statisticsData;

    }

    public function addVoteResultsForVote($vote){
        $voteResults = [];
        $voteParticipants = $vote->voteParticipants;

        foreach ($voteParticipants as $key => $voteParticipant) {
            $voteResults[$key]['user_id'] = $voteParticipant['user_id'];
            $voteResults[$key]['meeting_guest_id'] = $voteParticipant['meeting_guest_id'];
            $voteResults[$key]['vote_id'] = $vote->id;
            $voteResults[$key]['vote_status_id'] = config('voteStatuses.notDecided');
            $voteResults[$key]['decision_weight'] = (isset($voteParticipant['user_id']) && $this->committeeUserRepository->checkIfUserIsHeadOfCommittee($voteParticipant['user_id'], $vote->meeting->committee_id)) ? config('decisionWeight.HeadDecisionWeight') : config('decisionWeight.participantDecisionWeight');
        }
        $vote->voteResults()->createMany($voteResults);
    }

    public function updateVoteResultsForVote($vote){
        $voteResults = [];
        $voteParticipants = $vote->voteParticipants;
        $oldVoteResults = $vote->voteResults->toArray();

        $toBeAdded = [];
        $toBeDeleted = [];

        // To be added
        foreach ($voteParticipants as $voteParticipant) {
            $found = false;
            foreach ($oldVoteResults as $oldVoteResult) {
                if((isset($oldVoteResult["user_id"]) && $oldVoteResult["user_id"] == $voteParticipant["user_id"]) ||
                    (isset($oldVoteResult["meeting_guest_id"]) && $oldVoteResult["meeting_guest_id"] == $voteParticipant["meeting_guest_id"])
                ){
                    $found = true;
                    break;
                }
            }
            if(!$found){
                $toBeAdded[] = $voteParticipant;
            }
        }

        // To be deleted
        foreach ($oldVoteResults as $oldVoteResult) {
            $found = false;
            foreach ($voteParticipants as $voteParticipant) {
                if ((isset($oldVoteResult["user_id"]) && $oldVoteResult["user_id"] == $voteParticipant["user_id"]) ||
                    (isset($oldVoteResult["meeting_guest_id"]) && $oldVoteResult["meeting_guest_id"] == $voteParticipant["meeting_guest_id"])
                ) {
                    $found = true; 
                    break;
                }
            }
            if (!$found) {
                $toBeDeleted[] = $oldVoteResult;
            }
        }

        foreach ($toBeAdded as $key => $value) {
            $voteResults[$key]['user_id'] = $value["user_id"];
            $voteResults[$key]['meeting_guest_id'] = $value["meeting_guest_id"];
            $voteResults[$key]['vote_id'] = $vote->id;
            $voteResults[$key]['vote_status_id'] = config('voteStatuses.notDecided');
            $voteResults[$key]['decision_weight'] = (isset($value["user_id"]) && $this->committeeUserRepository->checkIfUserIsHeadOfCommittee($value["user_id"], $vote->meeting->committee_id)) ? config('decisionWeight.HeadDecisionWeight') : config('decisionWeight.participantDecisionWeight');
        }

        if(count($voteResults) > 0){
            $vote->voteResults()->createMany($voteResults);
        }

        foreach ($toBeDeleted as $value) {
            $vote->voteResults()->where('user_id', $value["user_id"])->where('meeting_guest_id', $value["meeting_guest_id"])->delete();
        }
    }

    public function setShowAttendancePercentageWarningFlagForEachMeeting($meetingList){
        foreach ($meetingList->Results as $key => $meeting) {
            $meetingList->Results[$key]['show_attendance_percentage_warning'] = $meeting['meeting_attendance_percentage'] && (($meeting['attend'] /$meeting['totalParticipants'] *100) < $meeting['meeting_attendance_percentage'])? true : false;
        }
        return $meetingList;
    }

    public function getLimitOfMeetingsForUser($userId){
        $meetings = $this->repository->getLimitOfMeetingsForUser($userId)->toArray();
        foreach ($meetings as $key => $meeting) {
            setlocale(LC_ALL, 'ar_AE.utf8');
            $meetings[$key]['meeting_schedule_time_from_ar'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->formatLocalized('%I:%M %p');
            $meetings[$key]['meeting_schedule_time_to_ar'] = Carbon::parse($meetings[$key]['meeting_schedule_to'])->formatLocalized('%I:%M %p');
            $meetings[$key]['meeting_date_ar']['month'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->formatLocalized('%B');
            $meetings[$key]['meeting_date_ar']['day'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->formatLocalized('%A');
            $meetings[$key]['meeting_date_ar']['date'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->formatLocalized('%d');
            
            setlocale(LC_ALL, 'en_EN.utf8');
            $meetings[$key]['meeting_schedule_time_from_en'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->format('g:i A');
            $meetings[$key]['meeting_schedule_time_to_en'] = Carbon::parse($meetings[$key]['meeting_schedule_to'])->format('g:i A');
            $meetings[$key]['meeting_date_en']['month'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->format('F');
            $meetings[$key]['meeting_date_en']['day'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->format('l');
            $meetings[$key]['meeting_date_en']['date'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->format('d');
        }
        return $meetings;
    }


    public function getLimitOfMeetingsForOrganization($organiationId){
        $meetings = $this->repository->getLimitOfMeetingsForOrganization($organiationId)->toArray();
        foreach ($meetings as $key => $meeting) {
            setlocale(LC_ALL, 'ar_AE.utf8');
            $meetings[$key]['meeting_schedule_time_from_ar'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->formatLocalized('%I:%M %p');
            $meetings[$key]['meeting_schedule_time_to_ar'] = Carbon::parse($meetings[$key]['meeting_schedule_to'])->formatLocalized('%I:%M %p');
            $meetings[$key]['meeting_date_ar']['month'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->formatLocalized('%B');
            $meetings[$key]['meeting_date_ar']['day'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->formatLocalized('%A');
            $meetings[$key]['meeting_date_ar']['date'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->formatLocalized('%d');
            
            setlocale(LC_ALL, 'en_EN.utf8');
            $meetings[$key]['meeting_schedule_time_from_en'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->format('g:i A');
            $meetings[$key]['meeting_schedule_time_to_en'] = Carbon::parse($meetings[$key]['meeting_schedule_to'])->format('g:i A');
            $meetings[$key]['meeting_date_en']['month'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->format('F');
            $meetings[$key]['meeting_date_en']['day'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->format('l');
            $meetings[$key]['meeting_date_en']['date'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->format('d');
        }
        return $meetings;
    }
    


    public function getLimitOfMeetingsForCommittee($committee_id){
        $meetings = $this->repository->getLimitOfMeetingsForCommittee($committee_id)->toArray();
        foreach ($meetings as $key => $meeting) {
            setlocale(LC_ALL, 'ar_AE.utf8');
            $meetings[$key]['meeting_schedule_time_from_ar'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->formatLocalized('%I:%M %p');
            $meetings[$key]['meeting_schedule_time_to_ar'] = Carbon::parse($meetings[$key]['meeting_schedule_to'])->formatLocalized('%I:%M %p');
            $meetings[$key]['meeting_date_ar']['month'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->formatLocalized('%B');
            $meetings[$key]['meeting_date_ar']['day'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->formatLocalized('%A');
            $meetings[$key]['meeting_date_ar']['date'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->formatLocalized('%d');
            
            setlocale(LC_ALL, 'en_EN.utf8');
            $meetings[$key]['meeting_schedule_time_from_en'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->format('g:i A');
            $meetings[$key]['meeting_schedule_time_to_en'] = Carbon::parse($meetings[$key]['meeting_schedule_to'])->format('g:i A');
            $meetings[$key]['meeting_date_en']['month'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->format('F');
            $meetings[$key]['meeting_date_en']['day'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->format('l');
            $meetings[$key]['meeting_date_en']['date'] = Carbon::parse($meetings[$key]['meeting_schedule_from'])->format('d');
        }
        return $meetings;
    }

    public function getMeetingsStatisticsForUser($userId,$organizationId){
        $meetingsStats =  $this->repository->getMemberMeetingStatistics($userId);
        $statisticsData = $this->adjustStatisticsArray($meetingsStats);
        return $statisticsData;
    }
    private function createDirectory($directory){
        $directory = $this->directoryRepository->create($directory->toArray());

        $directoryBreakDowns[] = ['parent_id' => $directory->id,'level'=>'0'];

        // static rights
        if(isset($directory["parent_directory_id"])){

            $parent = $this->directoryRepository->find($directory["parent_directory_id"]);

            $parentBreakDown = $parent->parentBreakDowns->toArray();
            foreach ($parentBreakDown as $key => $value) {
                # code...
                $directoryBreakDowns[$key+1]['parent_id']= $value['parent_id'];
                $directoryBreakDowns[$key+1]['level']= $value['level']+1;
            }

        }
        $directory->parentBreakDowns()->createMany($directoryBreakDowns);
        return $directory;
    }
    
    public function createMeetingDirectory($meetingId){
        $meeting = $this->repository->find($meetingId);
        $creator_id = $meeting->creator->id;
        $organisers = array_column($meeting->organisers->toArray(), 'user_id');
        $organisers = array_filter($organisers,function($user_id)use($creator_id){
            return $user_id != $creator_id;
        });
        $participants = array_column($meeting->participants->toArray(), 'user_id');
        $participants = array_filter($participants,function($user_id)use($organisers,$creator_id){
            return (!in_array($user_id,$organisers)) && $user_id != $creator_id ;
        });
        $directory = $this->storageHelper->createMeetingDirectory($meeting,$meeting->creator);
        $directory = $this->createDirectory($directory);
        $meeting["directory_id"] = $directory->id;
        $attachments = $meeting->meetingAttachments;
        $systemFiles = array_column($attachments->toArray(), 'file_id');

        $files = [];
        foreach($attachments as $index=> $attachment){
            $file = $this->storageHelper->mapFileFromAttachment($attachment['attachment_name'],$attachment['attachment_url'],$index,$meeting->creator,$directory->id);
            $files[$index] = $file;
        }
        $storageAccess = [];
        foreach($organisers as $index => $organiser){
            $storageAccess[] = ['user_id'=>$organiser,'can_read'=> true , 'can_edit'=> true, 'can_delete'=> true];
        }
        foreach($participants as $index => $participant){
            $storageAccess[] = ['user_id'=>$participant,'can_read'=> true , 'can_edit'=> false, 'can_delete'=> false];
        }
        $directory->storageAccess()->createMany($storageAccess);
        $directory->files()->createMany($files);
        $meetingAgendas = $meeting->meetingAgendas;
        foreach ($meetingAgendas as $key => $meetingAgenda) {
            $agendaDirectory = $this->storageHelper->createMeetingAgendaDirectory($meetingAgenda,$meeting);
            $agendaDirectory = $this->createDirectory($agendaDirectory);
            $meetingAgenda["directory_id"] = $agendaDirectory->id;
            $attachments = $meetingAgenda->agendaAttachments->toArray();
            $agendaFiles = array_column($attachments, 'file_id');
            $systemFiles = array_merge($systemFiles , $agendaFiles);
            $files = [];
            foreach($attachments as $index=> $attachment){
                $file = $this->storageHelper->mapFileFromAttachment($attachment['attachment_name'],$attachment['attachment_url'],$index,$meeting->creator,$directory->id);
                $files[$index] = $file;
            }
            $agendaDirectory->files()->createMany($files);
            $this->meetingAgendaRepository->update($meetingAgenda->toArray(),$meetingAgenda['id']);
        }
        $systemFiles = array_filter($systemFiles,function($file){
            return  $file != null;
        });
        $this->fileRepository->deleteFiles($systemFiles);
        $this->repository->update($meeting->toArray(),$meeting['id']);
    }

    private function updateMeetingApprovals(
        $targetMeeting,
        $meeting,
        $customApprovalId = null,
        $updatedApprovalData = null,
        $lastVersionOfMeeting = null
    ) {
        $targetMeeting->approvals()->delete();
        $newApprovals = [];
        $newAttachmentApprovals = [];
        $indexToDeleteOrUpdate = -1;
        if ($customApprovalId != null) {
            $lastVersionOfMeetingApprovals = $lastVersionOfMeeting->approvals;
            $lastVersionOfMeetingApprovalsIds = array_column($lastVersionOfMeetingApprovals->toArray(), 'id');
            $indexToDeleteOrUpdate = array_search($customApprovalId, $lastVersionOfMeetingApprovalsIds);
        }
        foreach ($meeting->approvals as $key => $meetingApproval) {
            if ($customApprovalId != null && $updatedApprovalData == null && $key == $indexToDeleteOrUpdate) {
                continue;
            }
            if ($customApprovalId != null && $updatedApprovalData != null && $key == $indexToDeleteOrUpdate) {
                $newApprovals[] = $this->UpdateApprovalFromUnpublishedMeeting(
                    $updatedApprovalData,
                    $targetMeeting->id,
                    $meetingApproval
                );
                if ($updatedApprovalData['attachment_url'] != $meetingApproval->attachment_url) {
                    $newAttachmentApprovals[] = $key;
                }
                continue;
            }
            $newApprovalData = [
                'approval_title' => $meetingApproval['approval_title'],
                'committee_id' => $meetingApproval['committee_id'],
                'created_by' => $meetingApproval['created_by'],
                'status_id' => $meetingApproval['status_id'],
                'meeting_id' => $targetMeeting->id,
                'attachment_url' => $meetingApproval['attachment_url'],
                'attachment_name' => $meetingApproval['attachment_name'],
                'organization_id' => $meetingApproval['organization_id'],
                'file_id' => $meetingApproval['file_id'],
                'signature_document_id' => $meetingApproval['signature_document_id'],
            ];

            $newApprovalData['members'] = [];
            foreach ($meetingApproval->members as $approvalMember) {
                $newApprovalData['members'][] = [
                    'member_id' => $approvalMember->member_id,
                    'signature_page_number' => $approvalMember->signature_page_number,
                    'signature_x_upper_left' => $approvalMember->signature_x_upper_left,
                    'signature_y_upper_left' => $approvalMember->signature_y_upper_left,
                    'is_signed' => $approvalMember->is_signed,
                    'signature_comment' => $approvalMember->signature_comment
                ];
            }
            $newApprovals[] = $newApprovalData;
        }
        $targetMeeting->approvals()->createMany($newApprovals);
        foreach ($targetMeeting->approvals as $key => $newApproval) {
            $newApproval->members()->createMany($newApprovals[$key]['members']);
            if (array_search($key, $newAttachmentApprovals)) {
                UploadHelper::convertApprovalDocumentToImages($newApproval);
            }
        }
    }

    public function UpdateApprovalFromUnpublishedMeeting(
        $updatedApprovalData,
        $targetMeetingId,
        $oldMeetingApprovalData
    ) {
        $newApprovalData = [
            'approval_title' => $updatedApprovalData['approval_title'],
            'committee_id' => $updatedApprovalData['committee_id'],
            'meeting_id' => $targetMeetingId,
            'attachment_url' => $updatedApprovalData['attachment_url'],
            'attachment_name' => $updatedApprovalData['attachment_name'],
            'created_by' => $oldMeetingApprovalData['created_by'],
            'status_id' => $oldMeetingApprovalData['status_id'],
            'organization_id' => $oldMeetingApprovalData['organization_id'],
            'file_id' => $oldMeetingApprovalData['file_id'],
            'signature_document_id' => $oldMeetingApprovalData['signature_document_id'],
        ];
        $isAttachChanged = $updatedApprovalData['attachment_url'] != $oldMeetingApprovalData->attachment_url;
        if ($isAttachChanged) {
            $user = $this->securityHelper->getCurrentUser();
            $storageFile = $this->storageHelper->mapSystemFile(
                $updatedApprovalData['attachment_name'],
                $updatedApprovalData['attachment_url'],
                0,
                $user
            );

            $attachmentFile = $this->fileRepository->create($storageFile);
            $newApprovalData['file_id'] = $attachmentFile->id;
        }

        $newApprovalData['members'] = [];
        $oldMemberApproval = ($this->approvalRepository->find($updatedApprovalData['id']))->members;
        foreach ($updatedApprovalData['members'] as $approvalMember) {
            $oldMemberIndex = array_search($approvalMember, array_column($oldMemberApproval->toArray(), 'member_id'));
            if ($oldMemberIndex !== false) {
                $oldMember = ($oldMemberApproval->toArray())[$oldMemberIndex];
                $newApprovalData['members'][] = [
                    'member_id' => $approvalMember,
                    'signature_page_number' => $oldMember['signature_page_number'],
                    'signature_x_upper_left' => $oldMember['signature_x_upper_left'],
                    'signature_y_upper_left' => $oldMember['signature_y_upper_left'],
                    'is_signed' => $oldMember['is_signed'],
                    'signature_comment' => $oldMember['signature_comment'],
                ];
            } else {
                $newApprovalData['members'][] = ['member_id' => $approvalMember];
            }

        }

        return $newApprovalData;
    }

    public function createApprovalsForMeetingVersion($meetingId, array $data)
    {
        // update master meeting
        $masterMeeting = $this->getById($meetingId);

        // update version of meeting
        $versionOfMeeting = $this->repository->getUnpublishedVersionOfMeeting($meetingId);
        if ($versionOfMeeting) {
            $data['meeting_id'] = $versionOfMeeting->id;
            return $data;
        } else {
            // create version of this meeting
            $lastVersionOfMeeting = $this->repository->getLastVersionOfMeeting($meetingId);
            $versionOfMeeting = $this->createVersionOfMeetingFromMasterMeeting($masterMeeting, $lastVersionOfMeeting);
            // update version of meeting
            $data['meeting_id'] = $versionOfMeeting->id;
            return $data;
        }
    }

    public function deleteApprovalsFormMeetingVersion($meetingId, $approvalId)
    {
        // update master meeting
        $masterMeeting = $this->getById($meetingId);

        // update version of meeting
        $versionOfMeeting = $this->repository->getUnpublishedVersionOfMeeting($meetingId);
        if ($versionOfMeeting) {
            $this->approvalRepository->delete($approvalId);
        } else {
            // create version of this meeting
            $lastVersionOfMeeting = $this->repository->getLastVersionOfMeeting($meetingId);
            $this->createVersionOfMeetingFromMasterMeeting(
                $masterMeeting,
                $lastVersionOfMeeting,
                $approvalId,
                null
            );
        }
    }
    private function updateMeetingRecommendations($targetMeeting,$meeting) {
        $targetMeeting->meetingRecommendations()->delete();
        $newRecommendations = [];
        $indexToDeleteOrUpdate = -1;
        foreach ($meeting->meetingRecommendations as $key => $meetingRecommendation) {
            $newRecommendationsData = [
                'recommendation_text' => $meetingRecommendation['recommendation_text'],
                'recommendation_date' => $meetingRecommendation['recommendation_date'],
                'responsible_user' => $meetingRecommendation['responsible_user'],
                'responsible_party' => $meetingRecommendation['responsible_party'],
                'recommendation_status_id' => $meetingRecommendation['recommendation_status_id'],
                'meeting_id' => $targetMeeting->id,
            ];
            $newRecommendations[] = $newRecommendationsData;
        }
        $targetMeeting->meetingRecommendations()->createMany($newRecommendations);
    }

}
