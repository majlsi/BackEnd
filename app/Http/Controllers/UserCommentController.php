<?php

namespace App\Http\Controllers;

use Helpers\SecurityHelper;
use Illuminate\Http\Request;
use Services\MeetingService;
use Services\MomService;
use Services\UserCommentService;

class UserCommentController extends Controller
{

    private $userCommentService;
    private $securityHelper;
    private $momService;
    private $meetingService;

    public function __construct(UserCommentService $userCommentService,
        SecurityHelper $securityHelper, MomService $momService,
        MeetingService $meetingService) {
        $this->userCommentService = $userCommentService;
        $this->securityHelper = $securityHelper;
        $this->momService = $momService;
        $this->meetingService = $meetingService;

    }

    public function destroy($meetingId, $id)
    {
        $deleted = $this->userCommentService->delete($id);
        if ($deleted != 0) {
            return response()->json(['message' => 'user comment deleted successfully'], 200);
        }
    }

    public function store(Request $request, int $meetingId)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();

        $meetingAgendaId = $data['meeting_agenda_id'];

        $meeting = $this->meetingService->getById($meetingId);
        $meetingOrganisers = $meeting->meetingOrganisers;
        $meetingOrganiserIds = array_column($meetingOrganisers->toArray(), 'id');

        $data['is_organizer']= in_array($user->id, $meetingOrganiserIds)? 1 : 0;
        $userComment = $this->userCommentService->addOrUpdateUserComment($meetingAgendaId, $user->id, $data['comment_text'],$data['is_organizer']);
        if ($userComment) {
            return response()->json(['message' => 'Comment added Successfully'], 200);
        }
        return response()->json(['error' => 'Can\'t add comment'], 400);
      
    }

}
