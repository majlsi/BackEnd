<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Helpers\EventHelper;
use Helpers\NotificationHelper;
use Helpers\SecurityHelper;
use Helpers\SignatureHelper;
use Helpers\StorageHelper;
use Helpers\VoteHelper;
use Helpers\VoteResultHelper;
use Illuminate\Http\Request;
use Jobs\NewCircularDecisionCreatedEmail;
use Models\Vote;
use Models\VoteResult;
use PDF;
use Services\AttachmentService;
use Services\CommitteeUserService;
use Services\DirectoryService;
use Services\MeetingGuestService;
use Services\MeetingService;
use Services\NotificationService;
use Services\TaskManagementService;
use Services\VoteResultService;
use Services\VoteService;
use Validator;

class VoteController extends Controller
{

    private $voteService;
    private $securityHelper;
    private $voteResultService;
    private $meetingService;
    private $voteResultHelper;
    private $voteHelper;
    private $attachmentService;
    private $committeeUserService;
    private $notificationService;
    private $notificationHelper;
    private $taskManagementService;
    private $storageHelper;
    private $directoryService;
    private $signatureHelper;
    private EventHelper $eventHelper;
    private MeetingGuestService $meetingGuestService;

    public function __construct(VoteService $voteService, MeetingService $meetingService, VoteResultService $voteResultService,
        SecurityHelper $securityHelper, VoteResultHelper $voteResultHelper, EventHelper $eventHelper, VoteHelper $voteHelper,
        AttachmentService $attachmentService, CommitteeUserService $committeeUserService, StorageHelper $storageHelper,
        DirectoryService $directoryService, SignatureHelper $signatureHelper,
        NotificationService $notificationService,
        NotificationHelper $notificationHelper,
        TaskManagementService $taskManagementService,
        MeetingGuestService $meetingGuestService
    ) {
        $this->voteService = $voteService;
        $this->securityHelper = $securityHelper;
        $this->voteResultService = $voteResultService;
        $this->meetingService = $meetingService;
        $this->voteResultHelper = $voteResultHelper;
        $this->eventHelper = $eventHelper;
        $this->voteHelper = $voteHelper;
        $this->attachmentService = $attachmentService;
        $this->committeeUserService = $committeeUserService;
        $this->notificationService = $notificationService;
        $this->notificationHelper = $notificationHelper;
        $this->taskManagementService = $taskManagementService;
        $this->storageHelper = $storageHelper;
        $this->directoryService = $directoryService;
        $this->signatureHelper = $signatureHelper;
        $this->meetingGuestService = $meetingGuestService;
    }

    public function getMeetingVotes(int $meetingId)
    {
        return response()->json($this->voteService->getMeetingVotes($meetingId), 200);
    }

