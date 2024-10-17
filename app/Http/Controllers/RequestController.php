<?php

namespace App\Http\Controllers;

use App\Exports\ExportAddCommitteeRequests;
use App\Exports\ExportAddMemberToCommitteeRequests;
use App\Exports\ExportDeletedDocumentsRequests;
use App\Exports\ExportDeletedMemberRequests;
use App\Exports\ExportUnfreezeMembersRequests;
use Helpers\NotificationHelper; 
use Helpers\SecurityHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Models\CommitteeUser;
use Models\Request as RequestModel;
use Services\NotificationService;
use Services\RequestService;
use Helpers\CommitteeHelper;
use Helpers\CommitteeUserHelper;
use Helpers\EmailHelper;
use Helpers\RequestHelper;
use Helpers\StorageHelper;
use Maatwebsite\Excel\Facades\Excel;
use Models\Committee;
use Repositories\FileRepository;
use Repositories\OrganizationRepository;
use Services\CommitteeService;
use Services\CommitteeUserService;
use Services\FileService;
use Services\UserService;

use stdClass;

class RequestController extends Controller
{

    private CommitteeService $committeeService;
    private CommitteeUserService $committeeUserService;
    private CommitteeHelper $committeeHelper;
    private RequestService $requestService;
    private RequestHelper $requestHelper;
    private CommitteeUserHelper $committeeUserHelper;
    private SecurityHelper $securityHelper;
    private NotificationHelper $notificationHelper;
    private NotificationService $notificationService;
    private StorageHelper $storageHelper;
    private FileRepository $fileRepository;
    private FileService $fileService;

    private OrganizationRepository $organizationRepository;

    private $emailHelper;
    private $userService;

    public function __construct(
        RequestService $requestService,
        CommitteeService $committeeService,
        CommitteeUserService $committeeUserService,
        SecurityHelper $securityHelper,
        RequestHelper $requestHelper,
        CommitteeHelper $committeeHelper,
        CommitteeUserHelper $committeeUserHelper,
        NotificationHelper $notificationHelper,
        NotificationService $notificationService,
        EmailHelper $emailHelper,
        UserService $userService,
        StorageHelper $storageHelper,
        FileRepository $fileRepository,
        FileService $fileService,
        OrganizationRepository $organizationRepository,

    ) {
        $this->requestService = $requestService;
        $this->committeeUserService = $committeeUserService;
        $this->securityHelper = $securityHelper;
        $this->committeeService = $committeeService;
        $this->requestHelper = $requestHelper;
        $this->committeeHelper = $committeeHelper;
        $this->committeeUserHelper = $committeeUserHelper;
        $this->notificationHelper = $notificationHelper;
        $this->notificationService = $notificationService;
        $this->emailHelper = $emailHelper;
        $this->storageHelper = $storageHelper;
        $this->fileRepository = $fileRepository;
        $this->organizationRepository = $organizationRepository;
        $this->fileService = $fileService;
        $this->userService = $userService;

    }

