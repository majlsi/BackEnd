<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\CommitteeService;
use Services\VoteService;
use Services\TaskManagementService;
use Services\DocumentService;
use Services\MeetingService;
use Services\UserService;
use Services\RoleService;

use Helpers\SecurityHelper;
use Validator;

class CommitteeDashboardController extends Controller {

    private $committeeService,$voteService, $taskManagementService, $documentService,
        $meetingService, $userService,$roleService;
    private $securityHelper;

    public function __construct(CommitteeService $committeeService,SecurityHelper $securityHelper,
        VoteService $voteService, TaskManagementService $taskManagementService, DocumentService $documentService,
        MeetingService $meetingService, UserService $userService,RoleService $roleService) {
        $this->committeeService = $committeeService;
        $this->securityHelper = $securityHelper;
        $this->voteService = $voteService;
        $this->taskManagementService = $taskManagementService;
        $this->documentService = $documentService;
        $this->meetingService = $meetingService;
        $this->userService = $userService;
        $this->roleService = $roleService;
    }

    public function getMemberCommitteesDashboardStatistics(){
        $result = [];
        $user = $this->securityHelper->getCurrentUser();

        $result['committees_count'] = $this->committeeService->getCountOfCommitteesForCurrentUser($user->id,$user->organization_id);
        $result['committees'] = $this->committeeService->getLimitOfCommitteesThatCurrentUserOnIt($user->id,$user->organization_id);
        
        $result['meeting_decisions_statistics']['approved'] = $this->voteService->getMeetingDecisionsResultStatusStatisticsForUser($user->id,config('voteResultStatuses.approved'));
        $result['meeting_decisions_statistics']['rejected'] = $this->voteService->getMeetingDecisionsResultStatusStatisticsForUser($user->id,config('voteResultStatuses.rejected'));
        // $result['meeting_decisions_statistics']['balanced'] = $this->voteService->getMeetingDecisionsResultStatusStatisticsForUser($user->id,config('voteResultStatuses.balanced'));
        $result['meeting_decisions_statistics']['no_votes_yet'] = $this->voteService->getMeetingDecisionsResultStatusStatisticsForUser($user->id,config('voteResultStatuses.noVotesYet'));
        $result['meeting_decisions_statistics']['in_progress'] = $this->voteService->getMeetingDecisionsResultStatusStatisticsForUser($user->id,config('voteResultStatuses.inprogress'));
        $result['meeting_decisions'] = $this->voteService->getLimitOfMeetingDecisionsForUser($user->id);

        $result['circular_decisions_statistics']['approved'] = $this->voteService->getCircularDecisionsResultStatusStatisticsForUser($user->id,config('voteResultStatuses.approved'));
        $result['circular_decisions_statistics']['rejected'] = $this->voteService->getCircularDecisionsResultStatusStatisticsForUser($user->id,config('voteResultStatuses.rejected'));
        // $result['circular_decisions_statistics']['balanced'] = $this->voteService->getCircularDecisionsResultStatusStatisticsForUser($user->id,config('voteResultStatuses.balanced'));
        $result['circular_decisions_statistics']['no_votes_yet'] = $this->voteService->getCircularDecisionsResultStatusStatisticsForUser($user->id,config('voteResultStatuses.noVotesYet'));
        $result['circular_decisions_statistics']['in_progress'] = $this->voteService->getCircularDecisionsResultStatusStatisticsForUser($user->id,config('voteResultStatuses.inprogress'));
        $result['circular_decisions'] = $this->voteService->getLimitOfCircularDecisionsForUser($user->id);

        $result['tasks_statistics'] = $this->taskManagementService->getUserTaskCount($user->id);
        $result['tasks_statistics']['percentage_of_done_tasks'] = ($result['tasks_statistics']['done_tasks'] / ($result['tasks_statistics']['all_tasks'] == 0 ? 1 : $result['tasks_statistics']['all_tasks'])   ) * 100;
        $result['tasks_statistics']['delayed_tasks'] = $this->taskManagementService->getDelayedTasksStatusStatisticsForUser($user->id);
        $result['tasks'] = $this->taskManagementService->getLimitOfTasksForUser($user->id);

        $result['documents_statistics']['new'] = $this->documentService->getDocumentsResultStatusStatisticsForUser($user->id,config('documentStatuses.new'));
        $result['documents_statistics']['in_progress'] = $this->documentService->getDocumentsResultStatusStatisticsForUser($user->id,config('documentStatuses.inProgress'));
        $result['documents_statistics']['completed'] = $this->documentService->getDocumentsResultStatusStatisticsForUser($user->id,config('documentStatuses.complete'));
        $result['documents_statistics']['delayed'] = $this->documentService->getDocumentsResultStatusStatisticsForUser($user->id,config('documentStatuses.delay'));

        $result['documents'] = $this->documentService->getLimitOfDocumentsForUser($user->id);

        $result['meetings_statistics'] = $this->meetingService->getMeetingsStatisticsForUser($user->id,$user->organization_id);
        $result['meetings'] = $this->meetingService->getLimitOfMeetingsForUser($user->id);

        return response()->json($result,200);
    }


