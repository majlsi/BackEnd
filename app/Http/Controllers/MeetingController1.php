<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Connectors\SmsConnector;
use Connectors\SmsSwccGatewayConnector;
use Connectors\ZoomConnector;
use Helpers\EmailHelper;
use Helpers\EventHelper;
use Helpers\MeetingAgendaHelper;
use Helpers\MeetingHelper;
use Helpers\MomHelper;
use Helpers\NotificationHelper;
use Helpers\SecurityHelper;
use Helpers\SendCheckMeetingAttendanceHelper;
use Helpers\SignatureHelper;
use Helpers\SmsHelper;
use Helpers\StorageHelper;
use Helpers\ZoomMeetingHelper;
use Illuminate\Http\Request;
use Jobs\EndZoomMeeting;
use Models\Meeting;
use Models\Approval;
use PDF;
use Services\ApprovalService;
use Services\AttachmentService;
use Services\ChatGroupService;
use Services\ChatService;
use Services\DirectoryService;
use Services\MeetingAgendaService;
use Services\MeetingParticipantService;
use Services\MeetingService;
use Services\MomService;
use Services\MomTemplateService;
use Services\NotificationService;
use Services\OnlineMeetingAppService;
use Services\ProposalService;
use Services\RoleRightService;
use Services\TaskManagementService;
use Services\UserCommentService;
use Services\UserService;
use Services\ZoomConfigurationService;
use Services\VoteResultService;
use Services\VoteService;
use Services\StakeholderService;
use Services\CommitteeService;
use Services\MeetingGuestService;
use Storage;
use Validator;

class MeetingController extends Controller
{
    private $meetingService;
    private $securityHelper;
    private $meetingHelper;
    private $emailHelper;
    private $signatureHelper;
    private $smsConnector;
    private $smsSwccConnector;
    private $meetingParticipantService;
    private $eventHelper;
    private $attachmentService;
    private $meetingAgendaHelper;
    private $notificationHelper;
    private $userService;
    private $meetingAgendaService;
    private $zoomMeetingHelper;
    private $chatService;
    private $onlineMeetingAppService;
    private $chatGroupService;
    private $momHelper;
    private $momService;
    private $momTemplateService;
    private $notificationService;
    private $taskManagementService;
    private $voteResultService;
    private $voteService;
    private $stakeholderService;
    private $committeeService;
    private MeetingGuestService $meetingGuestService;
    private ApprovalService $approvalService;

    public function __construct(
        MeetingService $meetingService,
        SecurityHelper $securityHelper,
        MeetingHelper $meetingHelper,
        EmailHelper $emailHelper,
        SmsConnector $smsConnector,
        SmsSwccGatewayConnector $smsSwccConnector,
        ProposalService $proposalService,
        MeetingParticipantService $meetingParticipantService,
        UserCommentService $userCommentService,
        SendCheckMeetingAttendanceHelper $sendCheckMeetingAttendanceHelper,
        EventHelper $eventHelper,
        RoleRightService $roleRightService,
        AttachmentService $attachmentService,
        MeetingAgendaHelper $meetingAgendaHelper,
        NotificationHelper $notificationHelper,
        SignatureHelper $signatureHelper,
        UserService $userService,
        MeetingAgendaService $meetingAgendaService,
        ZoomMeetingHelper $zoomMeetingHelper,
        ZoomConfigurationService $zoomConfigurationService,
        ChatService $chatService,
        VoteService $voteService,
        OnlineMeetingAppService $onlineMeetingAppService,
        ChatGroupService $chatGroupService,
        MomHelper $momHelper,
        MomService $momService,
        VoteResultService $voteResultService,
        MomTemplateService $momTemplateService,
        NotificationService $notificationService,
        StorageHelper $storageHelper,
        TaskManagementService $taskManagementService,
        DirectoryService $directoryService,
        StakeholderService $stakeholderService,
        CommitteeService $committeeService,
        MeetingGuestService $meetingGuestService,
        ApprovalService $approvalService
    ) {
        $this->meetingService = $meetingService;
        $this->securityHelper = $securityHelper;
        $this->meetingHelper = $meetingHelper;
        $this->emailHelper = $emailHelper;
        $this->smsConnector = $smsConnector;
        $this->smsSwccConnector = $smsSwccConnector;
        $this->proposalService = $proposalService;
        $this->meetingParticipantService = $meetingParticipantService;
        $this->userCommentService = $userCommentService;
        $this->sendCheckMeetingAttendanceHelper = $sendCheckMeetingAttendanceHelper;
        $this->eventHelper = $eventHelper;
        $this->roleRightService = $roleRightService;
        $this->attachmentService = $attachmentService;
        $this->meetingAgendaHelper = $meetingAgendaHelper;
        $this->notificationHelper = $notificationHelper;
        $this->signatureHelper = $signatureHelper;
        $this->userService = $userService;
        $this->meetingAgendaService = $meetingAgendaService;
        $this->zoomMeetingHelper = $zoomMeetingHelper;
        $this->zoomConfigurationService = $zoomConfigurationService;
        $this->chatService = $chatService;
        $this->onlineMeetingAppService = $onlineMeetingAppService;
        $this->chatGroupService = $chatGroupService;
        $this->momHelper = $momHelper;
        $this->momService = $momService;
        $this->momTemplateService = $momTemplateService;
        $this->notificationService = $notificationService;
        $this->taskManagementService = $taskManagementService;
        $this->storageHelper = $storageHelper;
        $this->directoryService = $directoryService;
        $this->voteResultService = $voteResultService;
        $this->voteService = $voteService;
        $this->stakeholderService = $stakeholderService;
        $this->committeeService = $committeeService;
        $this->meetingGuestService = $meetingGuestService;
        $this->approvalService = $approvalService;
    }

    public function show($id)
    {
        $user = $this->securityHelper->getCurrentUser();

        $allowedGuestsVoteParticipants = null;
        $allowedUsersVoteParticipants = null;
        if ($user) {
            if ($user->id != -1) {
                $allowedUsersVoteParticipants = $user->id;
            } else {
                $allowedGuestsVoteParticipants = $user->meeting_guest_id;
            }
        }

        return response()->json($this->meetingService->getMeetingDetails($id, $user, $allowedGuestsVoteParticipants, $allowedUsersVoteParticipants), 200);
    }

