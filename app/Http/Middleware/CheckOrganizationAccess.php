<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Middleware;

use Helpers\SecurityHelper;
use JWTAuth;
use Services\MeetingService;
use Services\AttachmentService;
use Services\TaskManagementService;
use Services\CommitteeService;
use Services\DocumentService;
use Services\MeetingTypeService;
use Services\HtmlMomTemplateService;
use Services\NicknameService;
use Services\VoteService;
use Services\UserTitleService;
use Services\JobTitleService;
use Services\MomTemplateService;
use Services\AgendaTemplateService;
use Services\UserOnlineConfigurationService;
use Services\ProposalService;
use Services\ChatGroupService;
use Services\UserService;
use Services\DecisionTypeService;
use Services\RoleService;

/**
 * Description of CheckOrganizationAccess
 *
 * @author Eman
 */
class CheckOrganizationAccess
{
    private $scurityHelper, $meetingService, $attachmentService,
            $taskManagementService, $committeeService, $documentService,
            $meetingTypeService, $htmlMomTemplateService, $nicknameService, $voteService,
            $userTitleService, $jobTitleService, $momTemplateService, $agendaTemplateService,
            $userOnlineConfigurationService, $proposalService, $chatGroupService,
            $userService, $decisionTypeService, $roleService;

    public function __construct(SecurityHelper $scurityHelper, MeetingService $meetingService,
        AttachmentService $attachmentService, TaskManagementService $taskManagementService,
        CommitteeService $committeeService, DocumentService $documentService,
        MeetingTypeService $meetingTypeService, HtmlMomTemplateService $htmlMomTemplateService,
        NicknameService $nicknameService, VoteService $voteService,
        UserTitleService $userTitleService, JobTitleService $jobTitleService, 
        MomTemplateService $momTemplateService, AgendaTemplateService $agendaTemplateService,
        UserOnlineConfigurationService $userOnlineConfigurationService, ProposalService $proposalService,
        ChatGroupService $chatGroupService, UserService $userService, DecisionTypeService $decisionTypeService,
        RoleService $roleService)
    {
        $this->scurityHelper = $scurityHelper;
        $this->meetingService = $meetingService;
        $this->attachmentService = $attachmentService;
        $this->taskManagementService = $taskManagementService;
        $this->committeeService = $committeeService;
        $this->documentService = $documentService;
        $this->meetingTypeService = $meetingTypeService;
        $this->htmlMomTemplateService = $htmlMomTemplateService;
        $this->nicknameService = $nicknameService;
        $this->voteService = $voteService;
        $this->userTitleService = $userTitleService;
        $this->jobTitleService = $jobTitleService;
        $this->momTemplateService = $momTemplateService;
        $this->agendaTemplateService = $agendaTemplateService;
        $this->userOnlineConfigurationService = $userOnlineConfigurationService;
        $this->proposalService = $proposalService;
        $this->chatGroupService = $chatGroupService;
        $this->userService = $userService;
        $this->decisionTypeService = $decisionTypeService;
        $this->roleService = $roleService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $account= $this->scurityHelper->getCurrentUser();
        if ($account) {
            try {
                if(isset($request->route()->parameters()['meeting_id'])){
                    $meetingId = $request->route()->parameters()['meeting_id'];
                    $meeting = $this->meetingService->getById($meetingId);
                    // allow meeting guests and normal users
                    if((!$meeting) ||
                      (($meeting->organization_id != $account->organization_id) &&
                      (isset($account->meeting->organization->id) && 
                        $meeting->organization_id != $account->meeting->organization->id)))
                    {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['attachment_id'])){
                    $attachmentId = $request->route()->parameters()['attachment_id'];
                    $attachment = $this->attachmentService->getById($attachmentId);
                    if(!$attachment ||
                        ($attachment->meeting && ($attachment->meeting->organization_id != $account->organization_id && $attachment->meeting->organization_id != $account->meeting->organization->id)) ||
                        ($attachment->meetingAgenda && ($attachment->meetingAgenda->meeting->organization_id != $account->organization_id && $attachment->meetingAgenda->meeting->organization_id != $account->meeting->organization->id))
                    ) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['task_id'])){
                    $taskId = $request->route()->parameters()['task_id'];
                    $task = $this->taskManagementService->getById($taskId);
                    if(!$task ||
                       ($task->createdBy && $task->createdBy->organization_id != $account->organization_id)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['committee_id'])){
                    $committeeId = $request->route()->parameters()['committee_id'];
                    $committee = $this->committeeService->getById($committeeId);
                    if(!$committee ||
                       ($committee->organization_id != $account->organization_id)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['document_id'])){
                    $documentId = $request->route()->parameters()['document_id'];
                    $document = $this->documentService->getById($documentId);
                    if(!$document ||
                       ($document->creator->organization_id != $account->organization_id)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['meeting_type_id'])){
                    $meetingTypeId = $request->route()->parameters()['meeting_type_id'];
                    $meetingType = $this->meetingTypeService->getById($meetingTypeId);
                    if(!$meetingType ||
                       ($meetingType->organization_id != $account->organization_id)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['html_mom_template_id'])){
                    $htmlMomTemplateId = $request->route()->parameters()['html_mom_template_id'];
                    $htmlMomTemplate = $this->htmlMomTemplateService->getById($htmlMomTemplateId);
                    if(!$htmlMomTemplate ||
                       ($htmlMomTemplate->organization_id != $account->organization_id)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['nickname_id'])){
                    $nicknameId = $request->route()->parameters()['nickname_id'];
                    $nickname = $this->nicknameService->getById($nicknameId);
                    if(!$nickname ||
                       ($nickname->organization_id != $account->organization_id)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['circular_decision_id'])){
                    $voteId = $request->route()->parameters()['circular_decision_id'];
                    $vote = $this->voteService->getById($voteId);
                    if(!$vote ||
                       ($vote->creator && $vote->creator->organization_id != $account->organization_id)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['user_title_id'])){
                    $userTitleId = $request->route()->parameters()['user_title_id'];
                    $userTitle = $this->userTitleService->getById($userTitleId);
                    if(!$userTitle ||
                       ($userTitle->organization_id != $account->organization_id)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['job_title_id'])){
                    $jobTitleId = $request->route()->parameters()['job_title_id'];
                    $jobTitle = $this->jobTitleService->getById($jobTitleId);
                    if(!$jobTitle ||
                       ($jobTitle->organization_id != $account->organization_id)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['mom_template_id'])){
                    $momTemplateId = $request->route()->parameters()['mom_template_id'];
                    $momTemplate = $this->momTemplateService->getById($momTemplateId);
                    if(!$momTemplate ||
                       ($momTemplate->organization_id != $account->organization_id)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['agenda_template_id'])){
                    $agendaTemplateId = $request->route()->parameters()['agenda_template_id'];
                    $agendaTemplate = $this->agendaTemplateService->getById($agendaTemplateId);
                    if(!$agendaTemplate ||
                       ($agendaTemplate->organization_id != $account->organization_id)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['user_online_configuration_id'])){
                    $userOnlineConfigurationId = $request->route()->parameters()['user_online_configuration_id'];
                    $userOnlineConfiguration = $this->userOnlineConfigurationService->getById($userOnlineConfigurationId);
                    if(!$userOnlineConfiguration ||
                       ($userOnlineConfiguration->user && $userOnlineConfiguration->user->organization_id != $account->organization_id)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['proposal_id'])){
                    $proposalId = $request->route()->parameters()['proposal_id'];
                    $proposal = $this->proposalService->getById($proposalId);
                    if(!$proposal ||
                        ($proposal->organization_id != $account->organization_id)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['chat_group_id'])){
                    $chatGroupId = $request->route()->parameters()['chat_group_id'];
                    $chatGroup = $this->chatGroupService->getById($chatGroupId);
                    if(!$chatGroup ||
                        (($chatGroup->organization_id != $account->organization_id) && $account->id != -1) ||
                        ($chatGroup->meeting_id != $account->meeting_id  && $account->id == -1)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['user_id'])){
                    $userId = $request->route()->parameters()['user_id'];
                    $user = $this->userService->getById($userId);
                    if(!$user || ($account->organization_id && $user->organization_id != $account->organization_id)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['decision_type_id'])){
                    $decisionTypeId = $request->route()->parameters()['decision_type_id'];
                    $decisionType = $this->decisionTypeService->getById($decisionTypeId);
                    if(!$decisionType ||
                        ($decisionType->organization_id != $account->organization_id)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                if(isset($request->route()->parameters()['role_id'])){
                    $roleId = $request->route()->parameters()['role_id'];
                    $role = $this->roleService->getById($roleId);
                    if(!$role ||
                        ($role->organization_id && $role->organization_id != $account->organization_id)) {
                        return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                    }
                }
                return $next($request);
            } catch (\Exception $e) {
                return $next($request);
            }
        } else {
            return response()->json(['message' => ["Not Allowed"]], 401);
        }
    }
}
