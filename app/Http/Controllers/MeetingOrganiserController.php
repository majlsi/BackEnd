<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\MeetingOrganiserService;
use Services\MeetingService;
use Services\ChatService;
use Services\ChatGroupService;
use Models\MeetingOrganiser;
use Helpers\SecurityHelper;
use Helpers\EventHelper;
use Validator;

class MeetingOrganiserController extends Controller
{

    private $meetingOrganiserService;
    private $meetingService;
    private $securityHelper;
    private $eventHelper;
    private $chatService;
    private $chatGroupService;

    public function __construct(
        MeetingOrganiserService $meetingOrganiserService,
        SecurityHelper $securityHelper,
        MeetingService $meetingService
        , EventHelper $eventHelper,
        ChatService $chatService,
        ChatGroupService $chatGroupService
    ) {
        $this->meetingOrganiserService = $meetingOrganiserService;
        $this->securityHelper = $securityHelper;
        $this->meetingService = $meetingService;
        $this->eventHelper = $eventHelper;
        $this->chatService = $chatService;
        $this->chatGroupService = $chatGroupService;
    }

    public function getMeetingOrganisersForMeeting(int $meetingId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        if ($user && $user->organization_id === $meeting->organization_id) {
            return response()->json($this->meetingOrganiserService->getMeetingOrganisersForMeeting($meetingId), 200);
        } else {
            return response()->json(['error' => 'You don\'t have access'], 400);
        }
    }

    public function storeMeetingOrganisersForMeeting(Request $request, int $meetingId)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        if ($user  && $user->organization_id === $meeting->organization_id) {
            $organisersIds = [];
            foreach ($data as $key => $organizer) {
                $organisersIds[$key]['user_id'] = $organizer['id'];
            }
            $created = $this->meetingService->createOrganisersForMeetingVersion($meetingId, $organisersIds);
            // $this->eventHelper->fireEvent([], 'App\Events\MeetingDataChangedEvent');
            // update chat room users for meeting
            $meeting = $this->meetingService->getById($meetingId);
            if ($meeting->chat_room_id && $user->chat_user_id && $meeting->meeting_status_id == config('meetingStatus.draft')) {
                //update chat group users
                $this->chatGroupService->updateMeetingChatGroupMeemerUsers($meeting);
                $this->chatService->updateMeetingRoom($user,$meeting);
            }
            $versionOfMeeting = $this->meetingService->getUnpublishedVersionOfMeeting($meetingId);
            return response()->json(['meeting_organisers' => $created,'meeting_version_id' => $versionOfMeeting? $versionOfMeeting->id : null], 200);
        } else {
            return response()->json(['error' => 'You don\'t have access'], 400);
        }
    }

    public function checkIfOrganiser(Request $request, int $meetingId)
    {

        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        $meetingOrganisers = $meeting->meetingOrganisers;
        $meetingOrganiserIds = array_column($meetingOrganisers->toArray(), 'id');
        $meetingOrganiserIds[] = $meeting->created_by;
        if (\in_array($user->id, $meetingOrganiserIds)) {
            return response()->json(true, 200);
        } else {
            return response()->json(false, 200);
        }
    }
}