    public function getMeetingVersionData(int $id)
    {
        $meetingId = $id;
        $masterMeeting = $this->meetingService->getById($id);
        $user = $this->securityHelper->getCurrentUser();
        $versionOfMeeting = $this->meetingService->getLastVersionOfMeeting($id);
        if ($versionOfMeeting && (!in_array($masterMeeting->meeting_status_id, [config('meetingStatus.cancel'), config('meetingStatus.end'), config('meetingStatus.sendRecommendation')]))) {
            $meetingId = $versionOfMeeting->id;
        }
        $meetingData = $this->meetingService->getMeetingDetails($meetingId, $user);
        $meetingData['id'] = $id;
        $meetingData['is_changed_publish'] = $versionOfMeeting ? $versionOfMeeting->is_published : true;

        return response()->json($meetingData, 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            $meetingData = $this->meetingHelper->prepareMeetingDataOnCreate($data, $user->organization_id);
            $validator = Validator::make($meetingData, Meeting::rules('save'));

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
            if (isset($data['meeting_reminders'])) {
                $meetingReminders = $data['meeting_reminders'];
            } else {
                $meetingReminders = [];
            }
            try {
                $Data = ['meeting' => $meetingData, 'meeting_reminders' => $meetingReminders, 'user' => $user];

                $newMeeting = $this->meetingService->create($Data);
                $this->eventHelper->fireEvent([], 'App\Events\MeetingDataChangedEvent');

                // create online meeting
                $this->onlineMeetingAppService->createOnlineMeeting($user, $newMeeting, $newMeeting->id);
                // create chat room for meeting
                // if ($user->chat_user_id) {
                //     $this->chatService->createMeetingRoom($user,$newMeeting);
                // }

                return response()->json($newMeeting, 200);
            } catch (\Exception $e) {
                report($e);

                return response()->json($e, 500);
            }
        } else {
            return response()->json(['error' => 'You can\'t add meeting'], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($id);
        $versionOfMeetingBeforeUpdate = $this->meetingService->getUnpublishedVersionOfMeeting($id);

        if ($user && $user->organization_id && $user->organization_id == $meeting->organization_id) {
            $meetingData = $this->meetingHelper->prepareMeetingDataOnUpdate($data);
            $validator = Validator::make($meetingData, Meeting::rules('update', $id));

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
            $check = $this->meetingService->checkCommitteChange($id, $data['committee_id']);
            if ($check == true) {
                $this->meetingService->update($id, $meetingData);
                // $this->eventHelper->fireEvent([], 'App\Events\MeetingDataChangedEvent');
                // create online meeting
                $versionOfMeeting = $this->meetingService->getUnpublishedVersionOfMeeting($id);
                if ((!$versionOfMeetingBeforeUpdate && ($meeting->online_configuration_id != $versionOfMeeting->online_configuration_id)) || ($versionOfMeeting && $versionOfMeetingBeforeUpdate && ($versionOfMeetingBeforeUpdate->online_configuration_id != $versionOfMeeting->online_configuration_id))) {
                    $this->onlineMeetingAppService->createOnlineMeeting($user, $versionOfMeeting, $meeting->id);
                } else {
                    // $this->onlineMeetingAppService->updateOnlineMeeting($user, $versionOfMeeting, $meeting->id);
                }

                return response()->json(['message' => 'Meeting update successuflly', 'meeting_version_id' => $versionOfMeeting ? $versionOfMeeting->id : null], 200);
            } else {
                return response()->json(['error' => 'You can\'t change committee , please remove the participants first', 'error_ar' => 'لا يمكنك تغيير لجنة ، يرجى إزالة المشاركين أولاً'], 400);
            }
        } else {
            return response()->json(['error' => 'You can\'t add meeting'], 400);
        }
    }

    public function destroy($id)
    {
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($id);
        if ($meeting && $meeting->meeting_status_id == config('meetingStatus.draft')) {
            if ($user && $user->organization_id && $user->organization_id == $meeting->organization_id) {
                $this->meetingService->deleteAllMeetingDetails($meeting);

                return response()->json(['message' => 'success'], 200);
            } else {
                return response()->json(['error' => 'You don\'t have access'], 400);
            }
        } else {
            return response()->json([
                'error' => 'You can\'t delete this meeting, its status not draft',
                'error_ar' => 'لا يمكنك حذف هذا الاجتماع ، حالته ليست مسودة'
            ], 400);
        }
    }

    public function getPagedList(Request $request)
    {
        try {
            $filter = (object) ($request->all());
            $user = $this->securityHelper->getCurrentUser();
            if (!$user) {
                return response()->json(['error' => 'Don\'t have access!'], 400);
            }
            $userRoleCode = $user->role->role_code;
            $meetingList = $this->meetingService->getPagedList($filter, $user->organization_id, $userRoleCode, $user->id);
            $meetingList = $this->meetingService->setShowAttendancePercentageWarningFlagForEachMeeting($meetingList);

            return response()->json($meetingList, 200);
        } catch (\Exception $e) {
            report($e);

            return response()->json($e, 500);
        }
    }

    public function changeMeetingMomTemplate(Request $request, int $meetingId)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        if ($user && $user->organization_id && $user->organization_id == $meeting->organization_id) {
            $this->meetingService->changeMeetingMomTemplate($meetingId, $data['mom_template_id']);

            return response()->json(['message' => 'Mom Template update successuflly', 'message_ar' => 'تم نغيير نموذج محضر الإجتماع بنجاح'], 200);
        } else {
            return response()->json(['error' => 'You can\'t update this  meeting'], 400);
        }
    }

    public function changeMeetingMomPdf(Request $request, int $meetingId)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        if ($user && $user->organization_id && $user->organization_id == $meeting->organization_id) {
            $this->meetingService->changeMeetingMomPdf($meeting, $data);
            return response()->json(['message' => 'Mom Template update successuflly', 'message_ar' => 'تم نغيير نموذج محضر الإجتماع بنجاح'], 200);
        } else {
            return response()->json(['error' => 'You can\'t update this  meeting'], 400);
        }
    }

    public function publishMeeting(Request $request, int $meetingId)
    {
        $data = $request->all();

        $meeting = $this->meetingService->getById($data['id']);
        $zoomJoinUrl = null;
        $microsoftTeamsJoinUrl = null;

        if ($meeting && $meeting->meeting_status_id == config('meetingStatus.draft')) {
            $status = ['meeting_status_id' => config('meetingStatus.publish')];
            $this->meetingService->update($data['id'], $status);
            $this->updateChatGroupUsers($meeting);
            $emailData = $this->meetingHelper->prepareMeetingPublishedEmailData($meeting);

            if ($meeting->organization['online_meeting_app_id'] == config('onlineMeetingApp.zoom') && $meeting['zoom_meeting_id']) {
                $zoomJoinUrl = $meeting['zoom_join_url'];
            }
            if ($meeting->organization['online_meeting_app_id'] == config('onlineMeetingApp.microsoftTeams') && $meeting['microsoft_teams_meeting_id']) {
                $microsoftTeamsJoinUrl = $meeting['microsoft_teams_join_url'];
            }
            $sendNotification = $this->meetingService->sendNotificationToMeeting($meeting, config('meetingStatus.publish'));
            $participants = $meeting->meetingParticipants;
            $usersIds = array_column($participants->toArray(), 'id');
            $stakeholders = $this->stakeholderService->getStakeholdersInUsersIds($usersIds)->toArray();

            // activate stakeholders
            $stakeholdersUserIds = array_column($stakeholders, 'id');
            if (count($stakeholdersUserIds) > 0) {
                $this->userService->activatDeactivateeUsers($stakeholdersUserIds, 1);
            }
            // send welcome email to stakeholders
            $this->userService->sendWelcomeEmailToStakeholders($stakeholders);

            // invite guests
            $this->meetingGuestService->inviteGuests($data['id'], $emailData, $meeting->organization->systemAdmin["language_id"], $meeting->timeZone, $zoomJoinUrl, $microsoftTeamsJoinUrl);

            // send email to all participants
            foreach ($participants as $key => $meetingParticipant) {
                $this->emailHelper->sendMeetingPublished($meetingParticipant->email, $meetingParticipant->name_ar, $meetingParticipant->name, $emailData['meeting_title_ar'], $emailData['meeting_title_en'], $emailData['meeting_venue_ar'], $emailData['meeting_venue_en'], $emailData['meeting_schedule_from'], $emailData['meeting_schedule_to'], $zoomJoinUrl, $microsoftTeamsJoinUrl, $meetingParticipant->language_id, $meeting->timeZone);
                if ($meetingParticipant->user_phone) {
                    if(config('smsGateway.smsSwccGateway'))
                    {                        
                        if ($meetingParticipant->language_id == config('languages.en')) {
                            $this->smsSwccConnector->sendSMS($meetingParticipant->user_phone, SmsHelper::getSmsBody('sms.PublishMeetingEn', ['meeting' => $emailData['meeting_title_en'], 'zoomJoinUrl' => $zoomJoinUrl, 'microsoftTeamsJoinUrl' => $microsoftTeamsJoinUrl]));
                        } elseif ($meetingParticipant->language_id == config('languages.ar')) {
                            $this->smsSwccConnector->sendSMS($meetingParticipant->user_phone, SmsHelper::getSmsBody('sms.PublishMeetingAr', ['meeting' => $emailData['meeting_title_ar'], 'zoomJoinUrl' => $zoomJoinUrl, 'microsoftTeamsJoinUrl' => $microsoftTeamsJoinUrl]));
                        }
                    }
                    else 
                    {
                        if ($meetingParticipant->language_id == config('languages.en')) {
                            $this->smsConnector->sendSMS($meetingParticipant->user_phone, SmsHelper::getSmsBody('sms.PublishMeetingEn', ['meeting' => $emailData['meeting_title_en'], 'zoomJoinUrl' => $zoomJoinUrl, 'microsoftTeamsJoinUrl' => $microsoftTeamsJoinUrl]));
                        } elseif ($meetingParticipant->language_id == config('languages.ar')) {
                            $this->smsConnector->sendSMS($meetingParticipant->user_phone, SmsHelper::getSmsBody('sms.PublishMeetingAr', ['meeting' => $emailData['meeting_title_ar'], 'zoomJoinUrl' => $zoomJoinUrl, 'microsoftTeamsJoinUrl' => $microsoftTeamsJoinUrl]));
                        }
                    }
                }
            }
            // create and send notification
            $user = $this->securityHelper->getCurrentUser();
            $notificationData = $this->notificationHelper->prepareNotificationDataForMeeting($meeting, $user, config('meetingNotifications.publishMeeting'), []);
            $this->notificationService->sendNotification($notificationData);

            return response()->json(['message' => 'Meeting Published successuflly'], 200);
        }

        return response()->json(['error' => 'Meeting Can\'t be Published'], 400);
    }

