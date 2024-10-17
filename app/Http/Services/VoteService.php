<?php

namespace Services;

use Carbon\Carbon;
use Helpers\StorageHelper;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\File;
use Repositories\VoteParticipantRepository;
use Repositories\CommitteeUserRepository;
use Repositories\FileRepository;
use Repositories\MeetingRepository;
use Repositories\VoteRepository;
use Repositories\VoteResultRepository;
use \Illuminate\Database\Eloquent\Model;

class VoteService extends BaseService
{
    private $meetingRepository;
    private $voteResultRepository;
    private $committeeUserRepository;
    private $attachmentRepository;
    private VoteParticipantRepository $voteParticipantRepository;
    private $storageHelper, $fileRepository;

    public function __construct(
        DatabaseManager $database,
        VoteRepository $repository,
        MeetingRepository $meetingRepository,
        CommitteeUserRepository $committeeUserRepository,
        StorageHelper $storageHelper,
        FileRepository $fileRepository,
        VoteResultRepository $voteResultRepository,
        VoteParticipantRepository $voteParticipantRepository
    ) {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->meetingRepository = $meetingRepository;
        $this->committeeUserRepository = $committeeUserRepository;
        $this->voteResultRepository = $voteResultRepository;
        $this->storageHelper = $storageHelper;
        $this->fileRepository = $fileRepository;
        $this->voteParticipantRepository = $voteParticipantRepository;
    }

    public function prepareCreate(array $data)
    {
        $voteResults = [];
        $voteUsersIds = [];

        if (isset($data['vote_users_ids'])) {
            $voteUsersIds = $data['vote_users_ids'];
            unset($data['vote_users_ids']);
        }
        // create vote
        $vote = $this->repository->create($data);

        if (count($voteUsersIds) > 0) {
            foreach ($voteUsersIds as $key => $value) {
                $voteResults[$key]['user_id'] = $value;
                $voteResults[$key]['vote_id'] = $vote->id;
                $voteResults[$key]['vote_status_id'] = config('voteStatuses.notDecided');
                $voteResults[$key]['decision_weight'] = $this->committeeUserRepository->checkIfUserIsHeadOfCommittee($value, $vote->committee_id) ? config('decisionWeight.HeadDecisionWeight') : config('decisionWeight.participantDecisionWeight');
            }
            // // add creator
            // if(!in_array($vote->creator_id,$voteUsersIds)) {
            //     $isHeadOfCommittee = $this->committeeUserRepository->checkIfUserIsHeadOfCommittee($vote->creator_id,$vote->committee_id)? config('decisionWeight.HeadDecisionWeight') : config('decisionWeight.participantDecisionWeight');
            //     $voteResults[] = ['user_id' => $vote->creator_id,'vote_id' => $vote->id,
            //         'vote_status_id' => config('voteStatuses.notDecided'),'decision_weight' => $isHeadOfCommittee];
            // }
            // create document reviewers
            $vote->voteResults()->createMany($voteResults);
        }

        if (isset($data['attachments'])) {
            foreach ($data['attachments'] as $key => $attachment) {
                $storageFile = $this->storageHelper->mapSystemFile($attachment['attachment_name'], $attachment['attachment_url'], $key, $vote->creator);
                $attachmentFile = $this->fileRepository->create($storageFile);
                $data['attachments'][$key]['file_id'] = $attachmentFile->id;
            }
            $vote->attachments()->createMany($data['attachments']);
            unset($data['attachments']);
        }
        return $vote;
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $voteResults = [];
        $voteUsersIds = [];
        $fileIds = [];

        if (isset($data['vote_users_ids'])) {
            $voteUsersIds = $data['vote_users_ids'];
            // $voteUsersIds[] = $model->creator_id;
            // update vote voters
            $oldVotersIds = array_column($model->voters->toArray(), 'id');

            $remainingOldUsers = array_intersect($voteUsersIds, $oldVotersIds);
            if (count($remainingOldUsers) > 0 && $data['resetDocument']) {
                unset($data['resetDocument']);
                $data['vote_result_status_id'] = config('voteStatuses.notDecided');
                $model->voteResults()->whereIn('user_id', $remainingOldUsers)->update(['is_signed' => null, 'signature_comment' => null, 'vote_status_id' => config('voteStatuses.notDecided')]);
            }

            $deletedVotersIds = array_diff($oldVotersIds, $voteUsersIds);
            $newVotersIds = array_diff($voteUsersIds, $oldVotersIds);

            foreach ($newVotersIds as $key => $value) {
                $voteResults[$key]['user_id'] = $value;
                $voteResults[$key]['vote_id'] = $model->id;
                $voteResults[$key]['vote_status_id'] = config('voteStatuses.notDecided');
                $voteResults[$key]['decision_weight'] = $this->committeeUserRepository->checkIfUserIsHeadOfCommittee($value, $data['committee_id']) ? config('decisionWeight.HeadDecisionWeight') : config('decisionWeight.participantDecisionWeight');
            }
            // create new voters
            if (count($voteResults) > 0) {
                $model->voteResults()->createMany($voteResults);
            }
            // delete voters
            if (count($deletedVotersIds) > 0) {
                $model->voteResults()->whereIn('user_id', $deletedVotersIds)->delete();
            }
            unset($data['vote_users_ids']);
        }
        foreach ($model->attachments as $voteAttachment) {
            $path = public_path() . '/uploads/attachments/' . $voteAttachment->id;
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
            }
            if ($voteAttachment->file_id) {
                $fileIds[] = $voteAttachment->file_id;
            }
        }
        if (isset($data['attachments'])) {
            $this->fileRepository->deleteFiles($fileIds);
            $model->attachments()->delete();
            foreach ($data['attachments'] as $key => $attachment) {
                $storageFile = $this->storageHelper->mapSystemFile($attachment['attachment_name'], $attachment['attachment_url'], $key, $model->creator);
                $attachmentFile = $this->fileRepository->create($storageFile);
                $data['attachments'][$key]['file_id'] = $attachmentFile->id;
            }
            $model->attachments()->createMany($data['attachments']);
            unset($data['attachments']);
        }