    public function getBoardDashboardStatistics(){
        $result = [];
        $user = $this->securityHelper->getCurrentUser();

        $result['committees_count'] = $this->committeeService->getCountOfCommitteesForOrganization($user->organization_id);
        $result['committees_members_count'] = $this->committeeService->getCountOfCommitteesMembersForOrganization($user->organization_id);
        $result['members_count'] = $this->roleService->getCountOfMemebersForOrganization($user->organization_id);
        $result['members'] = $this->userService->getLimitOfOrganizationMembers($user->organization_id);


        $result['committees'] = $this->committeeService->getLimitOfOrganizationCommittees($user->organization_id);
        
        $result['meeting_decisions_statistics']['approved'] = $this->voteService->getMeetingDecisionsResultStatusStatisticsForOrganization($user->organization_id,config('voteResultStatuses.approved'));
        $result['meeting_decisions_statistics']['rejected'] = $this->voteService->getMeetingDecisionsResultStatusStatisticsForOrganization($user->organization_id,config('voteResultStatuses.rejected'));
        // $result['meeting_decisions_statistics']['balanced'] = $this->voteService->getMeetingDecisionsResultStatusStatisticsForOrganization($user->organization_id,config('voteResultStatuses.balanced'));
        $result['meeting_decisions_statistics']['no_votes_yet'] = $this->voteService->getMeetingDecisionsResultStatusStatisticsForOrganization($user->organization_id,config('voteResultStatuses.noVotesYet'));
        $result['meeting_decisions_statistics']['in_progress'] = $this->voteService->getMeetingDecisionsResultStatusStatisticsForOrganization($user->organization_id,config('voteResultStatuses.inprogress'));
        $result['meeting_decisions'] = $this->voteService->getLimitOfMeetingDecisionsForOrganization($user->organization_id);

        $result['circular_decisions_statistics']['approved'] = $this->voteService->getCircularDecisionsResultStatusStatisticsForOrganization($user->organization_id,config('voteResultStatuses.approved'));
        $result['circular_decisions_statistics']['rejected'] = $this->voteService->getCircularDecisionsResultStatusStatisticsForOrganization($user->organization_id,config('voteResultStatuses.rejected'));
        // $result['circular_decisions_statistics']['balanced'] = $this->voteService->getCircularDecisionsResultStatusStatisticsForOrganization($user->organization_id,config('voteResultStatuses.balanced'));
        $result['circular_decisions_statistics']['no_votes_yet'] = $this->voteService->getCircularDecisionsResultStatusStatisticsForOrganization($user->organization_id,config('voteResultStatuses.noVotesYet'));
        $result['circular_decisions_statistics']['in_progress'] = $this->voteService->getCircularDecisionsResultStatusStatisticsForOrganization($user->organization_id,config('voteResultStatuses.inprogress'));
        $result['circular_decisions'] = $this->voteService->getLimitOfCircularDecisionsForOrganization($user->organization_id);

        $result['tasks_statistics'] = $this->taskManagementService->getOrganizationTaskDashBoard($user->organization_id);
        $result['tasks_statistics']['percentage_of_done_tasks'] = ($result['tasks_statistics']['done_tasks'] / ($result['tasks_statistics']['all_tasks'] == 0 ? 1 : $result['tasks_statistics']['all_tasks'])    ) * 100;
        $result['tasks'] = $this->taskManagementService->getLimitOfTasksForOrganization($user->organization_id);

        $result['documents_statistics']['new'] = $this->documentService->getDocumentsResultStatusStatisticsForOrganization($user->organization_id,config('documentStatuses.new'));
        $result['documents_statistics']['in_progress'] = $this->documentService->getDocumentsResultStatusStatisticsForOrganization($user->organization_id,config('documentStatuses.inProgress'));
        $result['documents_statistics']['completed'] = $this->documentService->getDocumentsResultStatusStatisticsForOrganization($user->organization_id,config('documentStatuses.complete'));
        $result['documents_statistics']['delayed'] = $this->documentService->getDocumentsResultStatusStatisticsForOrganization($user->organization_id,config('documentStatuses.delay'));

        $result['documents'] = $this->documentService->getLimitOfDocumentsForOrganization($user->organization_id);

        $result['meetings_statistics'] = $this->meetingService->getOrganizationMeetingStatistics($user->organization_id);
        $result['meetings'] = $this->meetingService->getLimitOfMeetingsForOrganization($user->organization_id);

        return response()->json($result,200);
    }

