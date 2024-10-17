<?php

namespace App\Http\Controllers;

use Helpers\AttachmentHelper;
use Helpers\EventHelper;
use Helpers\SecurityHelper;
use Helpers\UploadHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Services\AttachmentService;
use Services\MeetingAgendaService;
use Services\MeetingService;
use TCPDF;
use Illuminate\Support\Facades\Input;

class AttachmentController extends Controller
{

    private $attachmentService;
    private $meetingService;
    private $securityHelper;
    private $attachmentHelper;
    private $eventHelper;
    private $meetingAgendaService;

    public function __construct(AttachmentService $attachmentService, SecurityHelper $securityHelper,
        MeetingService $meetingService, AttachmentHelper $attachmentHelper, EventHelper $eventHelper,
        MeetingAgendaService $meetingAgendaService) {
        $this->attachmentService = $attachmentService;
        $this->securityHelper = $securityHelper;
        $this->meetingService = $meetingService;
        $this->attachmentHelper = $attachmentHelper;
        $this->eventHelper = $eventHelper;
        $this->meetingAgendaService = $meetingAgendaService;
    }

    public function getAttachmentsForMeeting(int $meetingId)
    {
        return response()->json($this->attachmentService->getAttachmentsForMeeting($meetingId), 200);
    }

    public function setAttachmentsForMeeting(Request $request, int $meetingId)
    {
        $data = $request->all();
        $created = $this->meetingService->createAttachmentsForMeetingVersion($meetingId, $data['attachments']);
        //$this->eventHelper->fireEvent([], 'App\Events\MeetingDataChangedEvent');
        $versionOfMeeting = $this->meetingService->getUnpublishedVersionOfMeeting($meetingId);
        return response()->json(['meeting_attachemnts' => $created, 'meeting_version_id' => $versionOfMeeting ? $versionOfMeeting->id : null], 200);
    }

    public function destroy($meetingId, $attachmentId)
    {
        $attachment = $this->attachmentService->getById($attachmentId);
        if ($attachment) {
            $url = $attachment["attachment_url"];
            $this->attachmentService->delete($attachmentId);
            File::delete(public_path($url));
            return response()->json(['message' => 'Attachment deleted successfully'], 200);
        }
        return response()->json(['error' => 'Attachment can\'t deleted'], 400);
    }

    public function show($id)
    {
        $attachment = $this->attachmentService->getById($id);
        if ($attachment) {
            $attachmentAgenda = $attachment->meetingAgenda;
            if ($attachmentAgenda) {

                $meetingId = $attachmentAgenda->meeting_id;
                $meeting = $this->meetingService->getById($meetingId);

                $chatGuests = $meeting->guests->toArray();
                $chatGuestsIds = array_column($chatGuests, 'id');
                $attachment['chatGuestsIds'] = $chatGuestsIds;

                $meetingParticipants = $meeting->meetingParticipants;
                $meetingParticipantIds = array_column($meetingParticipants->toArray(), 'id');
                $meetingOrganisers = $meeting->meetingOrganisers;
                $meetingOrganiserIds = array_column($meetingOrganisers->toArray(), 'id');
                $meetingMemberIds = array_unique(array_merge($meetingParticipantIds, $meetingOrganiserIds));
                $attachment->meetingMemberIds = $meetingMemberIds;
                /*      $redis = Redis::connection();
                $presentationNotes = $redis->get('presentation_notes_'.$attachment->id);
                $attachment->presentation_notes = json_decode($presentationNotes); */

                $attachmentObj = $attachment->load('presenterGuest')->load('presenter')->load('meetingAgenda');
                return response()->json($attachmentObj, 200);
            }
        }
        return response()->json(['error' => 'Attachment not found', 'error_ar' => 'مرفق غير موجود'], 404);
    }

    public function getAttachmentSlides($attachmentId)
    {
        $attachment = $this->attachmentService->getById($attachmentId);

        if ($attachment) {
            $data = $this->attachmentService->getAttachmentSlides($attachment);
            return response()->json($data, 200);
        }
        return response()->json(['error' => 'Attachment not found', 'error_ar' => 'مرفق غير موجود'], 404);

    }