        return $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        return $this->repository->delete($id);
    }

    public function getMeetingVotes($meetingId)
    {
        return $this->repository->getMeetingVotes($meetingId);
    }

    public function updateMeetingVotes($meetingVotes, $meetingId, $isCreateNewVersion)
    {
        $numberOfUpdateVotes = 0;
        $haveNewVotesAdded = false;
        $haveDeletedVotes = false;
        $meeting = $this->meetingRepository->find($meetingId);
        $votesOfMeeting = $meeting->meetingVotes()->orderBy('id')->get()->toArray();

        if (count($votesOfMeeting) == count($meetingVotes)) {
            $numberOfUpdateVotes = count($votesOfMeeting);
        } else if (count($votesOfMeeting) < count($meetingVotes)) {
            $numberOfUpdateVotes = count($votesOfMeeting);
            $haveNewVotesAdded = true;
        } else if (count($votesOfMeeting) > count($meetingVotes)) {
            $numberOfUpdateVotes = count($meetingVotes);
            $haveDeletedVotes = true;
        }

        foreach ($meetingVotes as $index => $meetingVote) {
            if ($isCreateNewVersion) {
                $lastMeetingVersion = $this->meetingRepository->find($meetingVote['meeting_id']);
                $lastMeetingVersionAgendasIds = array_column($lastMeetingVersion->meetingAgendas->toArray(), 'id');
                $key = array_search($meetingVote['agenda_id'], $lastMeetingVersionAgendasIds);
                $meetingAgendas = $meeting->meetingAgendas->toArray();
                $meetingVote['agenda_id'] = $meetingAgendas[$key]['id'];
            }
            $meetingVote['meeting_id'] = $meetingId;
            unset($meetingVote['vote_results']);
            unset($meetingVote['vote_schedule_from_date']);
            unset($meetingVote['vote_schedule_to_date']);
            unset($meetingVote['vote_schedule_from_time']);
            unset($meetingVote['vote_schedule_to_time']);
            if ($index < $numberOfUpdateVotes) {
                unset($meetingVote['vote_result_status_id']);
                $this->repository->update($meetingVote, $votesOfMeeting[$index]['id']);
                $voteId = $votesOfMeeting[$index]['id'];
            } else {
                $createdMeetingVote = $this->repository->create($meetingVote);
                $voteId = $createdMeetingVote->id;
            }
            $dbVoteParticipant = $this->voteParticipantRepository->where('vote_id', $voteId)->get();
            foreach ($dbVoteParticipant as $dbParticipant) {
                $dbParticipant->delete();
            }
            foreach ($meetingVote['vote_participants'] as $index => $member) {
                $member["vote_id"] = $voteId;
                unset($member["name"]);
                $this->voteParticipantRepository->updateOrCreate($member);
            }
        }
        if ($haveDeletedVotes) { // delete meeting agendas
            for ($i = $numberOfUpdateVotes; $i < count($votesOfMeeting); $i++) {
                $this->voteParticipantRepository->where('vote_id', $votesOfMeeting[$i]['id'])->delete();
                $targetMeetingVote = $this->repository->find($votesOfMeeting[$i]['id']);
                $this->repository->delete($targetMeetingVote->id);
            }
        }
        return $this->repository->getMeetingVotes($meetingId);
    }

    public function getMeetingVoteDetails($voteId)
    {
        return $this->repository->getMeetingVoteDetails($voteId);
    }

    public function getPagedList($filter, $userId)
    {
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "votes.id";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        $data = $this->repository->getPagedMeetingVotes($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $userId);
        $data->Results = $this->recalculateVoteResultsIfYesAndNoVotesAreEquals($data->Results);
        return $data;
    }

    public function addMeetingVotes($meetingVotes, $versionOfMeeting, $masterMeeting)
    {
        $meetingVoteVersion = [];
        $votes = [];
        foreach ($meetingVotes as $index => $meetingVote) {
            foreach ($meetingVote['vote_participants'] as $key => $voteParticipant) {
                $dataParticipant[$key]['user_id'] = $voteParticipant['user_id'];
                $dataParticipant[$key]['meeting_guest_id'] = $voteParticipant['meeting_guest_id'];
            }
            if ($versionOfMeeting) {
                $versionMeetingAgendas = $versionOfMeeting->meetingAgendas->toArray();
                if (count($versionMeetingAgendas) > 0) {
                    $masterMeetingAgendas = $masterMeeting->meetingAgendas->toArray();
                    $masterMeetingAgendasIds = array_column($masterMeetingAgendas, 'id');
                    $key = array_search($meetingVote['agenda_id'], $masterMeetingAgendasIds);
                    $meetingVoteVersion = $meetingVote;
                    $meetingVoteVersion['meeting_id'] = $versionOfMeeting->id;
                    $meetingVoteVersion['agenda_id'] = $versionMeetingAgendas[$key ? $key : 0]['id'];
                    $voteVersion = $this->repository->create($meetingVoteVersion);
                    $voteVersion->voteParticipants()->createMany($dataParticipant);
                }
            }
            $masterVote = $this->repository->create($meetingVote);
            $masterVote->voteParticipants()->createMany($dataParticipant);
            $this->addVoteResultsForVote($masterVote);
            $votes[] = $masterVote;
        }
        return ['meeting_decision' => $this->repository->getMeetingVotes($masterMeeting->id), 'created_decisions' => $votes];
    }

    public function getCountOfVotesThatUsedDecisionType($decisionTypeId)
    {
        return $this->repository->getCountOfVotesThatUsedDecisionType($decisionTypeId);
    }

    public function checkUserCanVote($decisionId)
    {
        return $this->repository->checkUserCanVote($decisionId)->can_vote;
    }

    public function recalculateVoteResultsIfYesAndNoVotesAreEquals($results)
    {
        foreach ($results as $key => $vote) {
            if ($vote->yes_votes == $vote->no_votes) {
                $voteResults = $this->voteResultRepository->countVoteResultsWithWeight($vote->id);
                if (isset($voteResults[0])) {
                    $vote->yes_votes = $voteResults[0]['yes_votes'] ? $voteResults[0]['yes_votes'] : 0;
                    $vote->no_votes = $voteResults[0]['no_votes'] ? $voteResults[0]['no_votes'] : 0;
                    $vote->abstained_votes = $voteResults[0]['abstained_votes'];
                }
            }
        }
        return $results;
    }

    public function getCircularDecicion($id, $userId, $timeZone)
    {
        $circularDecicion = $this->repository->getCircularDecicion($id, $userId);
        if ($circularDecicion) {
            $currentDate = Carbon::now()->addHours($timeZone->diff_hours);
            $creationDate = Carbon::parse($circularDecicion->creation_date);
            if ($creationDate->diffInDays($currentDate) == 0) {
                $circularDecicion["creation_same_day"] = true;
                $circularDecicion["creation_hour_diff"] = $creationDate->diff($currentDate)->format('%H');
                $circularDecicion["creation_minute_diff"] = $creationDate->diff($currentDate)->format('%I');
            }
            $endDate = Carbon::parse($circularDecicion->vote_schedule_to);
            if ($endDate >= $currentDate) {
                $circularDecicion["show_due_date"] = true;
                if ($endDate->diffInMonths($currentDate) > 0) {
                    $circularDecicion["due_months"] = $endDate->diffInMonths($currentDate);
                } else if ($endDate->diffInDays($currentDate) > 0) {
                    $circularDecicion["due_days"] = $endDate->diffInDays($currentDate);
                } else if ($endDate->diffInHours($currentDate) > 0) {
                    $circularDecicion["due_hours"] = $endDate->diffInHours($currentDate);
                } else {
                    $circularDecicion["due_minutes"] = $endDate->diffInMinutes($currentDate);
                }
            }
        }

        return $circularDecicion;
    }

    public function getVoteDetails($voteId)
    {
        return $this->repository->getVoteDetails($voteId);
    }

    public function addVoteResultsForVote($vote)
    {
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

    public function getStartedCircularDecisions()
    {
        return $this->repository->getStartedCircularDecisions();
    }

    public function getDecisionDataWithCanSendNotificationFlag($decisionId)
    {
        return $this->repository->getDecisionDataWithCanSendNotificationFlag($decisionId);
    }

    public function getLimitOfMeetingDecisionsForUser($userId)
    {
        return $this->repository->getLimitOfMeetingDecisionsForUser($userId);
    }

    public function getLimitOfMeetingDecisionsForOrganization($organizationId)
    {
        return $this->repository->getLimitOfMeetingDecisionsForOrganization($organizationId);
    }

    public function getMeetingDecisionsResultStatusStatisticsForOrganization($organizationId, $voteResultStatusId)
    {
        return $this->repository->getMeetingDecisionsResultStatusStatisticsForOrganization($organizationId, $voteResultStatusId)->count();
    }

    public function getMeetingDecisionsResultStatusStatisticsForCommittee($committeeId, $voteResultStatusId)
    {
        return $this->repository->getMeetingDecisionsResultStatusStatisticsForCommittee($committeeId, $voteResultStatusId)->count();
    }
    public function getLimitOfMeetingDecisionsForCommitee($committeeId)
    {
        return $this->repository->getLimitOfMeetingDecisionsForCommitee($committeeId);
    }

    public function getMeetingDecisionsResultStatusStatisticsForUser($userId, $voteResultStatusId)
    {
        return $this->repository->getMeetingDecisionsResultStatusStatisticsForUser($userId, $voteResultStatusId)->count();
    }

    public function getCircularDecisionsResultStatusStatisticsForUser($userId, $voteResultStatusId)
    {
        return $this->repository->getCircularDecisionsResultStatusStatisticsForUser($userId, $voteResultStatusId)->count();
    }

    public function getCircularDecisionsResultStatusStatisticsForOrganization($organizationId, $voteResultStatusId)
    {
        return $this->repository->getCircularDecisionsResultStatusStatisticsForOrganization($organizationId, $voteResultStatusId)->count();
    }

    public function getCircularDecisionsResultStatusStatisticsForCommittee($committeeId, $voteResultStatusId)
    {
        return $this->repository->getCircularDecisionsResultStatusStatisticsForCommittee($committeeId, $voteResultStatusId)->count();
    }

    public function getLimitOfCircularDecisionsForUser($userId)
    {
        return $this->repository->getLimitOfCircularDecisionsForUser($userId);
    }

    public function getLimitOfCircularDecisionsForOrganization($organizationId)
    {
        return $this->repository->getLimitOfCircularDecisionsForOrganization($organizationId);
    }

    public function getLimitOfCircularDecisionsForCommittee($committee_id)
    {
        return $this->repository->getLimitOfCircularDecisionsForCommittee($committee_id);
    }

    public function getCircularDecisionsHaveEndDateInThePast()
    {
        return $this->repository->getCircularDecisionsHaveEndDateInThePast();
    }

    public function createStorageAccessAndFilesOfDirectory($circularDecision, $directory)
    {
        $storageAccess = [];
        $files = [];
        $systemFiles = array_column($circularDecision->attachments->toArray(), 'file_id');
        foreach ($circularDecision->voters as $index => $voter) {
            $storageAccess[] = ['user_id' => $voter->id, 'can_read' => true, 'can_edit' => false, 'can_delete' => false];
        }
        foreach ($circularDecision->attachments as $index => $attachment) {
            $file = $this->storageHelper->mapFileFromAttachment($attachment['attachment_name'], $attachment['attachment_url'], $index, $circularDecision->creator, $directory->id);
            $files[$index] = $file;
        }
        $this->fileRepository->deleteFiles($systemFiles);
        $directory->storageAccess()->createMany($storageAccess);
        $directory->files()->createMany($files);
    }

    public function getByDocumentId($documentId)
    {
        return $this->repository->findByField('document_id', $documentId)->first();
    }
}