    public function getPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        return response()->json($this->requestService->getPagedList($filter), 200);
    }

    public function getPagedPendingCommitteesList(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();

        $filter = (object) ($request->all());
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        $params->request_type_id = config("requestTypes.addCommittee");
        $filter->SearchObject = $params;

        return response()->json($this->requestService->getPagedPendingList($filter, $user), 200);
    }

    public function getCommitteeUsersList($committeeId)
    {
        $user = $this->securityHelper->getCurrentUser();
        $usersList = $this->requestService->getCommitteeUsersList($committeeId, $user);

        return response()->json($usersList, 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $errors = [];
        $validator = Validator::make($data, RequestModel::rules('save'), RequestModel::messages('save'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
        }
        if (count($errors) > 0) {
            return response()->json(["error" => $errors], 400);
        }
        $newRequest =  $this->requestService->create($data);
        if (isset($newRequest)) {
            return response($newRequest, 200);
        } else {
            return response([
                'error' => [
                    [
                        'message_ar' => 'فشل إضافة طلب جديدة',
                        'message' => 'Failed to add new Request'
                    ]
                ]
            ], 400);
        }
    }

    public function update(Request $request,  $requestId)
    {
        $data = $request->all();
        $errors = [];
        $validator = Validator::make($data, RequestModel::rules('update'), RequestModel::messages('update'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
        }
        if (count($errors) > 0) {
            return response()->json(["error" => $errors], 400);
        }
        $updatedRequest =  $this->requestService->update($requestId, $data);
        if (isset($updatedRequest)) {
            return response(["Results" => $updatedRequest, "message" => [
                'message_ar' => 'تم تعديل الطلب',
                'message' => 'Edit Request successfully'
            ]], 200);
        } else {
            return response([
                'error' => [
                    [
                        'error_ar' => 'فشل تعديل الطلب',
                        'error' => 'Failed to edit Request'
                    ]
                ]
            ], 400);
        }
    }

    public function show($id)
    {
        return response($this->requestService->getById($id), 200);
    }

    public function destroy($id)
    {
        $this->requestService->delete($id);
        return response(["success" => true], 200);
    }

    public function getAddCommitteeFeatureVariable()
    {
        $variableValue = config('customSetting.addCommitteeNewFields');
        return response()->json(['addCommitteeNewFields' => $variableValue], 200);
    }
    public function getAddUserFeatureVariable()
    {
        $variableValue = config('customSetting.addUserFeature');
        return response()->json(['addUserFeature' => $variableValue], 200);
    }

    public function getAdditionalUserFieldsVariable()
    {
        $variableValue = config('customSetting.additionalUserFields');
        return response()->json(['additionalUserFields' => $variableValue], 200);
    }

    public function getDeleteCommitteeFeatureVariable()
    {
        $variableValue = config('customSetting.deleteUserFeature');
        return response()->json(['deleteUserFeature' => $variableValue], 200);
    }

    //! method: POST  ==> request/delete-user/{id}
    // $id -> user_id
    // body-> (Reason, file, committee_id ) 
    public function createDeleteUserRequest(Request $request, $id)
    {

        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $errors = [];
        $canDeleteMember = $this->committeeService->canRequestDeleteUser($id, $user->organization_id);
        if ($canDeleteMember) {
            $validator = Validator::make($data, RequestModel::rules('deleteCommitteeUserRequest'));
            if ($validator->fails()) {
                $errors = array_merge($errors, array_values($validator->errors()->toArray()));
            }
            if (count($errors) > 0) {
                return response()->json(["error" => $errors], 400);
            }

            if (isset($data['proof_file'])) {
                // create file for evidence document
                $organization = $this->organizationRepository->find($user->organization_id, array('*'));
                $result = explode('/', $data['proof_file']);
                $fileName = $result[count($result) - 1];
                $storageFile =  $this->storageHelper->mapSystemFile($fileName, $data['proof_file'], 0, $organization->systemAdmin);
                $attachmentFile = $this->fileRepository->create($storageFile);
                $data['evidence_document_id']  =  $attachmentFile->id;
            }

            $committeeUserId = $this->committeeUserService->getCommitteeUser($id, $data['committee_id']);

            $committee = $this->committeeService->getById($data["committee_id"]);
            $user_details = $this->userService->getById($id);

            $deleteCommitteeUser = $this->requestHelper->prepareDeleteUserRequestData($data, $user, $committeeUserId->id, $user_details, $committee);

            $createdDeleteUserRequest = $this->requestService->deleteUserfromCommitteeRequest($deleteCommitteeUser);

            if (isset($createdDeleteUserRequest)) {
                $notificationData = $this->notificationHelper->prepareNotificationDataForRequest($createdDeleteUserRequest);
                $this->notificationService->sendNotification($notificationData);
                $dataForTemplate = [];
                $dataForTemplate["committee_name_en"] = $createdDeleteUserRequest->request_body["committee_name_en"];
                $dataForTemplate["committee_name_ar"] = $createdDeleteUserRequest->request_body["committee_name_ar"];
                $organizer = $createdDeleteUserRequest->orgnization->systemAdmin;
                $this->emailHelper->sendDeleteMemberFromCommitteeRequest(
                    $organizer->email,
                    $organizer->name_ar,
                    $organizer->name,
                    NotificationHelper::getNotificationData('notification.DeleteCommitteeUserRequestNotificationAr', $dataForTemplate),
                    NotificationHelper::getNotificationData('notification.DeleteCommitteeUserRequestNotificationEn', $dataForTemplate),
                    config("resetpassword.url_frontend") . "/" . config('notificationUrls.requests') . 'delete-member/' . $createdDeleteUserRequest->id,
                    $organizer->language_id
                );
            }

            return response()->json($createdDeleteUserRequest, 200);
        } else {
            return response()->json([
                'Errors' => [
                    "error_message_ar" => "لا يمكنك تقديم طلب حذف جديد",
                    "error_message" => "You cannot submit a new deletion request",
                ],
                'error_code' => 4
            ], 400);
        }


        // $committee = $this->committeeHelper->prepareCommitteData($data);
        // $CommitteeUsers = array_column($committee['member_users'], 'id');
        // $removefilterMemberUsers = function ($user) use ($CommitteeUsers) {
        //     return !in_array($user['user_id'], $CommitteeUsers);
        // };


        // $removedCommittieeUser = array_filter($oldCommittee->committeeUsers->toArray(), $removefilterMemberUsers);

        // $validator = Validator::make($committee, Committee::rules('update', $id), Committee::messages('update'));
        // if ($validator->fails()) {
        //     return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
        // }


        // $createdDeleteUserRequests = [];
        // foreach ($removedCommittieeUser as $committeeUser) {
        //     $deleteUserRequest = $this->requestHelper->prepareDeleteUserRequestData($committeeUser['id'], $user);
        //     $createdDeleteUserRequest = $this->requestService->addUserToCommitteeRequest($deleteUserRequest);
        //     $createdDeleteUserRequests[] = $createdDeleteUserRequest;
        // }
        // $requests = ["deleteCommitteeUserRequests" => $createdDeleteUserRequests];
        // return response()->json($requests, 200);
    }
    public function unFreezeCommitteesRequest(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $data = $request->all();
        $errors = [];
        $validator = Validator::make(
            $data,
            RequestModel::rules('unFreezeCommitteeRequest'),
            RequestModel::messages('unFreezeCommitteeRequest')
        );
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
            if (count($errors) > 0) {
                return response()->json(["error" => $errors], 400);
            }
        }
        $data['created_by'] = $user->id;
        $data['organization_id'] = $user->organization_id;
        $data['request_type_id'] = config('requestTypes.unfreezeCommittee');
        $newRequest =  $this->requestService->create($data);
        if (isset($newRequest)) {
            $notificationData = $this->notificationHelper->prepareNotificationDataForRequest($newRequest);
            $this->notificationService->sendNotification($notificationData);
            $organizer = $newRequest->orgnization->systemAdmin;
            $this->emailHelper->sendUnFreezeCommitteeRequest(
                $organizer->email,
                $organizer->name_ar,
                $organizer->name,
                NotificationHelper::getNotificationData('notification.UnFreezeCommitteeRequestNotificationAr', $data),
                NotificationHelper::getNotificationData('notification.UnFreezeCommitteeRequestNotificationEn', $data),
                config('notificationUrls.requests') . $newRequest->id,
                $organizer->language_id
            );
            $result = [
                'message' => [
                    [
                        'message_ar' => "لقد تم ارسال الطلب بنجاح",
                        'message' => 'The request has been sent successfully'
                    ]
                ],
                'Results' => $newRequest
            ];
            return response($result, 200);
        }
        return response([
            'error' => [
                [
                    'message_ar' => 'فشل إضافة طلب جديدة',
                    'message' => 'Failed to add new Request'
                ]
            ]
        ], 400);
    }

    public function getCommitteeRequestsPagedList(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $filter = (object) ($request->all());
        return response()->json($this->requestService->getCommitteeRequestsPagedList(
            $filter,
            config('requestTypes.addCommittee'),
            $user
        ), 200);
    }

    public function getCommitteeUpdateRequestsPagedList(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $filter = (object) ($request->all());
        return response()->json($this->requestService->getCommitteeRequestsPagedList(
            $filter,
            config('requestTypes.updateCommittee'),
            $user
        ), 200);
    }

    public function getAddMemberCommitteeRequestsPagedList(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $filter = (object) ($request->all());
        return response()->json($this->requestService->getCommitteeRequestsPagedList(
            $filter,
            config('requestTypes.addUserToCommittee'),
            $user
        ), 200);
    }

    public function getDeleteMemberCommitteeRequestsPagedList(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $filter = (object) ($request->all());
        return response()->json($this->requestService->getCommitteeRequestsPagedList(
            $filter,
            config('requestTypes.deleteUser'),
            $user
        ), 200);
    }
    public function getUnFreezeMemberRequestsPagedList(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $filter = (object) ($request->all());
        return response()->json($this->requestService->getCommitteeRequestsPagedList(
            $filter,
            config('requestTypes.unfreezeCommittee'),
            $user
        ), 200);
    }
    public function showUnFreezeMemberRequest($id)
    {
        return response()->json(
            $this->requestService->getRequestDetails($id),
            200
        );
    }

    public function rejectRequest(Request $request, $id)
    {
        $errors = [];
        $data = $request->all();
        $oldRequest = $this->requestService->getById($id);
        $user = $this->securityHelper->getCurrentUser();
        $updatedRequest = $this->requestHelper->prepareRejectUnfreezeCommitteeMembersRequest($oldRequest, $user, $data);
        $validator = Validator::make($updatedRequest, RequestModel::rules('rejectRequest'), RequestModel::messages('rejectRequest'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
        }
        $updatedRequest = $this->requestService->update($id, $updatedRequest);
        $rejectedRequest=$this->requestService->getById($id);
        $notificationData = $this->notificationHelper->prepareNotificationDataForRejectRequest($rejectedRequest);
        $this->notificationService->sendNotification($notificationData);
        $RequestCreatorData= $this->userService->getById($updatedRequest['created_by']);
        if($RequestCreatorData)
        {
            $this->emailHelper->sendRejectRequest(
                $RequestCreatorData->email,
                $RequestCreatorData->name_ar,
                $RequestCreatorData->name,
                NotificationHelper::getNotificationData('notification.RejectNotificationAr', $data),
                NotificationHelper::getNotificationData('notification.RejectNotificationEn', $data),
                config("resetpassword.url_frontend") . "/" . config('notificationUrls.committees'),
                $RequestCreatorData->language_id
            );
        }
        return response()->json($request, 200);
    }



    public function AcceptAddCommitteeRequest(Request $request, $id)
    {
        $data = $request->all();
        $requestData = $this->requestService->getById($id);
        $user = $this->securityHelper->getCurrentUser();
        $data['organization_id'] = $user->organization_id;
        $statusData = config('committeeStatuses.inProgress');
        if ($statusData) {
                $data['request_body']['committee_status_id']=$statusData['id'];       
        }
        $committee = $this->committeeHelper->prepareCommitteData($data['request_body']);
        $validator = Validator::make($committee, Committee::rules('save'), Committee::messages('save'));
        if ($validator->fails()) {
            return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
        }
        $createdCommittee = $this->committeeService->create($committee);
        if ($createdCommittee['directory_id'] == null) {
            $this->committeeService->createCommitteeDirectory($createdCommittee['id']);
        }
        $requestData->approved_by = $user->id;
        $requestData->is_approved = true;
        $requestData = $requestData->toArray();
        $updatedRequest = $this->requestService->update($id, $requestData);
        $notificationData = $this->notificationHelper->prepareNotificationDataForAcceptAddCommitteeRequest($createdCommittee, $requestData);
        $this->notificationService->sendNotification($notificationData);
        $RequestCreatorData= $this->userService->getById($createdCommittee['committee_head_id']);
        if($RequestCreatorData)
        {
            $this->emailHelper->sendAcceptAddCommitteeRequest(
                $RequestCreatorData->email,
                $RequestCreatorData->name_ar,
                $RequestCreatorData->name,
                NotificationHelper::getNotificationData('notification.ApproveAddCommitteeNotificationAr', $data),
                NotificationHelper::getNotificationData('notification.ApproveAddCommitteeNotificationEn', $data),
                config('notificationUrls.committees'),
                $RequestCreatorData->language_id
            );
        }
        return response()->json($createdCommittee, 200);
    }

    public function addCommitteeMembersRequest(Request $request){
        $user = $this->securityHelper->getCurrentUser();
        $data = $request->all();
        $errors = [];
        $committee=$this->committeeService->getById($data['committee_id']);
        $committeeUsers=array_column($committee->committeeUsers->toArray(), 'user_id');
        $filterMemberUsers = function($user) use ($committeeUsers) {
            return !in_array($user['id'], $committeeUsers);
        };
        $filteredMemberUsers = array_filter($data['member_users'], $filterMemberUsers);
        $addUserRequest = $this->requestHelper->prepareAddUserRequestData($data,$filteredMemberUsers,$user,$committee);
        $requests = $this->requestService->addUserToCommitteeRequest($addUserRequest);
        foreach ($requests as $newRequest) {
                $notificationData = $this->notificationHelper-> prepareNotificationDataForRequest($newRequest);
                $this->notificationService->sendNotification($notificationData);
                $organizer = $newRequest->orgnization->systemAdmin;
                $this->emailHelper->sendAddMemberToCommitteeRequest(
                    $organizer->email,
                    $organizer->name_ar,
                    $organizer->name,
                    NotificationHelper::getNotificationData('notification.AddUserToCommitteeRequestNotificationAr', $data),
                    NotificationHelper::getNotificationData('notification.AddUserToCommitteeRequestNotificationEn', $data),
                    config('notificationUrls.requests') . $newRequest->id,
                    $organizer->language_id
                );
        }
        return response()->json($requests, 200);
    }

    public function getRemoveCommitteeCodeFeatureVariable()
    {
        $variableValue = config('customSetting.removeCommitteeCode');
        return response()->json(['removeCommitteeCodeField' => $variableValue], 200);
    }

    public function getDeleteFileFeatureVariable()
    {
        $variableValue = config('customSetting.deleteFile');
        return response()->json(['deleteFileField' => $variableValue], 200);
    }

    public function deleteFileRequest(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $data = $request->all();
        $errors = [];
        $validator = Validator::make(
            $data,
            RequestModel::rules('deleteFileRequest'),
            RequestModel::messages('deleteFileRequest')
        );
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
            if (count($errors) > 0) {
                return response()->json(["error" => $errors], 400);
            }
        }


        if (isset($data['evidence_document_url'])) {
            // create file for evidence document
            $organization = $this->organizationRepository->find($user->organization_id, array('*'));
            $result = explode('/', $data['evidence_document_url']);
            $fileName = $result[count($result) - 1];
            $storageFile =  $this->storageHelper->mapSystemFile($fileName, $data['evidence_document_url'], 0, $organization->systemAdmin);
            $attachmentFile = $this->fileRepository->create($storageFile);
            $data['evidence_document_id']  =  $attachmentFile->id;
        }


        $data['created_by'] = $user->id;
        $data['organization_id'] = $user->organization_id;
        $data['request_type_id'] = config('requestTypes.deleteFile');

        $file = $this->fileService->getById($data["request_body"]["file_id"]);
        $data['request_body']["file"] = $file;
        $data['request_body']["committee_id"] = $file->directory->committees[0]->id;
        $data['request_body']["committee_name_ar"] = $file->directory->committees[0]->committee_name_ar;
        $data['request_body']["committee_name_en"] = $file->directory->committees[0]->committee_name_en;
        $data['committee_name_en'] = $file->directory->committees[0]->committee_name_en;
        $data['committee_name_ar'] = $file->directory->committees[0]->committee_name_ar;


        $data['target_id'] = $data['request_body']['file_id'];
        $newRequest =  $this->requestService->create($data);
        if (isset($newRequest)) {
            $notificationData = $this->notificationHelper->prepareNotificationDataForRequest($newRequest);
            $this->notificationService->sendNotification($notificationData);
            $organizer = $newRequest->orgnization->systemAdmin;
            $this->emailHelper->sendDeleteDocumentRequest(
                $organizer->email,
                $organizer->name_ar,
                $organizer->name,
                NotificationHelper::getNotificationData('notification.DeleteRequestNotificationAr', $data),
                NotificationHelper::getNotificationData('notification.DeleteRequestNotificationEn', $data),
                config('notificationUrls.requests') . $newRequest->id,
                $organizer->language_id
            );


        $result = [
            'message' => [
                [
                    'message_ar' => "لقد تم ارسال الطلب بنجاح",
                    'message' => 'The request has been sent successfully'
                ]
            ],
            'Results' => $newRequest
        ];
        return response($result, 200);
    }
}




 //! method: POST  ==> /requests/add-member-committee/${request_id}/accept
    // body-> (user_id, committee_id, is_head, committee_user_start_date, committee_user_expired_date ) 
    public function acceptAddUserRequest(Request $request, $id)
    {
        $user = $this->securityHelper->getCurrentUser();
        $data = $request->all();
        //! add user to committee
        $committeeUserData = $this->committeeUserHelper->prepareCommitteUserData($data);
        $errors = [];
        $validator = Validator::make($committeeUserData, CommitteeUser::rules('save'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
        }
        if (count($errors) > 0) {
            return response()->json(["error" => $errors], 400);
        }

        //! check if the committee_user is already exists
        $committeeUser = $this->committeeUserService->getCommitteeUser($data["user_id"], $data['committee_id']);
        if (!$committeeUser) {
            $createdCommitteeUser = $this->committeeUserService->create($committeeUserData);
            $committee = $this->committeeService->getById($data['committee_id']);
            $directory = $committee->directory;
            if (!in_array($data["user_id"], $directory->storageAccess->toArray())) {
                $directory->storageAccess()->create([
                    'user_id' => $data["user_id"], 'can_read' => true, 'can_edit' => true, 'can_delete' => true
                ]);
            }
        }

        $committee=$this->committeeService->getById($data['committee_id']);
        $committee->member_users=$committee->load('memberUsers');
        $committee['committeee_members_count'] =$committee->memberUsers->count();
        $this->committeeService->update($data['committee_id'], $committee->toArray());
        //! update the request to approved
        $updatedAddUserRequest = $this->requestHelper->updateAcceptAddUserRequest($user);
        $this->requestService->update($id, $updatedAddUserRequest);
        $updatedRequest=$this->requestService->getById($id);


        if (isset($updatedRequest)) {

            $notificationData = $this->notificationHelper->prepareNotificationDataForAcceptAddMemberRequest($updatedRequest);
            $this->notificationService->sendNotification($notificationData);
            $dataForTemplate=[];
            $dataForTemplate["committee_name_en"]=$updatedRequest->request_body["committee_name_en"];
            $dataForTemplate["committee_name_ar"]=$updatedRequest->request_body["committee_name_ar"];
            $organizer = $updatedRequest->orgnization->systemAdmin;
            $dataForTemplate["user_name"]=$organizer->name ?? $organizer->name_ar;
            $dataForTemplate["user_phone"]=$organizer->user_phone ? $organizer->user_phone:'';
            $dataForTemplate["user_email"]=$organizer->email ?? $organizer->name_ar;
            $this->emailHelper->sendAcceptRequest(
                $organizer->email,
                $organizer->name_ar,
                $organizer->name,
                NotificationHelper::getNotificationData('notification.addMemberRequestApprovedNotificationMessageAr', $dataForTemplate),
                NotificationHelper::getNotificationData('notification.addMemberRequestApprovedNotificationMessageEn', $dataForTemplate),
                config("resetpassword.url_frontend")."/". config('notificationUrls.committees'),
                $organizer->language_id
            );

            $addedUser= $this->userService->getById($createdCommitteeUser->user_id);
            if($addedUser)
            {
                $this->emailHelper->sendUserAboutAddingToCommitteeEmail(
                    $addedUser->email,
                    $addedUser->name_ar,
                    $addedUser->name,
                    NotificationHelper::getNotificationData('notification.AddUserToCommitteeNotificationMessageAr', $dataForTemplate),
                    NotificationHelper::getNotificationData('notification.AddUserToCommitteeNotificationMessageEn', $dataForTemplate),
                    $addedUser->language_id
                );
            }
        }
        return response()->json($updatedRequest, 200);

    }


    //! method: POST  ==> /requests/add-member-committee/${request_id}/reject
    // body-> ( reject_reason) 
    public function rejectAddUserRequest(Request $request, $id)
    {
        $user = $this->securityHelper->getCurrentUser();
        $data = $request->all();
        $errors = [];
        $validator = Validator::make($data, RequestModel::rules('rejectAddUserRequest'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
        }
        if (count($errors) > 0) {
            return response()->json(["error" => $errors], 400);
        }
        //! update the request to rejected
        $updatedAddUserRequest = $this->requestHelper->updateRejectAddUserRequest($user, $data['reject_reason']);
        $this->requestService->update($id, $updatedAddUserRequest);
        $updatedRequest=$this->requestService->getById($id);

        if (isset($updatedRequest)) {

            $notificationData = $this->notificationHelper->prepareNotificationDataForRejectRequest($updatedRequest);
            $this->notificationService->sendNotification($notificationData);
            $dataForTemplate=[];
            $dataForTemplate['committee_name_en'] = $updatedRequest->request_body['committee_name_en'];
            $dataForTemplate['committee_name_ar'] = $updatedRequest->request_body['committee_name_ar'];
            $dataForTemplate['reject_reason'] = $updatedRequest->reject_reason;

            $organizer = $updatedRequest->orgnization->systemAdmin;
            $this->emailHelper->sendRejectRequest(
                $organizer->email,
                $organizer->name_ar,
                $organizer->name,
                NotificationHelper::getNotificationData('notification.addMemberRequestRejectedNotificationMessageAr', $dataForTemplate),
                NotificationHelper::getNotificationData('notification.addMemberRequestRejectedNotificationMessageEn', $dataForTemplate),
                config("resetpassword.url_frontend")."/". config('notificationUrls.committees'),
                $organizer->language_id
            );

        }



        return response()->json($updatedRequest, 200);
    }




    //! method: POST  ==> /requests/delete-member-committee/${request_id}/accept
    public function acceptDeleteUserRequest(Request $request, $id)
    {
        $user = $this->securityHelper->getCurrentUser();
        //! delete user from committee
        $deleteUserRequest = $this->requestService->getById($id);
        //! check if the committee_user is not found
        $committee_user = $this->committeeUserService->getByIdOrNull($deleteUserRequest["request_body"]["committee_user_id"]);
        if ($committee_user) {
            $committee=$this->committeeService->getById($committee_user->committee_id);
            $systemAdmin = $committee->organization->systemAdmin;
            $storageAccessUsers = $committee->directory->storageAccess;
            foreach ($storageAccessUsers as $storageAccessUser) {
                if ($storageAccessUser['user_id'] == $committee_user->user_id && $storageAccessUser['user_id'] != $systemAdmin->id) {
                    $storageAccessUser->delete();
                    break;
                }
            }
            $this->committeeUserService->delete($deleteUserRequest["request_body"]["committee_user_id"]);
            $committee->member_users=$committee->load('memberUsers');
            $committee['committeee_members_count'] = $committee->memberUsers->count();  
            $this->committeeService->update($committee_user->committee_id, $committee->toArray());
        }


        //! update the request to approved
        $updatedDeleteUserRequest = $this->requestHelper->updateAcceptAddUserRequest($user);
        $this->requestService->update($id, $updatedDeleteUserRequest);
        $updatedRequest=$this->requestService->getById($id);


        if (isset($updatedRequest)) {

            $notificationData = $this->notificationHelper->prepareNotificationDataForAcceptDeleteMemberRequest($updatedRequest);
            $this->notificationService->sendNotification($notificationData);
            $dataForTemplate=[];
            $dataForTemplate["committee_name_en"]=$updatedRequest->request_body["committee_name_en"];
            $dataForTemplate["committee_name_ar"]=$updatedRequest->request_body["committee_name_ar"];
            $organizer = $updatedRequest->orgnization->systemAdmin;
            $this->emailHelper->sendAcceptRequest(
                $organizer->email,
                $organizer->name_ar,
                $organizer->name,
                NotificationHelper::getNotificationData('notification.DeleteMemberRequestApprovedNotificationMessageAr', $dataForTemplate),
                NotificationHelper::getNotificationData('notification.DeleteMemberRequestApprovedNotificationMessageEn', $dataForTemplate),
                config("resetpassword.url_frontend")."/". config('notificationUrls.committees'),
                $organizer->language_id
            );

        }


        return response()->json($updatedRequest, 200);
    }



    //! method: POST  ==> /requests/delete-member-committee/${request_id}/reject
    // body-> ( reject_reason) 
    public function rejectDeleteUserRequest(Request $request, $id)
    {
        $user = $this->securityHelper->getCurrentUser();
        $data = $request->all();
        $errors = [];
        $validator = Validator::make($data, RequestModel::rules('rejectAddUserRequest'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
        }
        if (count($errors) > 0) {
            return response()->json(["error" => $errors], 400);
        }
        //! update the request to rejected
        $updatedDeleteUserRequest = $this->requestHelper->updateRejectAddUserRequest($user, $data['reject_reason']);
        $this->requestService->update($id, $updatedDeleteUserRequest);
        $updatedRequest=$this->requestService->getById($id);

        if (isset($updatedRequest)) {

            $notificationData = $this->notificationHelper->prepareNotificationDataForRejectRequest($updatedRequest);
            $this->notificationService->sendNotification($notificationData);
            $dataForTemplate=[];
            $dataForTemplate['committee_name_en'] = $updatedRequest->request_body['committee_name_en'];
            $dataForTemplate['committee_name_ar'] = $updatedRequest->request_body['committee_name_ar'];
            $dataForTemplate['reject_reason'] = $updatedRequest->reject_reason;

            $organizer = $updatedRequest->orgnization->systemAdmin;
            $this->emailHelper->sendRejectRequest(
                $organizer->email,
                $organizer->name_ar,
                $organizer->name,
                NotificationHelper::getNotificationData('notification.DeleteMemberRequestRejectedNotificationMessageAr', $dataForTemplate),
                NotificationHelper::getNotificationData('notification.DeleteMemberRequestRejectedNotificationMessageEn', $dataForTemplate),
                config("resetpassword.url_frontend")."/". config('notificationUrls.committees'),
                $organizer->language_id
            );

        }

        $notificationData = $this->notificationHelper->prepareNotificationDataForRejectRequest($updatedRequest);
        $this->notificationService->sendNotification($notificationData);
        return response()->json($updatedRequest, 200);
    }



    public function getDeleteFileRequestsPagedList(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        $filter = (object) ($request->all());
        $deleteFileRequests = $this->requestService->getCommitteeRequestsPagedList(
            $filter,
            config('requestTypes.deleteFile'),
            $user
        );

        return response()->json($deleteFileRequests, 200);
    }



    //! method: POST  ==> /requests/delete-file/${request_id}/accept
    public function acceptDeleteFileRequest(Request $request, $id)
    {
        $user = $this->securityHelper->getCurrentUser();
        //! delete file from committee
        $deleteFileRequest = $this->requestService->getById($id);
        //! check if the file is not found
        $file = $this->fileService->getByIdOrNull($deleteFileRequest["request_body"]["file_id"]);
        if ($file) {
            $this->fileService->delete($deleteFileRequest["request_body"]["file_id"]);
        }

        //! update the request to approved
        $updatedDeleteUserRequest = $this->requestHelper->updateAcceptAddUserRequest($user);
        $this->requestService->update($id, $updatedDeleteUserRequest);
        $updatedRequest=$this->requestService->getById($id);

        if (isset($updatedRequest)) {

            $notificationData = $this->notificationHelper->prepareNotificationDataForAcceptDeleteFileRequest($updatedRequest);
            $this->notificationService->sendNotification($notificationData);
            $dataForTemplate=[];
            $dataForTemplate['committee_name_en'] = $updatedRequest->request_body['committee_name_en'];
            $dataForTemplate['committee_name_ar'] = $updatedRequest->request_body['committee_name_ar'];
            $dataForTemplate["file_name"] = $updatedRequest->request_body["file"]['file_name'];
            $dataForTemplate["file_name_ar"] = $updatedRequest->request_body["file"]['file_name_ar'];
            $organizer = $updatedRequest->orgnization->systemAdmin;
            $this->emailHelper->sendAcceptRequest(
                $organizer->email,
                $organizer->name_ar,
                $organizer->name,
                NotificationHelper::getNotificationData('notification.DeleteFileRequestApprovedNotificationMessageAr', $dataForTemplate),
                NotificationHelper::getNotificationData('notification.DeleteFileRequestApprovedNotificationMessageEn', $dataForTemplate),
                config("resetpassword.url_frontend")."/". config('notificationUrls.committees'),
                $organizer->language_id
            );

        }


        return response()->json($updatedRequest, 200);
    }



    //! method: POST  ==> /requests/delete-file/${request_id}/reject
    // body-> ( reject_reason) 
    public function rejectDeleteFileRequest(Request $request, $id)
    {
        $user = $this->securityHelper->getCurrentUser();
        $data = $request->all();
        $errors = [];
        $validator = Validator::make($data, RequestModel::rules('rejectAddUserRequest'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
        }
        if (count($errors) > 0) {
            return response()->json(["error" => $errors], 400);
        }
        //! update the request to rejected
        $updatedDeleteUserRequest = $this->requestHelper->updateRejectAddUserRequest($user, $data['reject_reason']);
        $this->requestService->update($id, $updatedDeleteUserRequest);
        $updatedRequest=$this->requestService->getById($id);


        if (isset($updatedRequest)) {

            $notificationData = $this->notificationHelper->prepareNotificationDataForRejectRequest($updatedRequest);
            $this->notificationService->sendNotification($notificationData);
            $dataForTemplate=[];
            $dataForTemplate['committee_name_en'] = $updatedRequest->request_body['committee_name_en'];
            $dataForTemplate['committee_name_ar'] = $updatedRequest->request_body['committee_name_ar'];
            $dataForTemplate["file_name"] = $updatedRequest->request_body["file"]['file_name'];
            $dataForTemplate["file_name_ar"] = $updatedRequest->request_body["file"]['file_name_ar'];
            $dataForTemplate['reject_reason'] = $updatedRequest->reject_reason;

            $organizer = $updatedRequest->orgnization->systemAdmin;
            $this->emailHelper->sendRejectRequest(
                $organizer->email,
                $organizer->name_ar,
                $organizer->name,
                NotificationHelper::getNotificationData('notification.DeleteFileRequestRejectedNotificationMessageAr', $dataForTemplate),
                NotificationHelper::getNotificationData('notification.DeleteFileRequestRejectedNotificationMessageEn', $dataForTemplate),
                config("resetpassword.url_frontend")."/". config('notificationUrls.committees'),
                $organizer->language_id
            );

        }


        return response()->json($updatedRequest, 200);
    }

    public function acceptUpdateCommitteeRequest(Request $request, $id)
    {
        $user = $this->securityHelper->getCurrentUser();
        //! delete file from committee
        $updateCommitteeRequest = $this->requestService->getById($id);
        $oldCommittee = $this->committeeService->getById($updateCommitteeRequest->target_id);
        $oldCommittee = $this->committeeHelper->prepareCommitteeUpdateData(
            $oldCommittee,
            $updateCommitteeRequest->request_body
        );
        $oldCommittee->save();
        if ($oldCommittee->has_recommendation_section == false) {
            $oldCommittee->recommendations()->delete();
        }
        //! update the request to approved
        $updateCommitteeRequest = $this->requestHelper->updateAcceptAddUserRequest($user);
        $this->requestService->update($id, $updateCommitteeRequest);
        $updatedRequest = $this->requestService->getById($id);

        if (isset($updatedRequest)) {

            $notificationData = $this->notificationHelper->prepareNotificationDataForUpdateCommitteeRequest($updatedRequest);
            $this->notificationService->sendNotification($notificationData);
            $dataForTemplate = [];
            $dataForTemplate['committee_name_en'] = $updatedRequest->request_body['committee_name_en'];
            $dataForTemplate['committee_name_ar'] = $updatedRequest->request_body['committee_name_ar'];
            $organizer = $updatedRequest->orgnization->systemAdmin;
            $this->emailHelper->sendAcceptRequest(
                $organizer->email,
                $organizer->name_ar,
                $organizer->name,
                NotificationHelper::getNotificationData('notification.UpdateCommitteeNotificationMessageAr', $dataForTemplate),
                NotificationHelper::getNotificationData('notification.UpdateCommitteeNotificationMessageEn', $dataForTemplate),
                config("resetpassword.url_frontend") . "/" . config('notificationUrls.committees'),
                $organizer->language_id
            );
        }


        return response()->json($updatedRequest, 200);
    }

    public function exportAddCommitteeRequests()
    {
        $user = $this->securityHelper->getCurrentUser();
        $requestTypeId=config("requestTypes.addCommittee");
        $lang = $user->language_id;
        $lang = $lang == config('languages.ar') ? 'ar' : 'en';
        $organizationId=$user->organization_id;
        
        return Excel::download(new ExportAddCommitteeRequests($organizationId,$lang, $requestTypeId), 'committee_requests.xlsx');
    }
    public function exportAddMemberToCommitteeRequests()
    {
        $user = $this->securityHelper->getCurrentUser();
        $requestTypeId=config("requestTypes.addUserToCommittee");
        $lang = $user->language_id;
        $lang = $lang == config('languages.ar') ? 'ar' : 'en';
        $organizationId=$user->organization_id;
        
        return Excel::download(new ExportAddMemberToCommitteeRequests($organizationId,$lang, $requestTypeId), 'committee_requests.xlsx');
    }

    public function exportDeleteMemberFromCommitteeRequests()
    {
        $user = $this->securityHelper->getCurrentUser();
        $requestTypeId=config("requestTypes.deleteUser");
        $lang = $user->language_id;
        $lang = $lang == config('languages.ar') ? 'ar' : 'en';
        $organizationId=$user->organization_id;
        
        return Excel::download(new ExportDeletedMemberRequests($organizationId,$lang, $requestTypeId), 'committee_requests.xlsx');
    }


    public function exportDeleteDocumentsRequests()
    {
        $user = $this->securityHelper->getCurrentUser();
        $requestTypeId=config("requestTypes.deleteFile");
        $lang = $user->language_id;
        $lang = $lang == config('languages.ar') ? 'ar' : 'en';
        $organizationId=$user->organization_id;
        
        return Excel::download(new ExportDeletedDocumentsRequests($organizationId,$lang, $requestTypeId), 'committee_requests.xlsx');
    }
    public function exportUnfreezeMembersRequests()
    {
        $user = $this->securityHelper->getCurrentUser();
        $requestTypeId=config("requestTypes.unfreezeCommittee");
        $lang = $user->language_id;
        $lang = $lang == config('languages.ar') ? 'ar' : 'en';
        $organizationId=$user->organization_id;
        
        return Excel::download(new ExportUnfreezeMembersRequests($organizationId,$lang, $requestTypeId), 'committee_requests.xlsx');
    }
}
