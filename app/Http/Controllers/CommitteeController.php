<?php

namespace App\Http\Controllers;

use App\Exports\CommitteesExport;
use Helpers\EmailHelper;
use Helpers\NotificationHelper;
use Helpers\RequestHelper;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Services\CommitteeService;
use Services\ChatService;
use Services\ChatGroupService;
use Services\NotificationService;
use Services\UserService;
use Helpers\SecurityHelper;
use Helpers\CommitteeHelper;
use Models\Committee;
use Models\Request as RequestModel;
use Services\RequestService;
use Illuminate\Support\Facades\Validator;
use Storages\StorageFactory;
use Services\RoleRightService;

class CommitteeController extends Controller
{

    private $committeeService;
    private $securityHelper;
    private $committeeHelper;
    private $requestHelper;
    private $chatService;
    private $chatGroupService;
    private $userService;
    private $requestService;
    private $notificationHelper;
    private $notificationService;
    private $storage;
    private $roleRightService;

    private $emailHelper;
    public function __construct(
        CommitteeService $committeeService,
        SecurityHelper $securityHelper,
        CommitteeHelper $committeeHelper,
        RequestHelper $requestHelper,
        ChatService $chatService,
        ChatGroupService $chatGroupService,
        UserService $userService,
        NotificationService $notificationService,
        RequestService $requestService,
        NotificationHelper $notificationHelper,
        RoleRightService $roleRightService,
        EmailHelper $emailHelper,
    ) {
        $this->committeeService = $committeeService;
        $this->securityHelper = $securityHelper;
        $this->committeeHelper = $committeeHelper;
        $this->requestHelper = $requestHelper;
        $this->chatService = $chatService;
        $this->chatGroupService = $chatGroupService;
        $this->userService = $userService;
        $this->requestService = $requestService;
        $this->notificationHelper = $notificationHelper;
        $this->notificationService = $notificationService;
        $this->roleRightService = $roleRightService;
        $this->emailHelper = $emailHelper;
        $this->storage = StorageFactory::createStorage();
    }

    public function show($id)
    {
        $user = $this->securityHelper->getCurrentUser();
        $committee = $this->committeeService->getById($id);
        if (!($user->organization_id == $committee->organization_id)) {
            return response()->json(['error' => 'Can\'t display this committee.'], 400);
        }
        $committee = $this->committeeService->getCommitteeDetails($id, $user);
        $canExport = $this->roleRightService->canAccess($user->role_id, config('rights.committeeExport')) != null;
        return response()->json(['Results' => $committee, 'CanExport' => $canExport], 200);
    }

    public function store(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $data = $request->get('committee');
        $data = json_decode($data, true);
        $data['organization_id'] = $user->organization_id;
        if (config('customSetting.addCommitteeNewFields')) {
            return $this->createCommitteeWithNewFields($request, $user, $data);
        }
        return $this->createCommittee($request, $data);
    }

    private function createCommittee($request, $data)
    {
        $governanceRegulationFile = $request->file('governanceRegulationFile');
        $committee = $this->committeeHelper->prepareCommitteData($data);
        // create committee directory
        // validate governance regulation file and upload it
        if ($governanceRegulationFile != null) {
            $errors = $this->validateGovernanceRegulationValidator($governanceRegulationFile, $request);
            if ($errors != null) {
                return response()->json(["error" => $errors], 400);
            }
            $directory = $this->committeeService->createNewCommitteeDirectory($committee);
            $committee["directory_id"] = $directory->id;
            $committee['governance_regulation_url'] =
            $this->storage->uploadFileByName($governanceRegulationFile, $directory->directory_path);
        }
        $validator = Validator::make($committee, Committee::rules('save'), Committee::messages('save'));
        if ($validator->fails()) {
            return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
        }
        $createdCommittee = $this->committeeService->create($committee);
        // if(config('customSetting.addFileToCommitteeFeature'))
        // {
        //     $this->committeeService->createCommitteeDirectory($createdCommittee['id']);
        // }
        // create chat room for committee
        // if ($user->chat_user_id) {
        //     $this->chatService->createCommitteeRoom($user,$createdCommittee);
        // }
        return response()->json($createdCommittee, 200);
    }

