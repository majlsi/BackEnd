<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\MeetingParticipantService;
use Services\MeetingService;
use Services\MeetingParticipantAlternativeService;
use Services\ChatService;
use Services\ChatGroupService;
use Services\CommitteeUserService;
use Services\NotificationService;
use Services\RoleService;
use Models\MeetingParticipant;
use Helpers\SecurityHelper;
use Helpers\EventHelper;
use Helpers\NotificationHelper;
use Models\MeetingParticipantAlternative;
use Validator;
use Illuminate\Support\Facades\Lang;
use Services\MeetingGuestService;

class MeetingParticipantController extends Controller
{

    private $meetingParticipantService;
    private $meetingService;
    private $securityHelper;
    private $eventHelper;
    private $meetingParticipantAlternativeService;
    private $chatService;
    private $chatGroupService;
    private $committeeUserService;
    private $notificationService;
    private $notificationHelper;
    private $roleService;
    private MeetingGuestService $meetingGuestService;

    public function __construct(
        MeetingParticipantService $meetingParticipantService,
        SecurityHelper $securityHelper,
        MeetingService $meetingService,
        EventHelper $eventHelper,
        ChatService $chatService,
        MeetingParticipantAlternativeService $meetingParticipantAlternativeService,
        ChatGroupService $chatGroupService,
        NotificationHelper $notificationHelper,
        CommitteeUserService $committeeUserService,
        NotificationService $notificationService,
        RoleService $roleService,
        MeetingGuestService $meetingGuestService
    ) {
        $this->meetingParticipantService = $meetingParticipantService;
        $this->securityHelper = $securityHelper;
        $this->meetingService = $meetingService;
        $this->eventHelper = $eventHelper;
        $this->chatService = $chatService;
        $this->meetingParticipantAlternativeService = $meetingParticipantAlternativeService;
        $this->chatGroupService = $chatGroupService;
        $this->committeeUserService = $committeeUserService;
        $this->notificationService = $notificationService;
        $this->notificationHelper = $notificationHelper;
        $this->roleService = $roleService;
        $this->meetingGuestService = $meetingGuestService;
    }

    public function getMeetingParticipantsForMeeting(int $meetingId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        if ($user && $user->organization_id === $meeting->organization_id) {
            return response()->json($this->meetingParticipantService->getMeetingParticipantsForMeeting($meetingId), 200);
        } else {
            return response()->json(['error' => 'You don\'t have access'], 400);
        }
    }

    public function getMeetingParticipants(int $meetingId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        $versionOfMeeting = $this->meetingService->getUnpublishedVersionOfMeeting($meetingId);
        if ($user && $user->organization_id === $meeting->organization_id) {
            return response()->json($this->meetingParticipantService->getMeetingParticipantsForMeeting($versionOfMeeting ? $versionOfMeeting->id : $meetingId), 200);
        } else {
            return response()->json(['error' => 'You don\'t have access'], 400);
        }
    }