    public function publishMeetingAgenda(Request $request, int $meetingId)
    {
        $data = $request->all();

        $meeting = $this->meetingService->getById($data['id']);

        if ($meeting && $meeting->meeting_status_id == config('meetingStatus.publish')) {
            $zoomJoinUrl = ($meeting->organization['online_meeting_app_id'] == config('onlineMeetingApp.zoom') && $meeting['zoom_meeting_id']) ? $meeting['zoom_join_url'] : null;
            $microsoftTeamsJoinUrl = ($meeting->organization['online_meeting_app_id'] == config('onlineMeetingApp.microsoftTeams') && $meeting['microsoft_teams_meeting_id']) ? $meeting['microsoft_teams_join_url'] : null;
            $status = ['meeting_status_id' => config('meetingStatus.publishAgenda')];
            $this->meetingService->update($data['id'], $status);
            $this->updateChatGroupUsers($meeting);
            $emailData = $this->meetingHelper->prepareMeetingPublishedEmailData($meeting);

            $sendNotification = $this->meetingService->sendNotificationToMeeting($meeting, config('meetingStatus.publishAgenda'));

            // send email to guests
            $this->meetingGuestService->sendMeetingAgenda($data['id'], $emailData, $meeting->organization->systemAdmin["language_id"], $meeting->timeZone, $zoomJoinUrl, $microsoftTeamsJoinUrl);

            $participants = $meeting->meetingParticipants;
            foreach ($participants as $key => $meetingParticipant) {
                $this->emailHelper->sendMeetingAgendaPublished($meetingParticipant->email, $meetingParticipant->name_ar, $meetingParticipant->name, $emailData['meeting_title_ar'], $emailData['meeting_title_en'], $emailData['meeting_venue_ar'], $emailData['meeting_venue_en'], $emailData['meeting_schedule_from'], $emailData['meeting_schedule_to'], $zoomJoinUrl, $microsoftTeamsJoinUrl, $meetingParticipant->language_id, $meeting->timeZone);
                if ($meetingParticipant->user_phone) {
                    if(config('smsGateway.smsSwccGateway'))
                    {                        
                        if ($meetingParticipant->language_id == config('languages.ar')) {
                            $this->smsSwccConnector->sendSMS($meetingParticipant->user_phone, SmsHelper::getSmsBody('sms.PublishMeetingAr', ['meeting' => $emailData['meeting_title_ar'], 'zoomJoinUrl' => $zoomJoinUrl, 'microsoftTeamsJoinUrl' => $microsoftTeamsJoinUrl]));
                        } elseif ($meetingParticipant->language_id == config('languages.en')) {
                            $this->smsSwccConnector->sendSMS($meetingParticipant->user_phone, SmsHelper::getSmsBody('sms.PublishMeetingEn', ['meeting' => $emailData['meeting_title_en'], 'zoomJoinUrl' => $zoomJoinUrl, 'microsoftTeamsJoinUrl' => $microsoftTeamsJoinUrl]));
                        }
                    }
                    else
                    {
                        if ($meetingParticipant->language_id == config('languages.ar')) {
                            $this->smsConnector->sendSMS($meetingParticipant->user_phone, SmsHelper::getSmsBody('sms.PublishMeetingAr', ['meeting' => $emailData['meeting_title_ar'], 'zoomJoinUrl' => $zoomJoinUrl, 'microsoftTeamsJoinUrl' => $microsoftTeamsJoinUrl]));
                        } elseif ($meetingParticipant->language_id == config('languages.en')) {
                            $this->smsConnector->sendSMS($meetingParticipant->user_phone, SmsHelper::getSmsBody('sms.PublishMeetingEn', ['meeting' => $emailData['meeting_title_en'], 'zoomJoinUrl' => $zoomJoinUrl, 'microsoftTeamsJoinUrl' => $microsoftTeamsJoinUrl]));
                        }
                    }
                }
            }
            // create and send notification
            $user = $this->securityHelper->getCurrentUser();
            $notificationData = $this->notificationHelper->prepareNotificationDataForMeeting($meeting, $user, config('meetingNotifications.publishMeetingAgenda'), []);
            $this->notificationService->sendNotification($notificationData);

            return response()->json(['message' => 'Meeting Agenda Published successuflly'], 200);
        }

        return response()->json(['error' => 'Meeting Agenda Can\'t be Published'], 400);
    }

    public function startMeeting(Request $request, int $meetingId)
    {
        $data = $request->all();
        $status = [];
        $currentUser = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($data['id']);
        $organizersIds = array_column($meeting->organisers->toArray(), 'user_id');
        if ($meeting && $meeting->meeting_status_id == config('meetingStatus.publishAgenda') && (in_array($currentUser->id, $organizersIds) || $currentUser->id == $meeting->created_by)) {
            $chatGroup = $this->chatGroupService->getChatGroupByMeetingId($meeting->id);
            if (!$chatGroup && !$meeting->chat_room_id) {
                // create chat room for meeting
                die('test 65');
                $response = $this->chatService->createMeetingChatRoom($currentUser, $meeting);
                if ($response['is_success']) {
                    $status['chat_room_id'] = $response['response']['chatRoom']['id'];
                    // create chat group for meeting if not exist
                    $chatGroup = $this->chatGroupService->createMeetingChatGroupIfNotExist($currentUser, $meeting, $response['response']['chatRoom']['id']);
                }
            }
            $status['meeting_status_id'] = config('meetingStatus.start');
            $this->meetingService->update($data['id'], $status);
            $meetingAfterUpdate = $this->meetingService->getById($data['id']);
            $this->updateChatGroupUsers($meetingAfterUpdate);
            $sendNotification = $this->meetingService->sendNotificationToMeeting($meeting, config('meetingStatus.start'));
            // start presenation
            if (isset($data['attachmentId'])) {
                $attachment = $this->attachmentService->getById($data['attachmentId']);
                $meeting = $this->meetingService->getById($data['id']);
                $firingData = $this->attachmentService->presentAttachment($meeting, $currentUser, $attachment);
                $this->meetingAgendaService->updateAgendaTimerWhenStartPresentation($attachment->meeting_agenda_id);
            }
            // create and send notification
            $user = $this->securityHelper->getCurrentUser();
            $notificationData = $this->notificationHelper->prepareNotificationDataForMeeting($meeting, $user, config('meetingNotifications.startMeeting'), []);
            $this->notificationService->sendNotification($notificationData);

            return response()->json(['message' => 'Meeting Started successuflly'], 200);
        }

        return response()->json(['error' => 'Meeting Can\'t be Started'], 400);
    }

    public function endMeeting(Request $request, int $meetingId)
    {
        $data = $request->all();

        $meeting = $this->meetingService->getById($data['id']);
        $user = $this->securityHelper->getCurrentUser();
        if ($meeting && $meeting->meeting_status_id == config('meetingStatus.start')) {
            if (isset($data['currentPresentationId'])) {
                $attachment = $this->attachmentService->getById($data['currentPresentationId']);

                $firingData = $this->attachmentService->endPresentation($meeting, $user, $attachment);
            } else {
                $presenationData = $this->meetingService->getCurrentPresentingAttachment($meeting, $user);
                if ($presenationData) {
                    return response()->json([
                        'error' => 'Can\'t end meeting, There is an attachment is Presenting Now',
                        'error_ar' => 'لا يمكن إنهاء الاجتماع ، هناك مرفق يتم تقديمه الآن',
                        'is_current_presenation' => true,
                        'current_attachment_id' => $presenationData['attachmentId']
                    ], 400);
                }
            }
            $status = ['meeting_status_id' => config('meetingStatus.end')];
            $this->meetingService->update($data['id'], $status);
            if ($user->organization->enable_meeting_archiving) {
                $this->meetingService->createMeetingDirectory($data['id']);
            }

            $this->updateChatGroupUsers($meeting);
            $emailData = $this->meetingHelper->prepareMeetingPublishedEmailData($meeting);
            $sendNotification = $this->meetingService->sendNotificationToMeeting($meeting, config('meetingStatus.end'));
            $meeting = $this->meetingService->getMeetingDataForPdfTemplate($meetingId);
            $meetingAllData = $meeting->toArray();
            $meetingAllData['meeting_schedule_date_from'] = Carbon::parse($meetingAllData['meeting_schedule_from'])->format('Y-m-d');
            $meetingAllData['meeting_schedule_time_from'] = Carbon::parse($meetingAllData['meeting_schedule_from'])->format('g:i A');

            $meetingOrganisers = $meeting->meetingOrganisers;
            $meetingOrganiserIds = array_column($meetingOrganisers->toArray(), 'id');
            $meetingAgendas = $meetingAllData['meeting_agendas'];
            $participants = $meeting->meetingParticipants;
            foreach ($participants as $key => $meetingParticipant) {
                // filter meeting agendas  comments
                $meetingAllData['meeting_agendas'] = $this->meetingAgendaHelper->filterMeetingAgendaComments($meetingParticipant, $meetingAgendas, $meetingOrganiserIds);

                $meetingAllData['meeting_agendas'] = array_filter($meetingAllData['meeting_agendas'], function ($meetingAgenda) {
                    return count($meetingAgenda['agenda_user_comments']) > 0;
                });

                $pdf = null;
                if (count($meetingAllData['meeting_agendas']) > 0) {
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
                    if ($meetingParticipant->language_id == config('languages.ar')) {
                        $pdf = PDF::loadView($mailFolderName . '.comments-pdf-template-min-ar', ['data' => $meetingAllData], []);
                    } else if ($meetingParticipant->language_id == config('languages.en')) {
                        $pdf = PDF::loadView($mailFolderName . '.comments-pdf-template-min', ['data' => $meetingAllData], []);
                    }
                }

                $this->emailHelper->sendMeetingEnded($meetingParticipant->email, $meetingAllData, $meetingParticipant->name_ar, $meetingParticipant->name, $emailData["meeting_title_ar"], $emailData["meeting_title_en"], $emailData["meeting_venue_ar"], $emailData["meeting_venue_en"], $emailData["meeting_schedule_from"], $emailData["meeting_schedule_to"], $pdf, $meetingParticipant->language_id);
            }

            // end zoom meeting
            $meetingOnlineConfiguration = $meeting->meetingOnlineConfigurations()->first();
            if ($meetingOnlineConfiguration && $meetingOnlineConfiguration->online_meeting_app_id == config('onlineMeetingApp.zoom')) {
                $zoomConfiguration = $meetingOnlineConfiguration;
                EndZoomMeeting::dispatch($meeting, $zoomConfiguration);
            }
            // create and send notification
            $notificationData = $this->notificationHelper->prepareNotificationDataForMeeting($meeting, $user, config('meetingNotifications.endMeeting'), []);
            $this->notificationService->sendNotification($notificationData);

            // deactivate stakeholders
            $usersIds = array_column($participants->toArray(), 'user_id');
            $stakeholders = $this->stakeholderService->getStakeholdersInUsersIds($usersIds);
            $stakeholdersUserIds = array_column($stakeholders->toArray(), 'id');
            if (count($stakeholdersUserIds) > 0) {
                $this->userService->activatDeactivateeUsers($stakeholdersUserIds, 0);
            }
            return response()->json(['message' => 'Meeting Ended successuflly'], 200);
        }

        return response()->json(['error' => 'Meeting Can\'t be Ended'], 400);
    }