    private function createCommitteeWithNewFields($request, $user, $data)
    {
        $governanceRegulationFile = $request->file('governanceRegulationFile');
        $decisionDocumentFile = $request->file('decisionDocumentFile');
        $committeeRequest = $this->requestHelper->prepareCommitteeRequestData($data, $user);
        // create committee directory
        if ($governanceRegulationFile != null || $decisionDocumentFile != null) {
            $directory = $this->committeeService->createNewCommitteeDirectory($committeeRequest['request_body']);
            $committeeRequest['request_body']["directory_id"] = $directory->id;
        }
        // check on governance regulation file validation and upload it
        if ($governanceRegulationFile != null) {
            $errors = $this->validateGovernanceRegulationValidator($governanceRegulationFile, $request);
            if ($errors != null) {
                return response()->json(["error" => $errors], 400);
            }
            $committeeRequest['request_body']['governance_regulation_url'] =
            $this->storage->uploadFileByName($governanceRegulationFile, $directory->directory_path);
        }
        // check on decision document file validation and upload it
        if ($decisionDocumentFile != null) {
            $errors = $this->validateDecisionDocumentValidator($decisionDocumentFile, $request);
            if ($errors != null) {
                return response()->json(["error" => $errors], 400);
            }
            $committeeRequest['request_body']['decision_document_url'] =
            $this->storage->uploadFileByName($decisionDocumentFile, $directory->directory_path);
        }
        $requestValidator = Validator::make($committeeRequest,
            RequestModel::rules('saveCommitteeRequest'),
            RequestModel::messages('saveCommitteeRequest')
        );
        if ($requestValidator->fails()) {
            return response()->json(["error" => array_values($requestValidator->errors()->toArray())], 400);
        }
        $createdCommitteeRequest = $this->requestService->addCommitteeRequest($committeeRequest);

        if (isset($createdCommitteeRequest)) {
            $notificationData = $this->notificationHelper->prepareNotificationDataForRequest($createdCommitteeRequest);
            $this->notificationService->sendNotification($notificationData);
            $organizer = $createdCommitteeRequest->orgnization->systemAdmin;
            $this->emailHelper->sendAddCommitteeRequest(
                $organizer->email,
                $organizer->name_ar,
                $organizer->name,
                NotificationHelper::getNotificationData('notification.AddCommitteeRequestNotificationAr', $data),
                NotificationHelper::getNotificationData('notification.AddCommitteeRequestNotificationEn', $data),
                config('notificationUrls.requests') . $createdCommitteeRequest->id,
                $organizer->language_id
            );
        }
        return response()->json($createdCommitteeRequest, 200);
    }

    private function validateGovernanceRegulationValidator($governanceRegulationFile, $request)
    {
        if (isset($governanceRegulationFile) && $governanceRegulationFile->getClientOriginalExtension() == 'docx') {
            $governanceRegulationValidator = Validator::make($request->all(), [
                'governanceRegulationFile' => 'max:' . config('attachment.file_size')
            ]);
        } else {
            $governanceRegulationValidator = Validator::make($request->all(), [
                'governanceRegulationFile' =>
                'required|mimes:jpeg,jpg,png,doc,docx,odt,xls,xlsx,ppt,pptx,pdf|max:'
                . config('attachment.file_size')
            ]);
        }
        if ($governanceRegulationValidator->fails()) {
            return $governanceRegulationValidator->errors()->all();
        }
        return null;
    }

    private function validateDecisionDocumentValidator($decisionDocumentFile, $request)
    {
        if (isset($decisionDocumentFile) && $decisionDocumentFile->getClientOriginalExtension() == 'docx') {
            $decisionDocumentValidator = Validator::make($request->all(), [
                'decisionDocumentFile' => 'max:' . config('attachment.file_size')
            ]);
        } else {
            $decisionDocumentValidator = Validator::make($request->all(), [
                'decisionDocumentFile' =>
                'required|mimes:jpeg,jpg,png,doc,docx,odt,xls,xlsx,ppt,pptx,pdf|max:'
                . config('attachment.file_size')
            ]);
        }
        if ($decisionDocumentValidator->fails()) {
            return $decisionDocumentValidator->errors()->all();
        }
        return null;
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $oldCommittee=$this->committeeService->getById($id);
        if (config('customSetting.updateCommitteeRequestFeature')) {
            return $this->updateCommitteeRequestHandler($data, $user);
        } else {
            return $this->updateCommitteeHandler($data, $id, $oldCommittee, $user);
        }
    }