    public function setMeetingVotes(Request $request, int $meetingId)
    {
        $message = [];
        $isCreateNewVersion = false;
        $data = array_map(function ($el) {
            $el['vote_type_id'] = config('voteTypes.duringMeeting');
            return $el;
        }, $request->all());

        $validator = Validator::make($data, Vote::rules('save'));

        if ($validator->fails()) {
            $message = array_merge($message, $validator->errors()->all());
            return response()->json(['error' => $message], 400);
        }

        // check max number of votes per agenda
        $agendaVotesNumber = [];
        foreach ($data as $key => $vote) {
            if (isset($data[$key]['vote_schedule_to'])) {
                $data[$key]['vote_schedule_to'] = new Carbon($data[$key]['vote_schedule_to']['year'] . '-' . $data[$key]['vote_schedule_to']['month'] . '-' . $data[$key]['vote_schedule_to']['day'] . ' ' . $data[$key]['vote_schedule_to']['hour'] . ':' . $data[$key]['vote_schedule_to']['minute'] . ':' . $data[$key]['vote_schedule_to']['second']);
            }
            if (isset($data[$key]['vote_schedule_from'])) {
                $data[$key]['vote_schedule_from'] = new Carbon($data[$key]['vote_schedule_from']['year'] . '-' . $data[$key]['vote_schedule_from']['month'] . '-' . $data[$key]['vote_schedule_from']['day'] . ' ' . $data[$key]['vote_schedule_from']['hour'] . ':' . $data[$key]['vote_schedule_from']['minute'] . ':' . $data[$key]['vote_schedule_from']['second']);
            }
            if (isset($data[$key]['decision_due_date'])) {
                $data[$key]['decision_due_date'] = new Carbon($data[$key]['decision_due_date']['year'] . '-' . $data[$key]['decision_due_date']['month'] . '-' . $data[$key]['decision_due_date']['day'] . ' 00:00:00');
            }
            if (isset($vote['agenda_id']) && !isset($vote['add_from_presentation'])) {
                if (isset($agendaVotesNumber[$vote['agenda_id']])) {
                    ++$agendaVotesNumber[$vote['agenda_id']];
                } else {
                    $agendaVotesNumber[$vote['agenda_id']] = 1;
                }
            } else if (isset($vote['agenda_id']) && isset($vote['add_from_presentation'])) {
                $meeting = $this->meetingService->getById($meetingId);
                $agendaVotesNumber[$vote['agenda_id']] = 1 + $meeting->meetingVotes->where('agenda_id', $vote['agenda_id'])->count();
            }
            $data[$key]['vote_result_status_id'] = config('voteResultStatuses.noVotesYet');
        }

        if (count($agendaVotesNumber) > 0 && max($agendaVotesNumber) > 5) {
            $message[0][] = ["message" => 'Max number of decisions per agenda is 5 decisions',
                "message_ar" => 'اقصى عدد يمكن استخدام فيه جدول الاعمال للقرار هو 5 مرات'];
        }

        if (!empty($message)) {
            return response()->json(['error' => $message], 400);
        }
        $versionOfMeeting = $this->meetingService->getUnpublishedVersionOfMeeting($meetingId);
        $masterMeeting = $this->meetingService->getById($meetingId);
        if (isset($vote['add_from_presentation'])) {
            $lastVersionOfMeeting = $this->meetingService->getLastVersionOfMeeting($meetingId);
            $mettingDecisionsData = $this->voteService->addMeetingVotes($data, $lastVersionOfMeeting, $masterMeeting);
            $meetingVotes = $mettingDecisionsData['meeting_decision'];
            $createdDecisions = $mettingDecisionsData['created_decisions'];
            $this->eventHelper->fireEvent([], 'App\Events\MeetingDataChangedEvent');
            // send notification for create new vote
            $this->sendNotificationWhenCreateMeetingDecisions($createdDecisions);
        } else {
            if (!$versionOfMeeting) {
                $isCreateNewVersion = true;
                $lastVersionOfMeeting = $this->meetingService->getLastVersionOfMeeting($meetingId);
                $versionOfMeeting = $this->meetingService->createVersionOfMeetingFromMasterMeeting($masterMeeting, $lastVersionOfMeeting);
            }
            $meetingVotes = $this->voteService->updateMeetingVotes($data, $versionOfMeeting->id, $isCreateNewVersion);
        }

        //$this->eventHelper->fireEvent([], 'App\Events\MeetingDataChangedEvent');
        return response()->json(['meeting_votes' => $meetingVotes, 'meeting_version_id' => $versionOfMeeting ? $versionOfMeeting->id : null], 200);

    }

    public function destroy($meetingId, $voteId)
    {
        $vote = $this->voteService->getById($voteId);
        if ($vote && $vote->tasks->count() == 0) {
            $meeting = $this->meetingService->getById($vote->meeting_id);
            if ($meeting && $meeting->related_meeting_id && $meeting->is_published) {
                $this->meetingService->updateMeetingIsPublishedFlag($meeting->id);
            }
            $this->voteService->delete($voteId);
            //$this->eventHelper->fireEvent([], 'App\Events\MeetingDataChangedEvent');
            return response()->json(['message' => 'Decision deleted successfully'], 200);
        }
        return response()->json(['error' => 'Data can\'t deleted'], 400);
    }