    public function sendEmailAfterEndMeeting(Request $request, int $meetingId)
    {
        $data = $request->all();

        $meeting = $this->meetingService->getById($data['id']);
        $user = $this->securityHelper->getCurrentUser();

        if ($meeting) {
            $emailData = $this->meetingHelper->prepareMeetingPublishedEmailData($meeting);
            $meetingAllData = $this->meetingService->getMeetingDataForPdfTemplate($meetingId)->toArray();
            $meetingGuests = $this->meetingGuestService->getMeetingGuests($meetingId);
            $meetingAllData['canSignParticipants'] = collect($meetingAllData['meeting_participants'])->where('can_sign', 1);
            $meetingAllData['meeting_schedule_date_from'] = Carbon::parse($meetingAllData['meeting_schedule_from'])->format('Y-m-d');
            $meetingAllData['meeting_schedule_time_from'] = Carbon::parse($meetingAllData['meeting_schedule_from'])->format('g:i A');
            $meetingMom = $this->momService->getMeetingMom($meetingId);

            if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.mjlsi')) {
                $mailFolderName = 'pdf';
            } elseif (config('buildConfig.currentTheme') == config('buildConfig.themeNames.gaft')) {
                $mailFolderName = 'pdf-gaft';
            } elseif (config('buildConfig.currentTheme') == config('buildConfig.themeNames.eca')) {
                $mailFolderName = 'pdf-eca';
            } elseif (config('buildConfig.currentTheme') == config('buildConfig.themeNames.lcgpa')) {
                $mailFolderName = 'pdf-lcgpa';
            } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.sadu')) {
                $mailFolderName = 'pdf-sadu';
            } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.swcc')) {
                $mailFolderName = 'pdf-swcc';
            }

            $pdfAr = PDF::loadView($mailFolderName . '.pdf-template-min-ar', ['data' => $meetingAllData, 'meetingMom' => $meetingMom], []);
            $pdfEn = PDF::loadView($mailFolderName . '.pdf-template-min', ['data' => $meetingAllData, 'meetingMom' => $meetingMom], []);

            $sentUsersEmail = [];
            //unset($meetingMom['mom_summary']);
            //send email to participant
            $sendMomParticipants = $meeting->meetingParticipants
                ->where('pivot.send_mom', true);
            foreach ($sendMomParticipants as $meetingParticipant) {
                $sentUsersEmail[] = $meetingParticipant->email;
                $this->handleSendMomEmails(
                    $meetingAllData,
                    $meetingParticipant,
                    $mailFolderName,
                    $meetingMom,
                    $emailData,
                    $meeting
                );
            }

            //send mom email to guest
            $sendMomGuests = $meetingGuests->where('send_mom', true);
            foreach ($sendMomGuests as $meetingGuest) {
                $sentUsersEmail[] = $meetingGuest->email;
                $this->handleSendMomEmails(
                    $meetingAllData,
                    $meetingGuest,
                    $mailFolderName,
                    $meetingMom,
                    $emailData,
                    $meeting
                );
            }

            //send email to organizers
            foreach ($meeting->meetingOrganisers as $meetingOrganizer) {
                if (!in_array($meetingOrganizer->email, $sentUsersEmail)) {
                    $this->handleSendMomEmails(
                        $meetingAllData,
                        $meetingOrganizer,
                        $mailFolderName,
                        $meetingMom,
                        $emailData,
                        $meeting
                    );
                }
            }

            //get can sign participants
            $canSignParticipants = $meeting->meetingParticipants
                ->where('pivot.can_sign', true);

            $canSignGuests = $meetingGuests->where('can_sign', true);
            $guestEmails = $canSignGuests->map(function ($item, $key) {
                $hasNickName = $item['nickname_id'] == null ? false : true;

                return ['hasNick' => $hasNickName, 'email' => $item['email'], 'phone' => null];
            })->toArray();

            $participantEmails = $canSignParticipants->map(function ($item, $key) {
                $hasNickName = $item['nickname_id'] == null ? false : true;

                return ['hasNick' => $hasNickName, 'email' => $item['email'], 'phone' => $item['user_phone']];
            })->toArray();

            $canSignEmails = array_merge($participantEmails, $guestEmails);
            $pdf = null;
		//die('MBM');
            if ($meeting->is_mom_pdf) {
 		
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
		
                try {
                    $pdfAr = PDF::loadView($mailFolderName . '.pdf-template-min-sign-ar', ['data' => $meetingAllData, 'meetingMom' => $meetingMom], []);
                    $pdfEn = PDF::loadView($mailFolderName . '.pdf-template-min-sign', ['data' => $meetingAllData, 'meetingMom' => $meetingMom], []);
                } catch (\Exception $e) {
                    report($e);
                    return response()->json(['error' => 'There is an error in your mom template , Please check', 'error_ar' => 'يوجد خطأ فى نموذج محضر الإجتماع , برجاء المراجعة'], 400);
                }
                $pdf = $meetingMom->language_id == config('languages.ar') ? $pdfAr : $pdfEn;
                $path = 'temp.pdf';
                Storage::disk('local')->put($path, $pdf->output());
                $pdfmerged = new \LynX39\LaraPdfMerger\PdfManage;
                $pdfmerged->init();
                $pdfmerged->addPDF(public_path() . '/' . $meeting->mom_pdf_url, 'all');
                $pdfmerged->addPDF(Storage::disk('local')->path($path), 'all');
                $pdfmerged->merge();
                $pdf = $pdfmerged->save('new.pdf', 'string');
            } else {
                $pdf = isset($meetingMom->language_id) ? ($meetingMom->language_id == config('languages.ar') ? $pdfAr->output() : $pdfEn->output()) : $pdfAr->output();
            }

		//var_dump($user->organization, $pdf, $canSignEmails, $meeting->creator->language_id);
		//die();
            $docId = $this->signatureHelper->createDocument($user->organization, $pdf, $canSignEmails, $meeting->creator->language_id);
	
            if ($docId != null) {
                $updated = ['is_mom_sent' => true, 'document_id' => $docId];
                $this->meetingService->update($data['id'], $updated);
                // create and send notification
                $notificationData = $this->notificationHelper->prepareNotificationDataForMeeting($meeting, $user, config('meetingNotifications.sendMom'), []);
                $this->notificationService->sendNotification($notificationData);

                return response()->json(['message' => 'Email Send successuflly'], 200);
            } else {
                return response()->json(['error' => 'something went wrong, you can try again later! ', 'error_ar' => 'حدث خطأ ما، يمكنك المحاولة لاحقاَ'], 400);
            }
        }

        return response()->json(['error' => 'Meeting still not ended'], 400);
    }

    public function handleSendMomEmails(
        $meetingAllData,
        $meetingParticipant,
        $mailFolderName,
        $meetingMom,
        $emailData,
        $meeting
    ) {
        $dataCopy = $meetingAllData;
        if ($meetingAllData['meeting_agendas']) {
            foreach ($meetingAllData['meeting_agendas'] as $agendaIndex => $agenda) {
                if (
                    !($agenda['participants'] && (((!isset($meetingParticipant->meeting_id)) && in_array(
                        $meetingParticipant->id,
                        array_column($agenda['participants'], 'user_id')
                    )
                    )
                        || (isset($meetingParticipant->meeting_id) && in_array(
                            $meetingParticipant->id,
                            array_column($agenda['participants'], 'meeting_guest_id')
                        )
                        )
                    ))
                ) {
                    unset($dataCopy['meeting_agendas'][$agendaIndex]);
                } else {
                    if ($agenda['agenda_votes']) {
                        foreach ($agenda['agenda_votes'] as $index => $vote) {
                            if (
                                !($vote['vote_participants'] && (((!isset($meetingParticipant->meeting_id)) && in_array(
                                    $meetingParticipant->id,
                                    array_column($vote['vote_participants'], 'user_id')
                                )
                                )
                                    || (isset($meetingParticipant->meeting_id) && in_array(
                                        $meetingParticipant->id,
                                        array_column($vote['vote_participants'], 'meeting_guest_id')
                                    )
                                    )
                                ))
                            ) {
                                unset($dataCopy['meeting_agendas'][$agendaIndex]['agenda_votes'][$index]);
                            }
                        }
                    }
                }
            }
        }

        $pdfAr = PDF::loadView(
            $mailFolderName . '.pdf-template-min-ar',
            ['data' => $dataCopy, 'meetingMom' => $meetingMom],
            [],
            'UTF-8'
        );

        $pdfEn = PDF::loadView(
            $mailFolderName . '.pdf-template-min',
            ['data' => $dataCopy, 'meetingMom' => $meetingMom],
            [],
            'UTF-8'
        );

        $this->emailHelper->sendMOM(
            $meetingParticipant->email,
            $dataCopy,
            $meetingParticipant->name_ar,
            $meetingParticipant->name,
            $emailData["meeting_title_ar"],
            $emailData["meeting_title_en"],
            $emailData["meeting_venue_ar"],
            $emailData["meeting_venue_en"],
            $emailData["meeting_schedule_from"],
            $emailData["meeting_schedule_to"],
            $pdfAr,
            $pdfEn,
            $meetingParticipant->language_id,
            $meeting->is_mom_pdf,
            $meeting->mom_pdf_file_name,
            $meeting->mom_pdf_url
        );
    }

    public function draftMeeting(Request $request, int $meetingId)
    {
        $data = $request->all();

        $meeting = $this->meetingService->getById($data['id']);

        if ($meeting && $meeting->meeting_status_id == config('meetingStatus.cancel')) {
            $status = ['meeting_status_id' => config('meetingStatus.draft')];
            $this->meetingService->update($data['id'], $status);

            return response()->json(['message' => 'Meeting Un cancelled successuflly'], 200);
        }

        return response()->json(['error' => 'Meeting Can\'t Undo cancel'], 400);
    }

    public function previewMom($meetingId, $lang)
    {
        $meeting = $this->meetingService->getById($meetingId);

        $pdf = null;
        if ($meeting->is_signature_sent) {
            $user = $this->securityHelper->getCurrentUser();
            $file = $this->signatureHelper->getDocumentBinary($user->organization, $meeting->document_id, $user->email);
            return $file;
        } else {
            $meetingAllData = $this->meetingService->getMeetingDataForPdfTemplate($meetingId)->toArray();
            $meetingAllData['canSignParticipants'] = collect($meetingAllData['meeting_participants'])->where('can_sign', 1);
            $meetingAllData['meeting_schedule_date_from'] = Carbon::parse($meetingAllData['meeting_schedule_from'])->format('Y-m-d');
            $meetingAllData['meeting_schedule_time_from'] = Carbon::parse($meetingAllData['meeting_schedule_from'])->format('g:i A');
            $meetingMom = $this->momService->getMeetingMom($meetingId);
            if ($meeting->is_mom_pdf) {
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

                try {
                    $pdfAr = PDF::loadView($mailFolderName . '.pdf-template-min-sign-ar', ['data' => $meetingAllData, 'meetingMom' => $meetingMom], []);
                    $pdfEn = PDF::loadView($mailFolderName . '.pdf-template-min-sign', ['data' => $meetingAllData, 'meetingMom' => $meetingMom], []);
                } catch (\Exception $e) {
                    report($e);
                    return response()->json(['error' => 'There is an error in your mom template , Please check', 'error_ar' => 'يوجد خطأ فى نموذج محضر الإجتماع , برجاء المراجعة'], 400);
                }
                $pdf = $meetingMom->language_id == config('languages.ar') ? $pdfAr : $pdfEn;
                $path = 'temp.pdf';
                Storage::disk('local')->put($path, $pdf->output());
                $pdfmerged = new \LynX39\LaraPdfMerger\PdfManage;
                $pdfmerged->init();
                $pdfmerged->addPDF(public_path() . '/' . $meeting->mom_pdf_url, 'all');
                $pdfmerged->addPDF(Storage::disk('local')->path($path), 'all');
                $pdfmerged->merge();
                $pdf = $pdfmerged->save('new.pdf', 'string');
            } else {
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

                try {
                    $pdfAr = PDF::loadView(
                        $mailFolderName . '.pdf-template-min-ar',
                        ['data' => $meetingAllData, 'meetingMom' => $meetingMom],
                        []
                    );
                    $pdfEn = PDF::loadView(
                        $mailFolderName . '.pdf-template-min',
                        ['data' => $meetingAllData, 'meetingMom' => $meetingMom],
                        []
                    );
                } catch (\Exception $e) {
                    report($e);
                    return response()->json(['error' => 'There is an error in your mom template , Please check', 'error_ar' => 'يوجد خطأ فى نموذج محضر الإجتماع , برجاء المراجعة'], 400);
                }
                $pdf = ($meetingMom->language_id ?? config('languages.ar')) == config('languages.ar') ? $pdfAr->output() : $pdfEn->output();
            }
            //get can sign participants
            $canSignParticipants = $meeting->meetingParticipants
                ->where("pivot.can_sign", true);

            $participantEmails = $canSignParticipants->map(function ($item, $key) {
                $hasNickName = $item['nickname_id'] == null ? false : true;
                return ["hasNick" => $hasNickName, "email" => $item["email"], "phone" => $item['user_phone']];
            })->toArray();

            $user = $this->securityHelper->getCurrentUser();
            $docId = $this->signatureHelper->createDocument($user->organization, $pdf, $participantEmails, $meeting->creator->language_id);
		//dd($user->organization, $pdf, $participantEmails, $meeting->creator->language_id);
		//var_dump($docId);
		//die();
            if ($docId != null) {
                $file = $this->signatureHelper->getDocumentBinary($user->organization, $docId, $user->email);
                return $file;
            }
        }

        return response()->json(['error' => 'Somthing went wrong', 'error_ar' => 'حدث خطأ'], 404);
    }

    public function sendSignatureToAllParticipants(Request $request, int $meetingId)
    {
        $data = $request->all();
        $meeting = $this->meetingService->getById($data['meeting_id']);
        $user = $this->securityHelper->getCurrentUser();

        if ($meeting) {
            $emailData = $this->meetingHelper->prepareMeetingSignatureEmailData($meeting);
            $canSignParticipants = $meeting->meetingParticipants
                ->where('pivot.can_sign', true);
            $this->meetingService->update($data['meeting_id'], ['is_signature_sent' => 1]);
            if ($canSignParticipants->first()) {
                $meetingParticipant = $canSignParticipants->first();
                $this->meetingParticipantService->update($meetingParticipant->pivot->id, ['is_signature_sent' => 1]);
                $this->emailHelper->sendMeetingSignature($meetingParticipant->email, $meetingParticipant->name_ar, $meetingParticipant->name, $emailData['meeting_title_ar'], $emailData['meeting_title_en'], $emailData['id'], $meetingParticipant->language_id);
                // create and send notification
                $notificationData = $this->notificationHelper->prepareNotificationDataForMeeting($meeting, $user, config('meetingNotifications.sendSignature'), ['user_id' => $meetingParticipant->id]);
                $this->notificationService->sendNotification($notificationData);
            }

            return response()->json(['message' => 'Sent successuflly'], 200);
        }

        return response()->json(['error' => 'Can\'t Send signature'], 400);
    }

    public function sendSignatureToParticipant(Request $request, int $meetingId)
    {
        $data = $request->all();
        $meeting = $this->meetingService->getById($data['meeting_id']);
        $user = $this->securityHelper->getCurrentUser();

        if ($meeting) {
            $emailData = $this->meetingHelper->prepareMeetingSignatureEmailData($meeting);
            $meetingParticipant = $meeting->meetingParticipants->where("pivot.user_id", $data["user_id"]);
            $this->meetingService->update($data["meeting_id"], ['is_signature_sent' => 1]);
            if (count($meetingParticipant) > 0) {
                $meetingParticipant = array_values($meetingParticipant->toArray())[0];
                $this->meetingParticipantService->update($meetingParticipant['pivot']['id'], ['is_signature_sent' => 1, 'is_signature_sent_individualy' => 1]);
                $this->emailHelper->sendMeetingSignature($meetingParticipant['email'], $meetingParticipant['name_ar'], $meetingParticipant['name'], $emailData['meeting_title_ar'], $emailData['meeting_title_en'], $emailData['id'], $meetingParticipant['language_id']);
                // create and send notification
                $notificationData = $this->notificationHelper->prepareNotificationDataForMeeting($meeting, $user, config('meetingNotifications.sendSignature'), ['user_id' => $meetingParticipant['id']]);
                $this->notificationService->sendNotification($notificationData);
            }

            return response()->json(['message' => 'Sent successuflly'], 200);
        }

        return response()->json(['error' => 'Can\'t Send signature'], 400);
    }

    public function sendSignatureToNextParticipantCronJob(Request $request)
    {
        $meetings = $this->meetingService->getMeetingsToSignWithNextParticipant();

        $results = $this->meetingService->checkNextParticipantToSend($meetings);

        foreach ($results as $key => $value) {
            $emailData = $this->meetingHelper->prepareMeetingSignatureEmailData($value['meeting']);
            $meetingParticipant = $value['participant'];
            $this->meetingParticipantService->update($meetingParticipant->pivot->id, ['is_signature_sent' => 1]);
            $this->emailHelper->sendMeetingSignature($meetingParticipant->email, $meetingParticipant->name_ar, $meetingParticipant->name, $emailData['meeting_title_ar'], $emailData['meeting_title_en'], $emailData['id'], $meetingParticipant->language_id);
        }
    }

    public function loginUserToSignature(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($data['meeting_id']);
        $userToken = $this->signatureHelper->loginUser($user->organization, $user->email, $meeting->document_id);
        if ($userToken != null) {
            return response()->json(['token' => $userToken, 'timeZone' => $user->organization->timeZone->diff_hours], 200);
        }

        return response()->json(['error' => 'Somthing went wrong', 'error_ar' => 'حدث خطأ'], 404);
    }

    public function signMOM(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, Meeting::rules('signature-callback'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        if (!$data['IsApproval']) {
            $meeting = $this->meetingService->getByDocumentId($data['document_id']);
            if ($meeting) {
                $user = $this->userService->getUserByEmail($data['email']);
                if ($user) {
                    $currentParticipant = $meeting->meetingParticipants->where('id', $user->id)->first();
                    if ($currentParticipant) {
                        $this->meetingParticipantService->update($currentParticipant->pivot->id, ['is_signed' => $data['is_signed'], 'signature_comment' => $request->comment]);
                        $meetingAfterUpdate = $this->meetingService->getById($meeting->id);
                        $results = $this->meetingService->checkNextParticipantToSend([$meetingAfterUpdate]);
                        foreach ($results as $key => $value) {
                            $emailData = $this->meetingHelper->prepareMeetingSignatureEmailData($value['meeting']);
                            $meetingParticipant = $value['participant'];
                            $this->meetingParticipantService->update($meetingParticipant->pivot->id, ['is_signature_sent' => 1]);
                            $this->emailHelper->sendMeetingSignature($meetingParticipant->email, $meetingParticipant->name_ar, $meetingParticipant->name, $emailData['meeting_title_ar'], $emailData['meeting_title_en'], $emailData['id'], $meetingParticipant->language_id);
                        }
                        $currentParticipant = $meetingAfterUpdate->meetingParticipants->where('id', $user->id)->first();
                        //send email to organizers
                        $emailData = $this->meetingHelper->prepareMeetingSignatureEmailData($meetingAfterUpdate);
                        if ($data['is_signed'] == 1) {
                            foreach ($meetingAfterUpdate->meetingOrganisers as $key => $meetingOrganiser) {
                                $this->emailHelper->sendParticipantSignedYesEmail($meetingOrganiser->email, $meetingOrganiser->name_ar, $meetingOrganiser->name, $emailData['meeting_title_ar'], $emailData['meeting_title_en'], $currentParticipant->name, $currentParticipant->name_ar, $currentParticipant->pivot->signature_comment, $meetingOrganiser->language_id);
                            }
                        } elseif ($data['is_signed'] == 0) {
                            foreach ($meetingAfterUpdate->meetingOrganisers as $key => $meetingOrganiser) {
                                $this->emailHelper->sendParticipantSignedNoEmail($meetingOrganiser->email, $meetingOrganiser->name_ar, $meetingOrganiser->name, $emailData['meeting_title_ar'], $emailData['meeting_title_en'], $currentParticipant->name, $currentParticipant->name_ar, $currentParticipant->pivot->signature_comment, $meetingOrganiser->language_id);
                            }
                        }

                        return response()->json(true, 200);
                    }
                }

                return response()->json(['message' => ['Not Allowed']], 401);
            } else {
                $decision = $this->voteService->getByDocumentId($data['document_id']);
                if ($decision) {
                    $user = $this->userService->getUserByEmail($data['email']);
                    if ($user) {
                        $currentVoter = $decision->voteResults->where('user_id', $user->id)->first();
                        if ($currentVoter) {
                            $this->voteResultService->update($currentVoter->id, ['is_signed' => $data['is_signed'], 'signature_comment' => $request->comment]);
                            // $voteResult = $this->voteResultService->getById($currentVoter->id);
                            // $notificationData = $this->notificationHelper->prepareNotificationDataForCircularDecision($decision, $user, config('decisionNotification.changeVote'), ['vote_status_id' => $voteResult->vote_status_id]);
                            // $this->notificationService->sendNotification($notificationData);
                            return response()->json(true, 200);
                        }
                    }

                    return response()->json(['message' => ['Not Allowed']], 401);
                }
            }

            return response()->json(['error' => 'Meeting Not Found', 'error_ar' => 'الاجتماع غير موجود'], 404);
        } else {
            $approval = $this->approvalService->getApprovalByDocumentId($data['document_id']);
            if ($approval) {
                $user = $this->userService->getUserByEmail($data['email']);
                if ($user) {
                    $this->approvalService->signApproval($data, $approval, $user);
                    return response()->json(true, 200);
                }
                return response()->json(['message' => ['Not Allowed']], 401);
            }
            return response()->json(['error' => 'Approval Not Found', 'error_ar' => 'الموافقة غير موجود'], 404);
        }

    }

    public function meetingCommitteeUsers(Request $request, int $meetingId)
    {
        $users = $this->meetingService->getMeetingCommitteeUsers($meetingId, $request->name);

        return response()->json($users, 200);
    }

    public function getMeetingRemindersForEmail()
    {
        $data = $this->meetingService->getMeetingRemindersForEmail();
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $notificationData = $this->notificationHelper->prepareReminderNotificationData($value);
                $this->eventHelper->fireEvent($notificationData, 'App\Events\SendNotificationEvent');
                // $emailData = $this->meetingHelper->prepareMeetingPublishedEmailData($value);
                // $this->emailHelper->sendMeetingReminderMail($value["email"], $value["name_ar"], $value["name"], $emailData["meeting_title_ar"], $emailData["meeting_title_en"], $emailData["meeting_venue_ar"], $emailData["meeting_venue_en"], $emailData["meeting_schedule_from"]);
            }
        }
    }

    public function cancelMeeting(Request $request, int $meetingId)
    {
        $data = $request->all();

        $meeting = $this->meetingService->getById($data['id']);

        if ($meeting && $meeting->meeting_status_id != config('meetingStatus.end')) {
            $status = ['meeting_status_id' => config('meetingStatus.cancel')];
            $this->meetingService->update($data['id'], $status);

            return response()->json(['message' => 'Meeting canceled successuflly'], 200);
        }

        return response()->json(['error' => 'Meeting Can\'t be Canceled'], 400);
    }

    public function getCurrentList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if (!$user) {
            return response()->json(['error' => 'Don\'t have access!'], 400);
        }

        $meetingList = $this->meetingService->getCurrentPreviousList($filter, $user->organization_id, $user->id, config('meetingDashboardTab.current'));

        $canViewAttendee = true;

        $meetingList = $this->meetingHelper->addCanViewAttendeeToResults($meetingList, $canViewAttendee);

        return response()->json($meetingList, 200);
    }

    public function getPreviousList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if (!$user) {
            return response()->json(['error' => 'Don\'t have access!'], 400);
        }

        $meetingList = $this->meetingService->getCurrentPreviousList($filter, $user->organization_id, $user->id, config('meetingDashboardTab.previous'));

        $canViewAttendee = true;

        $meetingList = $this->meetingHelper->addCanViewAttendeeToResults($meetingList, $canViewAttendee);

        return response()->json($meetingList, 200);
    }

    public function getUpComingList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if (!$user) {
            return response()->json(['error' => 'Don\'t have access!'], 400);
        }

        $meetingList = $this->meetingService->getCurrentPreviousList($filter, $user->organization_id, $user->id, config('meetingDashboardTab.upcoming'));

        return response()->json($meetingList, 200);
    }

    public function getTodayMeetingList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if (!$user) {
            return response()->json(['error' => 'Don\'t have access!'], 400);
        }

        $meetingList = $this->meetingService->getCurrentPreviousList($filter, $user->organization_id, $user->id, config('meetingDashboardTab.today'));

        return response()->json($meetingList, 200);
    }

    public function checkScheduleConflict(Request $request, int $meetingId)
    {
        $data = $request->all();

        $meeting = $this->meetingService->getById($meetingId);

        if ($meeting) {
            $conflicts = $this->meetingService->checkScheduleConflict($data['participant_ids'], $meetingId, $meeting->meeting_schedule_from, $meeting->meeting_schedule_to);

            return response()->json($conflicts, 200);
        }

        return response()->json(['error' => 'Meeting Not Found', 'error_ar' => 'الاجتماع غير موجود'], 404);
    }

    public function getMeetingAllData(Request $request, int $meetingId, int $currentPresentedAttachmentId = null)
    {
        $user = $this->securityHelper->getCurrentUser();
        $meetingAllData = $this->meetingService->getMeetingAllData($meetingId, $user, $currentPresentedAttachmentId);
        if (isset($user->meeting_guest_id)) {
            $participant = $this->meetingParticipantService->getMeetingGuest($meetingId, $user->meeting_guest_id);
        } else {
            $participant = $this->meetingParticipantService->getMeetingParticipant($meetingId, $user->id);
        }

        if ($participant) {
            $meetingAllData['participant_meeting_attendance_status_id'] = $participant->meeting_attendance_status_id;
            $meetingAllData['participant_meeting_attendance_status_name_ar'] = $participant->meeting_attendance_status_name_ar;
            $meetingAllData['participant_meeting_attendance_status_name_en'] = $participant->meeting_attendance_status_name_en;
            $meetingAllData['can_vote'] = true;
            $meetingAllData['can_attend'] = true;
            if ($participant->is_signature_sent == 1 && $participant->is_signed === null) {
                $meetingAllData['can_sign'] = 1;
            } else {
                $meetingAllData['can_sign'] = 0;
            }
        } else {
            $meetingAllData['participant_meeting_attendance_status_id'] = null;
            $meetingAllData['can_vote'] = false;
            $meetingAllData['can_attend'] = false;
            $meetingAllData['can_sign'] = 0;
        }

        $meetingAllData['can_view_attendee'] = true;
        $meetingAllData['can_view_recommendation'] =
            $meetingAllData['meeting_status_id'] == config('meetingStatus.sendRecommendation')
            && config('customSetting.meetingRecommendationsFeature');

        $meetingOrganiserIds = array_column($meetingAllData->meetingOrganisers->toArray(), 'id');
        $meetingParticipantIds = array_column($meetingAllData->meetingParticipants->toArray(), 'id');
        $meetingAllData['meetingMemberIds'] = array_merge($meetingParticipantIds, $meetingOrganiserIds);
        $meetingAllData['show_timer'] = ($meetingAllData['meeting_status_id'] != config('meetingStatus.end') &&
            $meetingAllData['meeting_status_id'] != config('meetingStatus.cancel') && $meetingAllData['meeting_status_id'] != config('meetingStatus.draft')) ? true : false;

        return response()->json($meetingAllData, 200);
    }

    public function getMeetingsForUserByMonth(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $userMeetings = $this->meetingService->getMeetingsForUserByMonth($user->id, $data['month'], $data['year'], null);

        return response()->json($userMeetings, 200);
    }

    public function getMeetingsForOrganizationByMonth(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        if ($user->role_id == config('roles.admin')) {
            $organizationId = $data['organization_id'];
            $userMeetings = $this->meetingService->getMeetingsForUserByMonth($user->id, $data['month'], $data['year'], $organizationId);

            return response()->json($userMeetings, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }

    public function getParticipantMeetingStatistics(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            $numberOfParticipantMeetings = $this->meetingService->getNumberOfParticipantMeetings($user->id, $user->organization_id);
            $participantMeetingStatistics = $this->meetingService->getParticipantMeetingStatistics($user->id, $user->organization_id);
            $statisticsData = array_merge($numberOfParticipantMeetings->toArray(), $participantMeetingStatistics);

            return response()->json($statisticsData, 200);
        }
    }

    public function getCurrentPresentingAttachment(Request $request, int $meetingId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        $meetingParticipants = $meeting->meetingParticipants;
        $meetingParticipantIds = array_column($meetingParticipants->toArray(), 'id');
        $meetingGuests = $this->meetingGuestService->getMeetingGuests($meetingId);
        $meetingGuestsIds = array_column($meetingGuests->toArray(), 'id');
        $meetingOrganisers = $meeting->meetingOrganisers;
        $meetingOrganiserIds = array_column($meetingOrganisers->toArray(), 'id');
        $meetingMemberIds = array_merge($meetingParticipantIds, $meetingOrganiserIds);
        $meetingMemberIds[] = $meeting->created_by;
        if ((!\in_array($user->id, $meetingMemberIds)) && !in_array($user?->meeting_guest_id, $meetingGuestsIds)) {
            return response()->json(['error' => 'You don\'t have access to this meeting', 'error_ar' => 'لا يمكنك الوصول إلى هذا الاجتماع'], 400);
        }
        if ($meeting) {
            $presenationData = $this->meetingService->getCurrentPresentingAttachment($meeting, $user);

            if ($presenationData) {
                return response()->json($presenationData, 200);
            } else {
                return response()->json(['error' => 'No current presentation'], 400);
            }
        }

        return response()->json(['error' => 'Meeting Not Found', 'error_ar' => 'الاجتماع غير موجود'], 404);
    }

    public function downloadMomPdf(Request $request, int $meetingId, string $lang)
    {
        $meeting = $this->meetingService->getById($meetingId);

        if ($meeting) {
            $user = $this->securityHelper->getCurrentUser();
            $file = $this->signatureHelper->getDocumentBinary($user->organization, $meeting->document_id, $user->email);

            return $file;
        }

        return response()->json(['error' => 'Meeting still not ended'], 400);
    }

    public function getZoomMeetingStartUrl(int $meetingId)
    {
        $meeting = $this->meetingService->getById($meetingId);
        $meetingOnlineConfiguration = $meeting->meetingOnlineConfigurations()->first();
        if ($meeting->zoom_meeting_id && $meetingOnlineConfiguration) {
            $zoomConfiguration = $meetingOnlineConfiguration;
            $headerData = $this->zoomMeetingHelper->getHeaderZoomConfigration($zoomConfiguration);
            $response = ZoomConnector::getMeeting($meeting->zoom_meeting_id, $headerData);
            if ($response['is_success']) {
                return response()->json(['zoom_start_url' => $response['response']['start_url']], 200);
            }
        }

        return response()->json([
            'error' => 'Meeting doesn\'t have zoom strat url',
            'error_ar' => 'لا يوجد اجتماع على زوم لهذا الاجتماع'
        ], 400);
    }

    public function getMeetingUsers(int $meetingId)
    {
        $meeting = $this->meetingService->getbyId($meetingId);
        $meetingUsers = $meeting->meetingParticipants->load('image')->toArray();
        $meetingOrganisers = array_filter($meeting->meetingOrganisers->load('image')->toArray(), function ($user) use ($meetingUsers) {
            return !in_array($user['id'], array_column($meetingUsers, 'id'));
        });
        $meetingUsers = array_merge($meetingUsers, $meetingOrganisers);
        $meetingCreator = $meeting->creator->load('image');
        if (!in_array($meetingCreator['id'], array_column($meetingUsers, 'id'))) {
            $meetingUsers[] = $meetingCreator;
        }

        return response()->json($meetingUsers, 200);
    }

    public function publishMeetingChanges(Request $request, int $meetingId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getbyId($meetingId);
        if ($meeting) {
            $this->meetingService->publishMeetingVersion($meetingId);
            $this->updateChatGroupUsers($meeting);
            $this->eventHelper->fireEvent([], 'App\Events\MeetingDataChangedEvent');
            // create and send notification
            $notificationData = $this->notificationHelper->prepareNotificationDataForMeeting($meeting, $user, config('meetingNotifications.publishMeetingChanges'), []);
            $this->notificationService->sendNotification($notificationData);

            return response()->json([
                'message' => 'Meeting changes published successfully',
                'message_ar' => ' تم نشر التغيرات بنجاح'
            ], 200);
        } else {
            return response()->json([
                'error' => 'Meeting not found',
                'error_ar' => 'هذا الاجتماع غير موجود'
            ], 404);
        }
    }

    private function updateChatGroupUsers($meeting)
    {
        $user = $this->securityHelper->getCurrentUser();
        // update chat group users
        if ($meeting->chat_room_id && $user->chat_user_id) {
            //update chat group users
            $this->chatGroupService->updateMeetingChatGroupMeemerUsers($meeting);
            $this->chatService->updateMeetingRoom($user, $meeting);
        }
    }

    public function getMeetingMomTemplate(int $meetingId, int $momTemplateId)
    {
        $meeting = $this->meetingService->getMeetingDataForPdfTemplate($meetingId);
        $meetingAllData = $meeting->toArray();
        $meetingAllData['meeting_schedule_date_from'] = Carbon::parse($meetingAllData['meeting_schedule_from'])->format('Y-m-d');
        $meetingAllData['meeting_schedule_time_from'] = Carbon::parse($meetingAllData['meeting_schedule_from'])->format('g:i A');
        $momTemplate = $this->momTemplateService->getMomTemplateDetails($momTemplateId);
        $meetingAllData = $this->momHelper->renderMomTemplateData($meetingAllData, $momTemplate);

        // set mom of meeting
        $momView = $this->momHelper->getMomOfMeeting($meetingAllData, $meeting->creator->language_id);

        return response()->json($momView, 200);
    }

    public function getMeetingPercentage(int $meetingId)
    {
        $data = [];
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getMeetingDetails($meetingId, $user);

        $data['attend'] = $meeting['attend'];
        $data['totalParticipants'] = $meeting['totalParticipants'];
        $data['meeting_attendance_percentage'] = $meeting['meeting_attendance_percentage'];
        $data['show_attendance_percentage_warning'] = $meeting['meeting_attendance_percentage'] && (($meeting['attend'] / $meeting['totalParticipants'] * 100) < $meeting['meeting_attendance_percentage']) ? true : false;
        $isStakeholdersCommitte = false;
        if (config('customSetting.removeCommitteeCode') == false) {
            $isStakeholdersCommitte = $meeting['committee_id'] ==
                $this->committeeService->getOrganizationCommitteeByCode($user->organization_id, config('committee.stakeholders'))->id;
        }
        $totalShare = $this->stakeholderService->getTotalShares($user->organization_id)->total_share ?? 0;
        $data['meeting_stakeholders_percentage'] = $meeting['meeting_stakeholders_percentage'];
        $data['meeting_attendance_share'] = $this->stakeholderService->getMeetingAttendanceShare($meetingId)->attendance_share ?? 0;
        $data['meeting_participants_share'] = $this->stakeholderService->getMeetingParticipantsShare($meetingId)->participants_share ?? 0;

        $data['show_participants_share_percentage_warning'] = $totalShare > 0 && $isStakeholdersCommitte && $meeting['meeting_stakeholders_percentage'] && (($data['meeting_participants_share'] / $totalShare * 100) < $meeting['meeting_stakeholders_percentage']) ? true : false;
        $data['show_attendance_share_percentage_warning'] = $totalShare > 0 && $isStakeholdersCommitte && $meeting['meeting_stakeholders_percentage'] && (($data['meeting_attendance_share'] / $totalShare * 100) < $meeting['meeting_stakeholders_percentage']) ? true : false;

        return response()->json($data, 200);
    }

    public function getTasks(Request $request, int $id)
    {
        $filter = (object) ($request->all());
        $filter->SearchObject['meeting_id'] = $id;
        return response()->json($this->taskManagementService->getPagedList($filter), 200);
    }

    public function AddApprovalToMeeting(Request $request, int $id)
    {
        $data = $request->all();
        $errors = [];
        $validator = Validator::make($data, Approval::rules('save'), Approval::messages('save'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
            return response()->json(["error" => $errors], 400);
        }

        $data = $this->meetingService->createApprovalsForMeetingVersion($id, $data);
        $approval = $this->approvalService->create($data);
        if (isset($approval)) {
            return response($approval, 200);
        } else {
            return response([
                'error' => [
                    [
                        'message_ar' => 'فشل إضافة موافقة جديدة',
                        'message' => 'Failed to add new Approval'
                    ]
                ]
            ], 400);
        }
    }

    public function DeleteApprovalFromMeeting($meetingId, Approval $approval)
    {
        try {
            $this->meetingService->deleteApprovalsFormMeetingVersion($meetingId, $approval->id);
            return response([
                'message' => [
                    [
                        'message_ar' => 'تم حذف الموافقة',
                        'message' => 'Approval deleted successfully'
                    ]
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'error' => [
                    [
                        'message_ar' => 'فشل حذف الموافقة',
                        'message' => 'Failed to delete approval'
                    ]
                ]
            ], 400);
        }
    }

    public function UpdateApprovalToMeeting(Request $request, $meetingId, Approval $approval)
    {
        $data = $request->all();
        $success = false;
        $errors = [];
        $validator = Validator::make($data, Approval::rules('update'), Approval::messages('update'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
            return response()->json(["error" => $errors], 400);
        }

        // update master meeting
        $masterMeeting = $this->meetingService->getById($meetingId);

        // update version of meeting
        $versionOfMeeting = $this->meetingService->getUnpublishedVersionOfMeeting($meetingId);
        if ($versionOfMeeting) {
            $approval = $this->approvalService->update($approval->id, $data);
            $success = $approval != null;
        } else {
            // create version of this meeting
            $lastVersionOfMeeting = $this->meetingService->getLastVersionOfMeeting($meetingId);
            $meeting = $this->meetingService->createVersionOfMeetingFromMasterMeeting(
                $masterMeeting,
                $lastVersionOfMeeting,
                $approval->id,
                $data
            );
            $success = $meeting != null;
        }

        if (isset($success)) {
            return response([
                "Results" => $approval,
                "message" => [
                    'message_ar' => 'تم تعديل الموافقة',
                    'message' => 'Edit Approval successfully'
                ]
            ], 200);
        } else {
            return response([
                'error' => [
                    [
                        'error_ar' => 'فشل تعديل موافقة',
                        'error' => 'Failed to edit Approval'
                    ]
                ]
            ], 400);
        }
    }

    public function sendMeetingRecommendations(Request $request, int $meetingId)
    {
        $meeting = $this->meetingService->getById($meetingId);
        $user = $this->securityHelper->getCurrentUser();
        if ($meeting && $meeting->meeting_status_id == config('meetingStatus.end')) {
            $status = ['meeting_status_id' => config('meetingStatus.sendRecommendation')];
            $this->meetingService->update($meetingId, $status);

            // set mom of meeting
            $meetingDataforPdf = $this->meetingService->getMeetingDataForPdfTemplate($meetingId);
            $meetingAllData = $meetingDataforPdf->toArray();
            $momView = $this->momHelper->getMomOfMeeting($meetingAllData, $meeting->creator->language_id);
            $this->momService->create($momView);

            $emailData = $this->meetingHelper->prepareMeetingPublishedEmailData($meeting);
            $meetingOrganisers = $meeting->meetingOrganisers;
            $participants = $meeting->meetingParticipants;
            foreach ($meetingOrganisers as $user) {
                $this->emailHelper->sendMeetingRecommendation(
                    $user->email,
                    $user->name_ar,
                    $user->name,
                    $emailData["meeting_title_ar"],
                    $emailData["meeting_title_en"],
                    $emailData["meeting_venue_ar"],
                    $emailData["meeting_venue_en"],
                    $emailData["meeting_schedule_from"],
                    $user->language_id
                );
            }

            foreach ($participants as $user) {
                $this->emailHelper->sendMeetingRecommendation(
                    $user->email,
                    $user->name_ar,
                    $user->name,
                    $emailData["meeting_title_ar"],
                    $emailData["meeting_title_en"],
                    $emailData["meeting_venue_ar"],
                    $emailData["meeting_venue_en"],
                    $emailData["meeting_schedule_from"],
                    $user->language_id
                );
            }

            // create and send notification
            $notificationData = $this->notificationHelper
            ->prepareNotificationDataForMeeting($meeting, $user, config('meetingNotifications.sendRecommendation'), []);
            $this->notificationService->sendNotification($notificationData);

            return response()->json(['message' => 'Meeting Recommendations Sended successuflly'], 200);
        }

        return response()->json(['error' => 'Meeting recommendations sending failed'], 400);
    }

}