    private function updateCommitteeRequestHandler($data, $user) {
        if (config('customSetting.addFileToCommitteeFeature')) {
            $ruleName = 'updateCommitteeWithNewFieldsRequest';
        } else {
            $ruleName = 'updateCommitteeRequest';
        }
        $committeeRequest = $this->requestHelper->prepareUpdateCommitteeRequestData($data, $user);
        $requestValidator = Validator::make(
            $committeeRequest,
            RequestModel::rules($ruleName),
            RequestModel::messages($ruleName)
        );
        if ($requestValidator->fails()) {
            return response()->json(["error" => array_values($requestValidator->errors()->toArray())], 400);
        }
        $updatedCommitteeRequest = $this->requestService->addCommitteeRequest($committeeRequest);

        if (isset($updatedCommitteeRequest)) {
            $notificationData = $this->notificationHelper->prepareNotificationDataForRequest($updatedCommitteeRequest);
            $this->notificationService->sendNotification($notificationData);
            $organizer = $updatedCommitteeRequest->orgnization->systemAdmin;
            $this->emailHelper->sendAddCommitteeRequest(
                $organizer->email,
                $organizer->name_ar,
                $organizer->name,
                NotificationHelper::getNotificationData('notification.UpdateCommitteeRequestNotificationAr', $data),
                NotificationHelper::getNotificationData('notification.UpdateCommitteeRequestNotificationEn', $data),
                env('APP_URL_FRONTEND', 'http://localhost:4200') . '/'
                . config('notificationUrls.requests') . 'update-committee-requests/'
                . $updatedCommitteeRequest->id,
                $organizer->language_id
            );
        }
        return response()->json($updatedCommitteeRequest, 200);
    }

    private function updateCommitteeHandler($data, $id, $oldCommittee, $user)
    {
        $committee = $this->committeeHelper->prepareCommitteData($data);
        if (config('customSetting.addCommitteeNewFields')) {
            $validator = Validator::make($committee, Committee::rules('updateWithNewFields'),
                Committee::messages('updateWithNewFields')
            );
        } else {
            $validator = Validator::make($committee, Committee::rules('update', $id), Committee::messages('update'));
        }
        if ($validator->fails()) {
            return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
        }
        // new feature add file to committee
        if (config('customSetting.addFileToCommitteeFeature')) {
            $this->committeeService->updateCommitteeDirectory($oldCommittee['id']);
        }

        $updatedCommittee = $this->committeeService->update($id, $committee);
        // update chat room for committee
        $committeeData = $this->committeeService->getById($id);
        if ($committeeData->chat_room_id && $user->chat_user_id) {
            //update chat group users
            $this->chatGroupService->updateCommitteeChatGroupMeemerUsers($committeeData);
            $this->chatService->updateCommitteeRoom($user, $committeeData);
        }
        return response()->json($updatedCommittee, 200);
    }

    public function destroy($id)
    {
        try {
            $user = $this->securityHelper->getCurrentUser();
            $committee = $this->committeeService->getById($id);
            if (($user->organization_id == $committee->organization_id)) {
                $committee->committeeUsers()->delete();
                $this->committeeService->delete($id);
                return response()->json(['message' => "success"], 200);
            } else {
                return response()->json(['error' => "Can't delete this committee!", 'error_ar' => "لا يمكن حذف هذه اللجنة!"], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => "Can't delete this committee!, it has related items to it.", 'error_ar' => 'لا يمكن حذف هذه اللجنة! ، فهي تحتوي على عناصر ذات صلة بها.'], 400);
        }
    }