    public function changeVoteResultToYes(Request $request, int $meetingId, int $voteId)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        /** check if create or update */
        $voteResult = $this->voteResultService->checkVoteBefore($user, $voteId);
        $meeting = $this->meetingService->getById($meetingId);
        if (in_array($meeting->meeting_status_id, [config('meetingStatus.cancel'), config('meetingStatus.end')])) {
            return response()->json(['error' => 'Can\'t update decision result', 'error_ar' => 'لا يمكنك اخذ القرار'], 400);
        }
        $voteDetails = $this->voteService->getMeetingVoteDetails($voteId);
        if (!$voteDetails->is_voted_now) {
            return response()->json(['error' => 'Can\'t update decision result, decision period time is passed', 'error_ar' => 'لا يمكنك اخذ القرار, فترة اخذ القرار انقضت'], 400);

        }
        $isHeadOfCommittee = $this->committeeUserService->checkIfUserIsHeadOfCommittee($user->id, $meeting->committee_id);
        $updateData = [];
        if(isset($user->meeting_id)){
            $guest = $this->meetingGuestService->getGuestByMeetingIdAndEmail($user->meeting_id, $user->email);
            $updateData = ['meeting_guest_id' => $guest->id];
        } else {
            $updateData = ['user_id' => $user->id];
        }
        $voteResultData = $this->voteResultHelper->prepareDataForVoteResult($voteId, config('voteStatuses.yes'), $isHeadOfCommittee, $updateData);
        if ($voteResult) {
            /** update */
            if ($user && $voteResult && ($user->id == $voteResult->user_id || $user->meeting_guest_id == $voteResult->meeting_guest_id)) {
                $validator = Validator::make($voteResultData, VoteResult::rules('update', $voteResult->id));
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()], 400);
                }
                $voteResultUpdate = $this->voteResultService->update($voteResult->id, $voteResultData);
                $vote = $this->voteResultService->getById($voteResultUpdate->id);
            }
        } else {
            /** create */
            $validator = Validator::make($voteResultData, VoteResult::rules('save'));
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
            $vote = $this->voteResultService->create($voteResultData);

        }
        // update decison result status
        //UpdateStatusOfVote::dispatch($voteId);
        $voteCountResult = $this->voteResultService->countVoteResults($voteId);
        $data = $this->voteHelper->prepareVoteResultStatus($voteCountResult);
        $this->voteService->update($voteId, $data);
        $voteCountResult = $this->voteResultService->countVoteResults($voteId);
        $this->eventHelper->fireEvent($meeting, 'App\Events\ChangeVoteEvent');
        // fire notification for change vote status
        $notificationData = $this->notificationHelper->prepareNotificationDataForMeetingDecision($voteDetails, $user, config('meetingDecision.addVote'), ['vote_status_id' => config('voteStatuses.yes')]);
        $this->notificationService->sendNotification($notificationData);
        return response()->json(['vote' => $vote, 'vote_results' => $voteCountResult], 200);

    }

    public function changeVoteResultToNo(Request $request, int $meetingId, int $voteId)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        /** check if create or update */
        $voteResult = $this->voteResultService->checkVoteBefore($user, $voteId);
        $meeting = $this->meetingService->getById($meetingId);
        if (in_array($meeting->meeting_status_id, [config('meetingStatus.cancel'), config('meetingStatus.end')])) {
            return response()->json(['error' => 'Can\'t update decision result', 'error_ar' => 'لا يمكنك  اخذ قرار'], 400);
        }
        $voteDetails = $this->voteService->getMeetingVoteDetails($voteId);
        if (!$voteDetails->is_voted_now) {
            return response()->json(['error' => 'Can\'t update decision result, decision period time is passed', 'error_ar' => 'لا يمكنك اخذ قرار, فترة اخذ القرار انقضت'], 400);

        }
        $isHeadOfCommittee = $this->committeeUserService->checkIfUserIsHeadOfCommittee($user->id, $meeting->committee_id);
        $updateData = [];
        if (isset($user->meeting_id)) {
            $guest = $this->meetingGuestService->getGuestByMeetingIdAndEmail($user->meeting_id, $user->email);
            $updateData = ['meeting_guest_id' => $guest->id];
        } else {
            $updateData = ['user_id' => $user->id];
        }
        $voteResultData = $this->voteResultHelper->prepareDataForVoteResult($voteId, config('voteStatuses.no'), $isHeadOfCommittee, $updateData);
        if ($voteResult) {
            /** update */
            if ($user && $voteResult && ($user->id == $voteResult->user_id || $user->meeting_guest_id == $voteResult->meeting_guest_id)) {
                $validator = Validator::make($voteResultData, VoteResult::rules('update', $voteResult->id));
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()], 400);
                }
                $voteResultUpdate = $this->voteResultService->update($voteResult->id, $voteResultData);
                $vote = $this->voteResultService->getById($voteResultUpdate->id);
            }
        } else {
            /** create */
            $validator = Validator::make($voteResultData, VoteResult::rules('save'));
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
            $vote = $this->voteResultService->create($voteResultData);

        }
        // update decison result status
        // UpdateStatusOfVote::dispatch($voteId);
        $voteCountResult = $this->voteResultService->countVoteResults($voteId);
        $data = $this->voteHelper->prepareVoteResultStatus($voteCountResult);
        $this->voteService->update($voteId, $data);
        $voteCountResult = $this->voteResultService->countVoteResults($voteId);
        $voteCountResult = $this->voteResultService->countVoteResults($voteId);
        $this->eventHelper->fireEvent($meeting, 'App\Events\ChangeVoteEvent');
        // fire notification for change vote status
        $notificationData = $this->notificationHelper->prepareNotificationDataForMeetingDecision($voteDetails, $user, config('meetingDecision.addVote'), ['vote_status_id' => config('voteStatuses.no')]);
        $this->notificationService->sendNotification($notificationData);
        return response()->json(['vote' => $vote, 'vote_results' => $voteCountResult], 200);

    }

    public function changeVoteResultToAbstained(Request $request, int $meetingId, int $voteId)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        /** check if create or update */
        $voteResult = $this->voteResultService->checkVoteBefore($user, $voteId);
        $meeting = $this->meetingService->getById($meetingId);
        if (in_array($meeting->meeting_status_id, [config('meetingStatus.cancel'), config('meetingStatus.end')])) {
            return response()->json(['error' => 'Can\'t update decision result', 'error_ar' => 'لا يمكنك اخذ قرار'], 400);
        }
        $voteDetails = $this->voteService->getMeetingVoteDetails($voteId);
        if (!$voteDetails->is_voted_now) {
            return response()->json(['error' => 'Can\'t update decision result, decision period time is passed', 'error_ar' => 'لا يمكنك اخذ القرار, فترة اخذ القرار انقضت'], 400);

        }
        $isHeadOfCommittee = $this->committeeUserService->checkIfUserIsHeadOfCommittee($user->id, $meeting->committee_id);
        $updateData = [];
        if (isset($user->meeting_id)) {
            $guest = $this->meetingGuestService->getGuestByMeetingIdAndEmail($user->meeting_id, $user->email);
            $updateData = ['meeting_guest_id' => $guest->id];
        } else {
            $updateData = ['user_id' => $user->id];
        }
        $voteResultData = $this->voteResultHelper->prepareDataForVoteResult($voteId, config('voteStatuses.abstained'), $isHeadOfCommittee, $updateData);
        if ($voteResult) {
            /** update */
            if ($user && $voteResult && ($user->id == $voteResult->user_id || $user->meeting_guest_id == $voteResult->meeting_guest_id)) {
                $validator = Validator::make($voteResultData, VoteResult::rules('update', $voteResult->id));
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()], 400);
                }
                $voteResultUpdate = $this->voteResultService->update($voteResult->id, $voteResultData);
                $vote = $this->voteResultService->getById($voteResultUpdate->id);
            }
        } else {
            /** create */
            $validator = Validator::make($voteResultData, VoteResult::rules('save'));
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
            $vote = $this->voteResultService->create($voteResultData);

        }
        // update decison result status
        //UpdateStatusOfVote::dispatch($voteId);
        $voteCountResult = $this->voteResultService->countVoteResults($voteId);
        $data = $this->voteHelper->prepareVoteResultStatus($voteCountResult);
        $this->voteService->update($voteId, $data);
        $voteCountResult = $this->voteResultService->countVoteResults($voteId);
        $voteCountResult = $this->voteResultService->countVoteResults($voteId);
        $this->eventHelper->fireEvent($meeting, 'App\Events\ChangeVoteEvent');
        // fire notification for change vote status
        $notificationData = $this->notificationHelper->prepareNotificationDataForMeetingDecision($voteDetails, $user, config('meetingDecision.addVote'), ['vote_status_id' => config('voteStatuses.abstained')]);
        $this->notificationService->sendNotification($notificationData);
        return response()->json(['vote' => $vote, 'vote_results' => $voteCountResult], 200);

    }

    public function startVote(Request $request, int $meetingId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        $meetingOrganiserIds = array_column($meeting->meetingOrganisers->toArray(), 'id');
        if (in_array($user->id, $meetingOrganiserIds) || $user->id == $meeting->created_by) {
            $this->voteService->update($request->vote_id, ["is_started" => 1]);
            $this->eventHelper->fireEvent($meeting, 'App\Events\StartVoteEvent');
        } else {
            return response()->json(['error' => 'Can\'t start decision', 'error_ar' => 'لا يمكنك بدأ اخذ القرار'], 400);
        }
    }

    public function endVote(Request $request, int $meetingId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        $meetingOrganiserIds = array_column($meeting->meetingOrganisers->toArray(), 'id');
        if (in_array($user->id, $meetingOrganiserIds) || $user->id == $meeting->created_by) {
            $this->voteService->update($request->vote_id, ["is_started" => 0]);
            $this->eventHelper->fireEvent($meeting, 'App\Events\EndVoteEvent');
        } else {
            return response()->json(['error' => 'Can\'t end decision', 'error_ar' => 'لا يمكنك انهاء القرار'], 400);
        }
    }

    public function getPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        $filter->SearchObject['is_meeting_vote'] = true;
        return response()->json($this->voteService->getPagedList($filter, $user->id), 200);
    }

    public function storeCircularDecicion(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $decisionData = $this->voteHelper->prepareCircularDecisionData($data, $user, true, null);

        $validator = Validator::make($decisionData, Vote::rules('save-circular-decision'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $decision = $this->voteService->create($decisionData);
        $decision = $this->voteService->getDecisionDataWithCanSendNotificationFlag($decision->id);
        if ($decision->can_send_notification) {
            $notificationData = $this->notificationHelper->prepareNotificationDataForCircularDecision($decision, $decision->creator, config('decisionNotification.startDecision'));
            $this->notificationService->sendNotification($notificationData);
            NewCircularDecisionCreatedEmail::dispatch($decision, null);
        }

        $created = $this->createDecisionDocument($decision, $user);
        if ($created) {
            return response()->json($decision, 200);
        } else {
            return response()->json(['error' => 'something went wrong, you can try again later! ', 'error_ar' => 'حدث خطأ ما، يمكنك المحاولة لاحقاَ'], 400);
        }

    }

    private function createDecisionDocument($decision, $user)
    {
        try {

            if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.mjlsi')) {
                $mailFolderName = 'pdf';
            } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.gaft')) {
                $mailFolderName = 'pdf-gaft';
            } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.eca')) {
                $mailFolderName = 'pdf-eca';
            } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.lcgpa')) {
                $mailFolderName = 'pdf-lcgpa';
            } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.sadu')) {
                $mailFolderName = 'pdf-sadu';
            } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.swcc')) {
                $mailFolderName = 'pdf-swcc';
            }
            $data['voters'] = $decision->voters;

             $decision->voters->map(function ($item, $key) {
                if($item->nickname && $item->nickname["nickname_en"]){
                    $item['nickname_en']=$item->nickname["nickname_en"];
                }
                if($item->nickname && $item->nickname["nickname_ar"]){
                    $item['nickname_ar']=$item->nickname["nickname_ar"];
                }
                if($item->userTitle && $item->userTitle["user_title_name_en"]){    
                    $item['user_title_en']=$item->userTitle["user_title_name_en"];
                }
                if($item->userTitle && $item->userTitle["user_title_name_ar"]){ 
                    $item['user_title_ar']=$item->userTitle["user_title_name_ar"];
                }
                if($item->jobTitle && $item->jobTitle["job_title_name_en"]){
                    $item['job_title_en']=$item->jobTitle["job_title_name_en"];
                }
                if($item->jobTitle && $item->jobTitle["job_title_name_ar"]){                
                    $item['job_title_ar']=$item->jobTitle["job_title_name_ar"];
                }
                
            })->toArray();

            $pdfAr = PDF::loadView($mailFolderName . '.pdf-template-vote-sign-ar', ['data' => $decision, 'organization' => $user->organization], []);
            $pdfEn = PDF::loadView($mailFolderName . '.pdf-template-vote-sign', ['data' => $decision, 'organization' => $user->organization], []);

            $pdf = $decision->creator->language_id == config('languages.ar') ? $pdfAr->output() : $pdfEn->output();

            $participantEmails = $decision->voters->map(function ($item, $key) {
                $hasNickName = $item['nickname_id'] == null ? false : true;
                return ['hasNick' => $hasNickName, 'email' => $item['email'], 'phone' => $item['user_phone']];
            })->toArray();

            $docId = $this->signatureHelper->createDocument($user->organization, $pdf, $participantEmails, $decision->creator->language_id);
            if ($docId != null) {
                $updated = ['document_id' => $docId];
                $this->voteService->update($decision->id, $updated);
                return true;
            }
            return false;

        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    public function loginUserToVoteSignature(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $vote = $this->voteService->getById($data['vote_id']);
        $userToken = $this->signatureHelper->loginUser($user->organization, $user->email, $vote->document_id);
        if ($userToken != null) {
            return response()->json(['token' => $userToken], 200);
        }

        return response()->json(['error' => 'Somthing went wrong', 'error_ar' => 'حدث خطأ'], 404);
    }

    public function getCircularDecicion(int $id)
    {
        $user = $this->securityHelper->getCurrentUser();
        $timeZone = $user->organization->timeZone;
        $decision = $this->voteService->getCircularDecicion($id, $user->id, $timeZone);
        if ($decision) {
            $decision->vote_users_ids = [];
            $vote_users_ids = array_column($decision->voters->toArray(), 'id');
            // $index = array_search($decision->creator_id,$vote_users_ids);
            // if ($index >= 0) {
            //     unset($vote_users_ids[$index]);
            // }
            $decision->vote_users_ids = array_merge($decision->vote_users_ids, $vote_users_ids);
            if (!in_array($user->id, $vote_users_ids)) {
                $decision->can_vote = false;
            }

            if ($user->id == $decision->creator_id) {
                $decision->is_creator = true;
            } else {
                $decision->is_creator = false;
            }
            return response()->json($decision, 200);
        } else {
            return response()->json(['error' => 'Circular decicion not found', 'error_ar' => 'القرار التعميمى غير موجود'], 400);
        }
    }

    public function updateCircularDecicion(Request $request, int $id)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $decision = $this->voteService->getById($id);
        $newVotersIds = [];
        if (isset($data['vote_users_ids'])) {
            $voteUsersIds = $data['vote_users_ids'];
            // $voteUsersIds[] = $decision->creator_id;
            // update vote voters
            $oldVotersIds = array_column($decision->voters->toArray(), 'id');
            $deletedVotersIds = array_diff($oldVotersIds, $voteUsersIds);
            $newVotersIds = array_diff($voteUsersIds, $oldVotersIds);
        }
        if ($decision && $decision->creator_id == $user->id) {
            $decisionData = $this->voteHelper->prepareCircularDecisionData($data, $user, false, $decision->vote_result_status_id);
            $validator = Validator::make($decisionData, Vote::rules('update-circular-decision', $decision->id));
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
            $decisionData['resetDocument'] = true;
            $this->voteService->update($id, $decisionData);
            $decision = $this->voteService->getDecisionDataWithCanSendNotificationFlag($decision->id);
            if ($decision->can_send_notification) {
                $notificationData = $this->notificationHelper->prepareNotificationDataForCircularDecision($decision, $decision->creator, config('decisionNotification.editDecision'));
                $this->notificationService->sendNotification($notificationData);
                if (!empty($newVotersIds)) {
                    NewCircularDecisionCreatedEmail::dispatch($decision, $newVotersIds);
                }
            }
            $created = $this->createDecisionDocument($decision, $user);
            return response()->json(['message' => 'Circular decision updated successfully', 'message_ar' => 'تم تعديل القرار التعميمى بنجاح'], 200);

        } else {

            return response()->json(['error' => 'Circular decicion not found', 'error_ar' => 'القرار التعميمى غير موجود'], 400);
        }
    }

    public function deleteCircularDecicion(int $id)
    {
        $user = $this->securityHelper->getCurrentUser();
        $decision = $this->voteService->getById($id);
        if ($decision && $decision->creator_id == $user->id && $decision->tasks->count() == 0) {
            $this->voteService->delete($id);
            return response()->json(['message' => 'Circular decision deleted successfully', 'message_ar' => 'تم حذف القرار التعميمى بنجاح'], 200);
        } else {
            return response()->json(['error' => 'Circular decicion can\'t be found', 'error_ar' => 'القرار التعميمى لايمكن حذفه'], 400);
        }
    }

    public function destroyVoteAttachment(int $decisionId, int $attachmentId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $decisionAttachment = $this->attachmentService->getById($attachmentId);
        if ($decisionAttachment && $decisionAttachment->vote && $decisionAttachment->vote->creator_id == $user->id) {
            $this->attachmentService->delete($attachmentId);
            return response()->json(['message' => 'Attachment deleted successfully', 'message_ar' => 'تم حذف المرفق بنجاح'], 200);
        } else {
            return response()->json(['error' => 'Attachment not found', 'error_ar' => 'المرفق غير موجود'], 400);
        }
    }

    public function getCircularDecisionsPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        $filter->SearchObject['is_meeting_vote'] = false;
        return response()->json($this->voteService->getPagedList($filter, $user->id), 200);
    }

    public function setCircularDecisionResultToYes(Request $request, int $decisionId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $decision = $this->voteService->getById($decisionId);

        if ($decision && in_array($user->id, array_column($decision->voters->toArray(), 'id'))) {
            $canVote = $this->voteService->checkUserCanVote($decision->id);
            if ($canVote) {
                $response = $this->setCircularDecisionResult($user, $decision, config('voteStatuses.yes'));
                $notificationData = $this->notificationHelper->prepareNotificationDataForCircularDecision($decision, $user, config('decisionNotification.changeVote'), ['vote_status_id' => config('voteStatuses.yes')]);
                $this->notificationService->sendNotification($notificationData);
                return $response;
            } else {
                return response()->json(['error' => 'Can\'t update decision result, decision period time is passed', 'error_ar' => 'لا يمكنك اخذ القرار, فترة اخذ القرار انقضت'], 400);
            }
        } else {
            return response()->json(['error' => 'Can\'t update decision result', 'error_ar' => 'لا يمكنك اخذ القرار'], 400);
        }
    }

    public function setCircularDecisionResultToNo(Request $request, int $decisionId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $decision = $this->voteService->getById($decisionId);

        if ($decision && in_array($user->id, array_column($decision->voters->toArray(), 'id'))) {
            $canVote = $this->voteService->checkUserCanVote($decision->id);
            if ($canVote) {
                $response = $this->setCircularDecisionResult($user, $decision, config('voteStatuses.no'));
                $notificationData = $this->notificationHelper->prepareNotificationDataForCircularDecision($decision, $user, config('decisionNotification.changeVote'), ['vote_status_id' => config('voteStatuses.no')]);
                $this->notificationService->sendNotification($notificationData);
                return $response;
            } else {
                return response()->json(['error' => 'Can\'t update decision result, decision period time is passed', 'error_ar' => 'لا يمكنك اخذ القرار, فترة اخذ القرار انقضت'], 400);
            }
        } else {
            return response()->json(['error' => 'Can\'t update decision result', 'error_ar' => 'لا يمكنك اخذ القرار'], 400);
        }
    }

    public function setCircularDecisionResultToAbstained(Request $request, int $decisionId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $decision = $this->voteService->getById($decisionId);

        if ($decision && in_array($user->id, array_column($decision->voters->toArray(), 'id'))) {
            $canVote = $this->voteService->checkUserCanVote($decision->id);
            if ($canVote) {
                $response = $this->setCircularDecisionResult($user, $decision, config('voteStatuses.abstained'));
                $notificationData = $this->notificationHelper->prepareNotificationDataForCircularDecision($decision, $user, config('decisionNotification.changeVote'), ['vote_status_id' => config('voteStatuses.abstained')]);
                $this->notificationService->sendNotification($notificationData);
                return $response;
            } else {
                return response()->json(['error' => 'Can\'t update decision result, decision period time is passed', 'error_ar' => 'لا يمكنك اخذ القرار, فترة اخذ القرار انقضت'], 400);
            }
        } else {
            return response()->json(['error' => 'Can\'t update decision result', 'error_ar' => 'لا يمكنك اخذ القرار'], 400);
        }
    }

    private function setCircularDecisionResult($user, $decision, $voteStatus)
    {
        $isHeadOfCommittee = $this->committeeUserService->checkIfUserIsHeadOfCommittee($user->id, $decision->committee_id);
        $voteResultData = $this->voteResultHelper->prepareDataForVoteResult($decision->id, $voteStatus, $isHeadOfCommittee, ['user_id' => $user->id]);
        $voteResult = $this->voteResultService->checkVoteBefore($user, $decision->id);
        if ($voteResult) { // update
            $this->voteResultService->update($voteResult->id, $voteResultData);
        } else { // create
            $this->voteResultService->create($voteResultData);
        }
        // update decison result status
        //UpdateStatusOfVote::dispatch($decision->id);
        $voteCountResult = $this->voteResultService->countVoteResults($decision->id);
        $data = $this->voteHelper->prepareVoteResultStatus($voteCountResult);
        $this->voteService->update($decision->id, $data);
        return response()->json(['message' => 'Decision result updated successfully', 'message_ar' => 'تم أخذ القرار بنجاح'], 200);
    }

    public function sendNotificationWhenCircularDecisionStart()
    {
        $decisions = $this->voteService->getStartedCircularDecisions();
        foreach ($decisions as $key => $decision) {
            $notificationData = $this->notificationHelper->prepareNotificationDataForCircularDecision($decision, $decision->creator, config('decisionNotification.startDecision'));
            $this->notificationService->sendNotification($notificationData);
            NewCircularDecisionCreatedEmail::dispatch($decision, null);
        }
    }

    public function sendNotificationWhenCreateMeetingDecisions($createdDecisions)
    {
        foreach ($createdDecisions as $key => $voteDetails) {
            $notificationData = $this->notificationHelper->prepareNotificationDataForMeetingDecision($voteDetails, $voteDetails->meeting->creator, config('meetingDecision.addDecision'), []);
            $this->notificationService->sendNotification($notificationData);
        }
    }

    public function getTasks(Request $request, int $id)
    {
        $filter = (object) ($request->all());
        $filter->SearchObject['vote_id'] = $id;
        return response()->json($this->taskManagementService->getPagedList($filter), 200);
    }

    public function createDirectoryForCircularDecisionAfterCompleted()
    {
        // get all circulsr decisios have end date in the past, have attachments and not hane directory
        $circurlarDecisions = $this->voteService->getCircularDecisionsHaveEndDateInThePast();
        foreach ($circurlarDecisions as $key => $circurlarDecision) {
            // create new directory
            $directory = $this->storageHelper->createCircularDecisionDirectory($circurlarDecision, $circurlarDecision->creator);
            $directory = $this->directoryService->create($directory->toArray());
            $this->voteService->createStorageAccessAndFilesOfDirectory($circurlarDecision, $directory);
        }
    }

    public function downloadCircularDecisionPdf(Request $request, int $voteId, string $lang)
    {
        $user = $this->securityHelper->getCurrentUser();
        $decision = $this->voteService->getCircularDecicion($voteId, $user->id, $user->organization->timeZone);

        if ($decision) {
            $file = $this->signatureHelper->getDocumentBinary($user->organization, $decision->document_id, $user->email);
            return $file;
        }

        return response()->json(['error' => 'Meeting still not ended'], 400);
    }
}