    public function getCommitteeDashboardStatistics($committee_id){
        $result = [];
        $user = $this->securityHelper->getCurrentUser();

        $result['members_count'] = $this->roleService->getCountOfMemebersForCommittee($committee_id);

        $result['members'] = $this->userService->getLimitOfCommitteeMembers($committee_id);
        
        $result['meeting_decisions_statistics']['approved'] = $this->voteService->getMeetingDecisionsResultStatusStatisticsForCommittee($committee_id,config('voteResultStatuses.approved'));
        $result['meeting_decisions_statistics']['rejected'] = $this->voteService->getMeetingDecisionsResultStatusStatisticsForCommittee($committee_id,config('voteResultStatuses.rejected'));
        // $result['meeting_decisions_statistics']['balanced'] = $this->voteService->getMeetingDecisionsResultStatusStatisticsForCommittee($committee_id,config('voteResultStatuses.balanced'));
        $result['meeting_decisions_statistics']['no_votes_yet'] = $this->voteService->getMeetingDecisionsResultStatusStatisticsForCommittee($committee_id,config('voteResultStatuses.noVotesYet'));
        $result['meeting_decisions_statistics']['in_progress'] = $this->voteService->getMeetingDecisionsResultStatusStatisticsForCommittee($committee_id,config('voteResultStatuses.inprogress'));
        $result['meeting_decisions'] = $this->voteService->getLimitOfMeetingDecisionsForCommitee($committee_id);

        $result['circular_decisions_statistics']['approved'] = $this->voteService->getCircularDecisionsResultStatusStatisticsForCommittee($committee_id,config('voteResultStatuses.approved'));
        $result['circular_decisions_statistics']['rejected'] = $this->voteService->getCircularDecisionsResultStatusStatisticsForCommittee($committee_id,config('voteResultStatuses.rejected'));
        // $result['circular_decisions_statistics']['balanced'] = $this->voteService->getCircularDecisionsResultStatusStatisticsForCommittee($committee_id,config('voteResultStatuses.balanced'));
        $result['circular_decisions_statistics']['no_votes_yet'] = $this->voteService->getCircularDecisionsResultStatusStatisticsForCommittee($committee_id,config('voteResultStatuses.noVotesYet'));
        $result['circular_decisions_statistics']['in_progress'] = $this->voteService->getCircularDecisionsResultStatusStatisticsForCommittee($committee_id,config('voteResultStatuses.inprogress'));
        $result['circular_decisions'] = $this->voteService->getLimitOfCircularDecisionsForCommittee($committee_id);

        $result['tasks_statistics'] = $this->taskManagementService->getCommitteeTaskDashBoard($committee_id);
        $result['tasks_statistics']['percentage_of_done_tasks'] = ($result['tasks_statistics']['done_tasks'] / ($result['tasks_statistics']['all_tasks'] == 0 ? 1 : $result['tasks_statistics']['all_tasks'])    ) * 100;
        $result['tasks'] = $this->taskManagementService->getLimitOfTasksForCommittee($committee_id);

        $result['documents_statistics']['new'] = $this->documentService->getDocumentsResultStatusStatisticsForCommittee($committee_id,config('documentStatuses.new'));
        $result['documents_statistics']['in_progress'] = $this->documentService->getDocumentsResultStatusStatisticsForCommittee($committee_id,config('documentStatuses.inProgress'));
        $result['documents_statistics']['completed'] = $this->documentService->getDocumentsResultStatusStatisticsForCommittee($committee_id,config('documentStatuses.complete'));
        $result['documents_statistics']['delayed'] = $this->documentService->getDocumentsResultStatusStatisticsForCommittee($committee_id,config('documentStatuses.delay'));

        $result['documents'] = $this->documentService->getLimitOfDocumentsForCommittee($committee_id);

        $result['meetings_statistics'] = $this->meetingService->getCommitteeMeetingStatistics($committee_id);
        $result['meetings'] = $this->meetingService->getLimitOfMeetingsForCommittee($committee_id);

        return response()->json($result,200);
    }

    public function getUserManagedCommittees(){
        $result = [];
        $user = $this->securityHelper->getCurrentUser();


        $result['committees'] = $this->committeeService->getUserManagedCommittees($user->id)->toArray();

        return response()->json($result,200);
    }
}