    public function getPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if (!$user) {
            return response()->json(['error' => 'Don\'t have access!'], 400);
        }
        $canExport = $this->roleRightService->canAccess($user->role_id, config('rights.committeeExport')) != null;
        $result = $this->committeeService->getPagedList($filter, $user->organization_id);
        return response()->json(['Results'=> $result, 'CanExport' => $canExport], 200);
    }

    public function getMyCommitteesPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if (!$user) {
            return response()->json(['error' => 'Don\'t have access!'], 400);
        }
        $canExport = $this->roleRightService->canAccess($user->role_id, config('rights.committeeExport')) != null;
        $result = $this->committeeService->getMyCommitteesPagedList($filter, $user);
        return response()->json(['Results' => $result, 'CanExport' => $canExport], 200);
    }

    public function getList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if (!$user) {
            return response()->json(['error' => 'Don\'t have access!'], 400);
        }
        $filter->include_stakeholders = true;
        return response()->json($this->committeeService->getPagedList($filter, $user->organization_id), 200);
    }

    public function getCurrentOrganizationCommittees(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        if (!$user) {
            return response()->json(['error' => 'Don\'t have access!'], 400);
        }
        if ($user->organization_id != null) {
            return response()->json($this->committeeService->getOrganizationCommittees($user->organization_id), 200);
        }
        return response()->json(['error' => 'Don\'t have access!'], 400);
    }

    public function getCommitteeUsers($committeeId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $committee = $this->committeeService->getbyId($committeeId);
        $committeeUsers = $this->userService->getCommitteeUsersWhosActiveNow($committeeId, $user->id)->toArray();

        $committeeOrganiser = $committee->committeeOrganiser->load('image');
        if (!in_array($committeeOrganiser['id'], array_column($committeeUsers, 'id')) && $user->id != $committeeOrganiser['id']) {
            $committeeUsers[] = $committeeOrganiser;
        }
        $committeeHead = $committee->committeeHead->load('image');
        if (!in_array($committeeHead['id'], array_column($committeeUsers, 'id')) && $user->id != $committeeHead['id']) {
            $committeeUsers[] = $committeeHead;
        }
        return response()->json($committeeUsers, 200);
    }

    public function getCommitteesThatUserMemberOnIt(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        if (!$user) {
            return response()->json(['error' => 'Don\'t have access!'], 400);
        }
        if ($user->organization_id != null) {
            return response()->json($this->committeeService->getCommitteesThatUserMemberOnIt($user->id, $user->organization_id), 200);
        }
        return response()->json(['error' => 'Don\'t have access!'], 400);
    }

    public function getStandingcommitteesPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if (!$user) {
            return response()->json(['error' => 'Don\'t have access!'], 400);
        }
        return response()->json($this->committeeService->getStandingcommitteesPagedList($filter, $user->organization_id), 200);
    }
    public function getTemporaryCommitteesPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if (!$user) {
            return response()->json(['error' => 'Don\'t have access!'], 400);
        }
        return response()->json($this->committeeService->getTemporaryCommitteesPagedList($filter, $user->organization_id), 200);
    }

    public function addCommitteeRecommendations(Request $request, $id)
    {
        $data = $request->all();

        $validator = Validator::make(
            $data,
            Committee::rules('committeeRecommendations'),
            Committee::messages('committeeRecommendations')
        );

        if ($validator->fails()) {
            return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
        }

        $committee = $this->committeeService->addCommitteeRecommendations($id, $data);

        return response()->json([
            'Results' => $committee,
            'Messages' => [
                'message' => 'The recommendations have been added successfully',
                'message_ar' => 'لقد تمت إضافة التوصيات بنجاح'
            ]
        ], 200);
    }

    public function unfreezeCommittee(Request $request,$id)
    {
        $data = $request->all();
        $unfreezeRequest=$this->requestService->getById($id);
        $committee=$this->committeeService->getById($data['request_body']['id']);
        $user = $this->securityHelper->getCurrentUser();
        if (isset($data['request_body']['committee_start_date'])) {
            $committee['committee_start_date'] = $data['request_body']['committee_start_date'];
        } 
        if (isset($data['request_body']['committee_expired_date'])) {
            $committee['committee_expired_date'] = $data['request_body']['committee_expired_date'];
        }
        $committeeArray = $committee->toArray();
        $validator = Validator::make($committeeArray, Committee::rules('unfreezeCommittee'), Committee::messages('unfreezeCommittee'));
        if ($validator->fails()) {
            return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
        }
        $committee->save();
        $unfreezeRequest=$this->requestHelper->prepareAcceptUnfreezeCommitteeMembersRequest($unfreezeRequest,$user);
        $updatedRequest=$this->requestService->update($id,$unfreezeRequest);        
        $notificationData = $this->notificationHelper->prepareNotificationDataForApproveUnfreezeCommitteeMember($committee,$data);
        $this->notificationService->sendNotification($notificationData);
        $RequestCreatorData= $this->userService->getById($data['created_by']);
        if($RequestCreatorData)
        {
            $this->emailHelper->sendAcceptUnfreezeCommitteeRequest(
                $RequestCreatorData->email,
                $RequestCreatorData->name_ar,
                $RequestCreatorData->name,
                NotificationHelper::getNotificationData('notification.UnfreezingCommitteeApprovedNotificationMessageAr', $data),
                NotificationHelper::getNotificationData('notification.UnfreezingCommitteeApprovedNotificationMessageEn', $data),
                config('notificationUrls.committees') . $committee->id,
                $RequestCreatorData->language_id
            );
        }
        return response()->json($committee, 200);
    }




    public function getWorkDoneByCommitteeFeatureVariable()
    {
        $variableValue = config('customSetting.workDoneByCommittee');
        return response()->json(['workDoneByCommitteeFeature' => $variableValue], 200);
    }

    public function notifyHeadMembersCommitteeJob()
    {
        $expiresCommittees = $this->committeeService->getNearedExpiredCommittees();
        foreach ($expiresCommittees as $committee) {
            $organizer = $committee->organization->systemAdmin;
            $notificationData = $this->notificationHelper
                ->prepareNotificationDataForNearedExpiredCommittees($committee, $organizer->id);
            $this->notificationService->sendNotification($notificationData);

            $data = [];
            $data['committee_name_ar'] = $committee->committee_name_ar ?? $committee->committee_name_en;
            $data['committee_name_en'] = $committee->committee_name_en ?? $committee->committee_name_ar;
            $head = $committee->committeeHead;
            $memberUsers = $committee->memberUsers;
            $this->emailHelper->sendEmailNearedExpiredCommittees(
                $organizer->email,
                $head->name_ar,
                $head->name,
                $data['committee_name_ar'],
                $data['committee_name_en'],
                $organizer->language_id
            );
            foreach ($memberUsers as $member) {
                $this->emailHelper->sendEmailNearedExpiredCommittees(
                    $member->email,
                    $member->name,
                    $member->name_ar,
                    $data['committee_name_ar'],
                    $data['committee_name_en'],
                    $organizer->language_id
                );
            }
        }
    }

    public function exportMyCommittees()
    {
        $user = $this->securityHelper->getCurrentUser();
        $lang = $user->language_id;
        $lang = $lang == config('languages.ar') ? 'ar' : 'en';
        $data = new \stdClass();
        $data->user_id = $user->id;
        return Excel::download(new CommitteesExport($data, $lang, $user->organization_id), 'my_committees.xlsx');
    }

    public function exportAllCommittees()
    {
        $user = $this->securityHelper->getCurrentUser();
        $lang = $user->language_id;
        $lang = $lang == config('languages.ar') ? 'ar' : 'en';
        return Excel::download(new CommitteesExport(null,$lang, $user->organization_id), 'all_committees.xlsx');
    }

    public function exportSingleCommittee($committeeId)
    {
        $committee=$this->committeeService->getById($committeeId);
        $user = $this->securityHelper->getCurrentUser();
        $lang = $user->language_id;
        $lang = $lang == config('languages.ar') ? 'ar' : 'en';
        return Excel::download(new CommitteesExport($committee,$lang,null), 'committee_'.$committeeId.'.xlsx');
    }
    public function addFinalOutputFileToCommittee(Request $request, $id)
    {
        $data = $request->all();

        $validator = Validator::make(
            $data,
            Committee::rules('finalOutputFile'),
            Committee::messages('finalOutputFile')
        );
        if ($validator->fails()) {
            return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
        }

        $committee = $this->committeeService->getById($id);
        $finalOutputs = $committee->finalOutputs->toArray();
        if ($committee->committee_type_id == config('committeeTypes.temporary') && count($finalOutputs) > 0) {
            return response()->json([
                'Errors' => [
                    'error' => 'Can not add more than one final output for the temporary committee',
                    'error_ar' => 'لا يمكن إضافة أكثر من مخرج نهائي للجنة المؤقتة'
                ]
            ], 400);
        }

        $committee = $this->committeeService->addFinalOutputFileToCommittee($data, $committee);

        if ($committee == null) {
            return response()->json([
                'Errors' => [
                    'error' => 'Committee not found',
                    'error_ar' => 'لم يتم العثور على اللجنة'
                ]
            ], 400);
        }

        return response()->json([
            'Results' => $committee,
            'Messages' => [
                'message' => 'The final output has been added successfully',
                'message_ar' => 'لقد تمت إضافة المخرج النهائي بنجاح'
            ]
        ], 200);
    }

    public function canRequestDeleteUser(Request $request, $id)
    {
        $user = $this->securityHelper->getCurrentUser();
        return response()->json([
                'Results' => $this->committeeService->canRequestDeleteUser($id, $user->organization_id),
                'Errors' => [
                    "error_message_ar" => "لا يمكنك تقديم طلب حذف جديد",
                    "error_message" => "You cannot submit a new deletion request",
                ]
            ],200);
    }

    public function reminderCommitteeMembers(Request $request, $id)
    {
        $committee = $this->committeeService->getById($id);

        $members = $committee->memberUsers->toArray();
        $members[] = $committee->committeeHead->toArray();
        $members[] = $committee->committeeOrganiser->toArray();
        $members[] = $committee->committeeResponsible->toArray();
        foreach ($members as $member) {
            // Use the user's ID as the key to check for uniqueness
            $uniqueMembers[$member['id']] = $member;
        }
        $uniqueMembers = array_values($uniqueMembers);
        $this->emailHelper->sendReminderFinalCommitteeWorkMail($uniqueMembers, $committee);
        return response()->json([
                'Message' => [
                    "message_ar" => "تم ارسال تذكير اخر ما تم من اعمال اللجنه",
                    "message" => "Reminder of the final committee work has been sent",
                ]
            ],200);
    }

    public function changeCommitteeStatusJob()
    {
        $expiresCommittees = $this->committeeService->getExpiredCommittees();
        foreach ($expiresCommittees as $committee) {
            if ($committee->final_output_url != null) {
                $this->committeeService->updateCommitteeStatus($committee, config('committeeStatuses.closed.id'));
            } else {
                $this->committeeService->updateCommitteeStatus($committee, config('committeeStatuses.final_document_pending.id'));
                $organizer = $committee->organization->systemAdmin;
                $notificationData = $this->notificationHelper
                    ->prepareNotificationDataForExpiredCommitteeMissingFinalOutput($committee, $organizer->id);
                $this->notificationService->sendNotification($notificationData);

                $data = [];
                $data['committee_name_ar'] = $committee->committee_name_ar ?? $committee->committee_name_en;
                $data['committee_name_en'] = $committee->committee_name_en ?? $committee->committee_name_ar;
                $head = $committee->committeeHead;
                $this->emailHelper->ExpiredCommitteeMissingFinalOutput(
                    $organizer->email,
                    $organizer->name_ar,
                    $organizer->name,
                    NotificationHelper::getNotificationData('notification.ExpiredCommitteeMissingFinalOutputTitleAr', $data),
                    NotificationHelper::getNotificationData('notification.ExpiredCommitteeMissingFinalOutputTitleEn', $data),
                    $head->name_ar,
                    $head->name,
                    $committee->committee_name_ar,
                    $committee->committee_name_en,
                    $organizer->language_id
                );
            }
        }
    }

    public function updateCommitteeRecommendationsStatus(Request $request, int $id)
    {
        $hasRecommendation = $request->get('has_recommendation_section');
        $result = $this->committeeService->updateCommitteeRecommendationsStatus($hasRecommendation, $id);
        if ($result) {
            return response()->json(["is_success" => $result], 200);
        } else {
            return response()->json(["is_success" => $result], 400);
        }
    }
    public function getCommitteeHasNatureFeatureVariable()
    {
        $variableValue = config('customSetting.committeeHasNatureFeature');
        return response()->json(['committeeHasNatureFeature' => $variableValue], 200);
    }
}