    public function storeMeetingParticipantsForMeeting(Request $request, int $meetingId)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        if ($user  && $user->organization_id === $meeting->organization_id) {
            $created = [];
            if (isset($data["guests"]) && count($data["guests"]) > 0) {
                $this->meetingGuestService->upsertGuests($meetingId, $data["guests"], $meeting->organization_id);
            }
            if (isset($data["members"]) && count($data["members"]) > 0) {
                $participantsIds = [];
                $participantRoleId = $this->roleService->getRoleByCode(config('roleCodes.participant'))->id;
                foreach ($data["members"] as $key => $participant) {
                    $participantsIds[$key]['user_id'] = $participant['id'];
                    $participantsIds[$key]['meeting_role_id'] = $participant["role_id"] != config('roles.organizationAdmin') ? $participant["role_id"]  : $participantRoleId;
                    $participantsIds[$key]['participant_order'] = $participant['order'];
                    $participantsIds[$key]['can_sign'] = $participant['can_sign'] ?? false;
                    $participantsIds[$key]['send_mom'] = $participant['send_mom'] ?? false;
                }
                if ($meeting->meeting_status_id != config('meetingStatus.end')) {
                    $created = $this->meetingService->createParticipantsForMeetingVersion($meetingId, $participantsIds);
                } else {
                    $created =  $this->meetingService->createMeetingParticipants($meetingId, $participantsIds);
                }
                //$this->eventHelper->fireEvent([], 'App\Events\MeetingDataChangedEvent');
                // update chat room users for meeting
                $meeting = $this->meetingService->getById($meetingId);
                if ($meeting->chat_room_id && $user->chat_user_id && $meeting->meeting_status_id == config('meetingStatus.draft')) {
                    //update chat group users
                    $this->chatGroupService->updateMeetingChatGroupMeemerUsers($meeting);
                    $this->chatService->updateMeetingRoom($user, $meeting);
                }
            }
            $versionOfMeeting = $this->meetingService->getUnpublishedVersionOfMeeting($meetingId);
            return response()->json(['meeting_participants' => $created, 'meeting_version_id' => $versionOfMeeting ? $versionOfMeeting->id : null], 200);
        } else {
            return response()->json(['error' => 'You don\'t have access'], 400);
        }
    }

    public function changeStatus(Request $request, int $meetingId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $meetingPaticipant = $this->meetingParticipantService->getMeetingParticipant($meetingId, $user->id);
        if ($meetingPaticipant) {
            return response()->json($this->meetingParticipantService->changeStatus($meetingPaticipant->id, $request->status_id), 200);
        }
        return response()->json(['error' => 'You don\'t have access'], 400);
    }

    public function attendMeenting(Request $request, int $meetingId)
    {
        $user = $this->securityHelper->getCurrentUser();
        return $this->setMeetingAttendanceStatus($meetingId, ['user_id' => $user->id], config('meetingAttendanceStatus.attend'), 'one', false);
    }

    public function absentMeenting(Request $request, int $meetingId)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        return $this->setMeetingAttendanceStatus($meetingId, ['user_id' => $user->id], config('meetingAttendanceStatus.absent'), 'one', false, $data);
    }

    public function mayAttendMeenting(Request $request, int $meetingId)
    {
        $user = $this->securityHelper->getCurrentUser();
        return $this->setMeetingAttendanceStatus($meetingId, ['user_id' => $user->id], config('meetingAttendanceStatus.mayAttend'), 'one', false);
    }


    public function updateParticipantAttendance(Request $request, int $meetingId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);

        if ($meeting->meeting_status_id == config('meetingStatus.end')) {
            $meetingPaticipant = $this->meetingParticipantService->getMeetingParticipant($meetingId, $user->id);
            return response()->json([
                'message' => 'Your meeting login is updated',
                'message_ar' => 'لقد تم تأكيد حضورك الاجتماع'
            ], 200);
        }
        return response()->json(['error' => 'You don\'t have access'], 400);
    }

    public function setAttendForMeentingParticipant(Request $request, int $meetingId)
    {
        $data = $request->all();
        return $this->setMeetingAttendanceStatus($meetingId, $data, config('meetingAttendanceStatus.attend'), 'one', true);
    }

    public function setAbsentForMeentingParticipant(Request $request, int $meetingId)
    {
        $data = $request->all();
        $reasonData['rejection_reason_comment'] = Lang::get('translation.meeting_attendance.absent_reason', [], 'ar');
        return $this->setMeetingAttendanceStatus($meetingId, $data, config('meetingAttendanceStatus.absent'), 'one', true, $reasonData);
    }

    public function setAttendForMeentingParticipants(Request $request, int $meetingId)
    {
        $data = $request->all();
        return $this->setMeetingAttendanceStatus($meetingId, $data, config('meetingAttendanceStatus.attend'), 'multi', true);
    }

    public function setAbsentForMeentingParticipants(Request $request, int $meetingId)
    {
        $data = $request->all();
        $reasonData['rejection_reason_comment'] = Lang::get('translation.meeting_attendance.absent_reason', [], 'ar');
        return $this->setMeetingAttendanceStatus($meetingId, $data, config('meetingAttendanceStatus.absent'), 'multi', true, $reasonData);
    }

    private function setMeetingAttendanceStatus($meetingId, $data, $meetingAttendanceStatusId, $numberOfParticipants, $sendNotification, $reasonData = null, $isAcceptAbsentByOrganiser = null)
    {
        $success = false;
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        $lastVersionOfMeeting = $this->meetingService->getLastVersionOfMeeting($meetingId);

        if (
            $meeting->meeting_status_id == config('meetingStatus.end') || $meeting->meeting_status_id == config('meetingStatus.draft') ||
            $meeting->meeting_status_id == config('meetingStatus.cancel')
        ) {
            return response()->json(['error' => 'You can\'t change attendance status', 'error_ar' => 'لا يمكنك تغيير حالة الحضور'], 400);
        }

        if ($numberOfParticipants == 'one') {
            if(isset($data['user_id'])){
                $masterMeetingPaticipant = $this->meetingParticipantService->getMeetingParticipant($meetingId, $data['user_id']);
                $success = $this->meetingParticipantService->setMeetingAttendanceStatusForOneParticipant($meetingId, $lastVersionOfMeeting, $data['user_id'], $meetingAttendanceStatusId, $success, $reasonData, $isAcceptAbsentByOrganiser);
                if ($masterMeetingPaticipant && $sendNotification && ($masterMeetingPaticipant->meeting_attendance_status_id != $meetingAttendanceStatusId || ($masterMeetingPaticipant->meeting_attendance_status_id == $meetingAttendanceStatusId && $isAcceptAbsentByOrganiser != $masterMeetingPaticipant->is_accept_absent_by_organiser))) {
                    $notificationData = $this->notificationHelper->prepareNotificationDataForMeeting($meeting, $user, config('meetingNotifications.attendanceChangedByOrganiser'), ['user_id' => $data['user_id'], 'meeting_attendance_status' => $isAcceptAbsentByOrganiser ? null : $meetingAttendanceStatusId]);
                    $this->notificationService->sendNotification($notificationData);
                }
            } else if(isset($data['meeting_guest_id'])){
                $guest = $this->meetingGuestService->getById($data['meeting_guest_id']);
                if(isset($guest)){
                    $success = $this->meetingGuestService->setMeetingAttendanceStatus($guest->id, $meetingAttendanceStatusId, $isAcceptAbsentByOrganiser);
                }
            }
        } else if ($numberOfParticipants == 'multi') {
            if (isset($data['users_ids'])) {
                foreach ($data['users_ids'] as $key => $userId) {
                    $masterMeetingPaticipant = $this->meetingParticipantService->getMeetingParticipant($meetingId, $userId);
                    $success = $this->meetingParticipantService->setMeetingAttendanceStatusForOneParticipant($meetingId, $lastVersionOfMeeting, $userId, $meetingAttendanceStatusId, $success, $reasonData, $isAcceptAbsentByOrganiser);
                    if ($masterMeetingPaticipant && $sendNotification && ($masterMeetingPaticipant->meeting_attendance_status_id != $meetingAttendanceStatusId || ($masterMeetingPaticipant->meeting_attendance_status_id == $meetingAttendanceStatusId && $isAcceptAbsentByOrganiser != $masterMeetingPaticipant->is_accept_absent_by_organiser))) {
                        $notificationData = $this->notificationHelper->prepareNotificationDataForMeeting($meeting, $user, config('meetingNotifications.attendanceChangedByOrganiser'), ['user_id' => $userId, 'meeting_attendance_status' => $isAcceptAbsentByOrganiser ? null : $meetingAttendanceStatusId]);
                        $this->notificationService->sendNotification($notificationData);
                    }
                }
            } 
            if (isset($data['meeting_guests_ids'])){
                foreach ($data['meeting_guests_ids'] as $meeting_guest_id) {
                    $guest = $this->meetingGuestService->getById($meeting_guest_id);
                    if (isset($guest)) {
                        $success = $this->meetingGuestService->setMeetingAttendanceStatus($guest->id, $meetingAttendanceStatusId, $isAcceptAbsentByOrganiser);
                    }
                }
            }
        }

        if ($success) {
            $this->eventHelper->fireEvent([], 'App\Events\MeetingDataChangedEvent');
            return response()->json(['message' => 'Attendance status updated successfully', 'message_ar' => 'تم تحديث حالة الحضور بنجاح'], 200);
        }
        return response()->json(['error' => 'This participant not found', 'error_ar' => 'هذا العضو غير موجود'], 400);
    }

    public function setAcceptAbsentForMeentingParticipant(Request $request, int $meetingId)
    {
        $data = $request->all();
        $reasonData['rejection_reason_comment'] = Lang::get('translation.meeting_attendance.absent_reason', [], 'ar');
        return $this->setMeetingAttendanceStatus($meetingId, $data, config('meetingAttendanceStatus.absent'), 'one', true, $reasonData, true);
    }

    public function setAcceptAbsentForMeentingParticipants(Request $request, int $meetingId)
    {
        $data = $request->all();
        $reasonData['rejection_reason_comment'] = Lang::get('translation.meeting_attendance.absent_reason', [], 'ar');
        return $this->setMeetingAttendanceStatus($meetingId, $data, config('meetingAttendanceStatus.absent'), 'multi', true, $reasonData, true);
    }
}