    public function presentAttachment(Request $request, int $meetingId, int $attachmentId)
    {
        $data = $request->all();
        $attachment = $this->attachmentService->getById($attachmentId);

        if (!$attachment) {
            return response()->json(['error' => 'Attachment not found', 'error_ar' => 'مرفق غير موجود'], 404);
        }
        $meeting = $this->meetingService->getById($meetingId);
        if (!$meeting) {
            return response()->json(['error' => 'Meeting not found', 'error_ar' => 'الاجتماع غير موجود'], 404);
        }

        $currentUser = $this->securityHelper->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['error' => 'User not found!'], 404);
        }
        if ($meeting->meeting_status_id != config('meetingStatus.start')) {
            return response()->json(['error' => 'Can\'t present attachment, Meeting still not started', 'error_ar' => 'لا يمكن تقديم المرفق ، الاجتماع لم يبدأ بعد'], 400);
        }
        $meetingAgendaPresenterIds = array_column($attachment->meetingAgenda->presentersAgenda->toArray(), 'id');
        $meetingAgendaOrganisersIds = array_column($meeting->meetingOrganisers->toArray(), 'id');
        $meetingAgendaGuestPresentersIds = array_column($attachment->meetingAgenda->presenters->toArray(), 'meeting_guest_id');
        if (
            !\in_array($currentUser->id, $meetingAgendaPresenterIds) && (!\in_array($currentUser->id, $meetingAgendaOrganisersIds)) && $currentUser->id != $meeting->created_by
            && (!\in_array($currentUser->meeting_guest_id, $meetingAgendaGuestPresentersIds))
        ) {
            return response()->json(['error' => 'Can\'t present attachment, You are not meeting agenda presenter', 'error_ar' => 'لا يمكن تقديم المرفقات ، فأنت لست مقدم اجتماع جدول أعمال'], 400);
        }
        $currentPresentationData = $this->meetingService->getCurrentPresentingAttachment($meeting, $currentUser);

        if ($currentPresentationData && $currentPresentationData['attachmentId'] != $attachmentId && (!\in_array($currentUser->id, $meetingAgendaOrganisersIds)) && $currentUser->id != $meeting->created_by) {
            return response()->json(['error' => 'Can\'t present attachment, There is another attachment is presented now', 'error_ar' => 'لا يمكن تقديم المرفق ، وهناك مرفق آخر معروض الآن'], 400);

        }
        if ($currentPresentationData && $currentPresentationData['attachmentId'] === $attachmentId && (!\in_array($currentUser->id, $meetingAgendaOrganisersIds)) && $currentUser->id != $meeting->created_by) {
            return response()->json(['error' => 'This attachment already presented', 'error_ar' => 'هذا المرفق يتم عرضه حاليا'], 400);
        }
        if ($currentPresentationData && $currentPresentationData['presenterUserId'] != $currentUser->id && (!\in_array($currentUser->id, $meetingAgendaOrganisersIds)) && $currentUser->id != $meeting->created_by) {
            return response()->json(['error' => 'Can\'t present attachment, There is another presenter present attachment', 'error_ar' => 'لا يمكن تقديم المرفقات ، هناك مرفق مقدم حاضر آخر'], 400);
        }

        $firingData = $this->attachmentService->presentAttachment($meeting, $currentUser, $attachment);
        $this->meetingAgendaService->updateAgendaTimerWhenStartPresentation($attachment->meeting_agenda_id);
        return response()->json(['message' => 'Can present attachment successfully', 'message_ar' => 'يمكن تقديم المرفق بنجاح'], 200);

    }

    public function presentAttachmentWithoutEndNotification(Request $request, int $meetingId, int $attachmentId)
    {
        $data = $request->all();
        $attachment = $this->attachmentService->getById($attachmentId);

        if (!$attachment) {
            return response()->json(['error' => 'Attachment not found', 'error_ar' => 'مرفق غير موجود'], 404);
        }
        $meeting = $this->meetingService->getById($meetingId);
        if (!$meeting) {
            return response()->json(['error' => 'Meeting not found', 'error_ar' => 'الاجتماع غير موجود'], 404);
        }

        $currentUser = $this->securityHelper->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['error' => 'User not found!'], 404);
        }
        if ($meeting->meeting_status_id != config('meetingStatus.start')) {
            return response()->json(['error' => 'Can\'t present attachment, Meeting still not started', 'error_ar' => 'لا يمكن تقديم المرفق ، الاجتماع لم يبدأ بعد'], 400);
        }
        $meetingAgendaPresenterIds = array_column($attachment->meetingAgenda->presentersAgenda->toArray(), 'id');
        $meetingAgendaOrganisersIds = array_column($meeting->meetingOrganisers->toArray(), 'id');
        if (!\in_array($currentUser->id, $meetingAgendaPresenterIds) && (!\in_array($currentUser->id, $meetingAgendaOrganisersIds)) && $currentUser->id != $meeting->created_by) {
            return response()->json(['error' => 'Can\'t present attachment, You are not meeting agenda presenter', 'error_ar' => 'لا يمكن تقديم المرفقات ، فأنت لست مقدم اجتماع جدول أعمال'], 400);
        }
        $currentPresentationData = $this->meetingService->getCurrentPresentingAttachment($meeting, $currentUser);

        if ($currentPresentationData && $currentPresentationData['attachmentId'] != $attachmentId && (!\in_array($currentUser->id, $meetingAgendaOrganisersIds)) && $currentUser->id != $meeting->created_by) {
            return response()->json(['error' => 'Can\'t present attachment, There is another attachment is presented now', 'error_ar' => 'لا يمكن تقديم المرفق ، وهناك مرفق آخر معروض الآن'], 400);

        }
        if ($currentPresentationData && $currentPresentationData['attachmentId'] === $attachmentId && (!\in_array($currentUser->id, $meetingAgendaOrganisersIds)) && $currentUser->id != $meeting->created_by) {
            return response()->json(['error' => 'This attachment already presented', 'error_ar' => 'هذا المرفق يتم عرضه حاليا'], 400);
        }
        if ($currentPresentationData && $currentPresentationData['presenterUserId'] != $currentUser->id && (!\in_array($currentUser->id, $meetingAgendaOrganisersIds)) && $currentUser->id != $meeting->created_by) {
            return response()->json(['error' => 'Can\'t present attachment, There is another presenter present attachment', 'error_ar' => 'لا يمكن تقديم المرفقات ، هناك مرفق مقدم حاضر آخر'], 400);
        }

        $firingData = $this->attachmentService->presentAttachment($meeting, $currentUser, $attachment, true);
        $this->meetingAgendaService->updateAgendaTimerWhenStartPresentation($attachment->meeting_agenda_id);
        return response()->json(['message' => 'Can present attachment successfully', 'message_ar' => 'يمكن تقديم المرفق بنجاح'], 200);

    }

    public function fireSlideNotes(Request $request, int $attachmentId)
    {
        $data = $request->all();
        $attachment = $this->attachmentService->getById($attachmentId);

        if (!$attachment) {
            return response()->json(['error' => 'Attachment not found', 'error_ar' => 'مرفق غير موجود'], 404);
        }
        $currentUser = $this->securityHelper->getCurrentUser();
        $data["createdBy"] = $currentUser->id;
        /*   $attachment = $this->attachmentService->getById($attachmentId);

        if(!$attachment){
        return response()->json(['error' => 'Attachment not found','error_ar'=>'هذا المرفق غير موجود'], 404);
        }
        $attachmentAgenda = $attachment->meetingAgenda;
        if($attachmentAgenda){

        $meetingId = $attachmentAgenda->meeting_id;
        $meeting = $this->meetingService->getById($meetingId);

        $meetingParticipants = $meeting->meetingParticipants;
        $meetingParticipantIds = array_column($meetingParticipants->toArray(),'id');
        $meetingOrganisers = $meeting->meetingOrganisers;
        $meetingOrganiserIds = array_column($meetingOrganisers->toArray(),'id');
        $meetingMemberIds = array_merge($meetingParticipantIds,$meetingOrganiserIds);
        $data['meetingMemberIds'] = $meetingMemberIds; */
        $this->eventHelper->fireEvent($data, 'App\Events\SlideNotesEvent');
        /*  if(isset($data['presentation_notes'])){
        $presentationNotes = json_encode($data['presentation_notes']);
        $attachment->presentation_notes = $presentationNotes;
        $redis = Redis::connection();
        $redis->set('presentation_notes_'.$attachmentId, $presentationNotes);
        } */
        return response()->json(['message' => 'Fire slide notes successfully'], 200);
        // }

        return response()->json(['error' => 'Agenda not found', 'error_ar' => 'جدول الأعمال غير موجود'], 404);

    }

    public function endPresentation(Request $request, int $meetingId, int $attachmentId)
    {
        $data = $request->all();
        $attachment = $this->attachmentService->getById($attachmentId)->load('presenter');
        if (!$attachment) {
            return response()->json(['error' => 'Attachment not found', 'error_ar' => 'مرفق غير موجود'], 404);
        }

        $meeting = $this->meetingService->getById($meetingId);
        if (!$meeting) {
            return response()->json(['error' => 'Meeting not found', 'error_ar' => 'الاجتماع غير موجود'], 404);
        }

        $currentUser = $this->securityHelper->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['error' => 'User not found!'], 404);
        }

        if ($meeting->meeting_status_id != config('meetingStatus.start')) {
            return response()->json(['error' => 'Can\'t end attachment, Meeting still not started', 'error_ar' => 'لا يمكن تقديم المرفق ، الاجتماع لم يبدأ بعد'], 400);
        }
        $presenters = $attachment->meetingAgenda->presenters->toArray();
        $meetingAgendaUserPresenterIds = array_column($presenters, 'user_id');
        $meetingAgendaGuestPresenterIds = array_column($presenters, 'meeting_guest_id');
        $meetingAgendaOrganisersIds = array_column($meeting->meetingOrganisers->toArray(), 'id');
        if ($currentUser->id == -1) {
            if (!\in_array($currentUser->meeting_guest_id, $meetingAgendaGuestPresenterIds) && $currentUser->meeting_guest_id != $attachment->presenter_meeting_guest_id) {
                return response()->json(['error' => 'Can\'t end attachment, You are not meeting agenda presenter', 'error_ar' => 'لا يمكن إنهاء تقديم المرفقات ، فأنت لست مقدم اجتماع جدول أعمال'], 400);
            }
        } else {
            if (!\in_array($currentUser->id, $meetingAgendaUserPresenterIds) && (!\in_array($currentUser->id, $meetingAgendaOrganisersIds)) && $currentUser->id != $meeting->created_by && $currentUser->id != $attachment->presenter_id) {
                return response()->json(['error' => 'Can\'t end attachment, You are not meeting agenda presenter', 'error_ar' => 'لا يمكن إنهاء تقديم المرفقات ، فأنت لست مقدم اجتماع جدول أعمال'], 400);
            }
        }

        $firingData = $this->attachmentService->endPresentation($meeting, $currentUser, $attachment);
        $this->meetingAgendaService->updateAgendaTimerWhenEndPresentation($attachment->meeting_agenda_id);
        return response()->json(['message' => 'Presentation end successfully'], 200);

    }
    public function endPresentationWithoutNotification(Request $request, int $meetingId, int $attachmentId)
    {
        $data = $request->all();
        $attachment = $this->attachmentService->getById($attachmentId)->load('presenter');
        if (!$attachment) {
            return response()->json(['error' => 'Attachment not found', 'error_ar' => 'مرفق غير موجود'], 404);
        }

        $meeting = $this->meetingService->getById($meetingId);
        if (!$meeting) {
            return response()->json(['error' => 'Meeting not found', 'error_ar' => 'الاجتماع غير موجود'], 404);
        }

        $currentUser = $this->securityHelper->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['error' => 'User not found!'], 404);
        }

        if ($meeting->meeting_status_id != config('meetingStatus.start')) {
            return response()->json(['error' => 'Can\'t end attachment, Meeting still not started', 'error_ar' => 'لا يمكن تقديم المرفق ، الاجتماع لم يبدأ بعد'], 400);
        }
        $meetingAgendaPresenterIds = array_column($attachment->meetingAgenda->presentersAgenda->toArray(), 'id');
        $meetingAgendaOrganisersIds = array_column($meeting->meetingOrganisers->toArray(), 'id');
        if (!\in_array($currentUser->id, $meetingAgendaPresenterIds) && (!\in_array($currentUser->id, $meetingAgendaOrganisersIds)) && $currentUser->id != $meeting->created_by) {
            return response()->json(['error' => 'Can\'t end attachment, You are not meeting agenda presenter', 'error_ar' => 'لا يمكن إنهاء تقديم المرفقات ، فأنت لست مقدم اجتماع جدول أعمال'], 400);
        }

        $firingData = $this->attachmentService->endPresentation($meeting, $currentUser, $attachment, true);
        $this->meetingAgendaService->updateAgendaTimerWhenEndPresentation($attachment->meeting_agenda_id);
        return response()->json(['message' => 'Presentation end successfully'], 200);
    }

    public function changePresenter(Request $request, int $meetingId, int $attachmentId)
    {
        $data = $request->all();
        $presenterUserId = $data['presenterUserId'];
        $isGuest = $data['isGuest'];
        $attachment = $this->attachmentService->getById($attachmentId)->load('presenter');
        if (!$attachment) {
            return response()->json(['error' => 'Attachment not found', 'error_ar' => 'مرفق غير موجود'], 404);
        }
        $meeting = $this->meetingService->getById($meetingId);
        if (!$meeting) {
            return response()->json(['error' => 'Meeting not found', 'error_ar' => 'الاجتماع غير موجود'], 404);
        }

        $currentUser = $this->securityHelper->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['error' => 'User not found!'], 404);
        }

        $firingData = $this->attachmentService->changePresenter($meeting, $currentUser, $attachment, $presenterUserId, $isGuest);

        return response()->json(['message' => 'Change presenter successfully'], 200);

    }

    public function checkPresentationMaster(Request $request, int $meetingId, int $attachmentId)
    {
        $data = $request->all();
        $attachment = $this->attachmentService->getById($attachmentId);

        if (!$attachment) {
            return response()->json(['error' => 'Attachment not found', 'error_ar' => 'مرفق غير موجود'], 404);
        }
        $meeting = $this->meetingService->getById($meetingId);
        if (!$meeting) {
            return response()->json(['error' => 'Meeting not found', 'error_ar' => 'الاجتماع غير موجود'], 404);
        }
        $currentUser = $this->securityHelper->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['error' => 'User not found!'], 404);
        }
        $data = $this->attachmentService->checkPresentationMaster($attachment, $meeting, $currentUser);
        if ($data == true) {
            return response()->json(['is_master' => true], 200);
        } else if ($data == false) {
            return response()->json(['is_master' => false], 200);
        } else {
            return response()->json(['error' => 'User can\'t access this presentation', 'error_ar' => 'لا يمكن للمستخدم الوصول إلى هذا العرض التقديمي'], 400);
        }

    }

    public function getMeetingPresentationAttachment(int $meetingId)
    {
        $meeting = $this->meetingService->getById($meetingId);
        if (!$meeting) {
            return response()->json(['error' => 'Meeting not found', 'error_ar' => 'الاجتماع غير موجود'], 404);
        }
        $currentUser = $this->securityHelper->getCurrentUser();
        if (!$currentUser) {
            return response()->json(['error' => 'User not found!'], 404);
        }
        $attachment = $this->attachmentService->getMeetingPresentationAttachment($meeting);
        return response()->json($attachment, 200);
    }
}
