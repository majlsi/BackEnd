<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Connectors\ChatConnector;
use Helpers\SecurityHelper;
use Helpers\ChatHelper;
use Helpers\ChatGroupHelper;
use Helpers\NotificationHelper;
use Services\ChatGroupService;
use Services\MeetingService;
use Services\ChatService;
use Services\CommitteeService;
use Services\UserService;
use Services\ChatGroupUserService;
use Services\NotificationService;
use Models\ChatGroup;
use Validator;
use Lang;
use Carbon\Carbon;
use Helpers\EmailHelper;

class ChatGroupController extends Controller
{
    private $securityHelper;
    private $chatGroupService;
    private $meetingService;
    private $chatGroupHelper;
    private $chatService;
    private $committeeService;
    private $userService;
    private $chatGroupUserService;
    private $chatHelper;
    private $notificationService;
    private $notificationHelper;
    private $emailHelper;
    public function __construct(SecurityHelper $securityHelper,ChatGroupService $chatGroupService,
                MeetingService $meetingService, ChatGroupHelper $chatGroupHelper, ChatService $chatService,
                CommitteeService $committeeService, UserService $userService, ChatGroupUserService $chatGroupUserService,
                ChatHelper $chatHelper, NotificationService $notificationService, NotificationHelper $notificationHelper,EmailHelper $emailHelper) {
        $this->securityHelper = $securityHelper;
        $this->chatGroupService = $chatGroupService;
        $this->meetingService = $meetingService;
        $this->chatGroupHelper = $chatGroupHelper;
        $this->chatService = $chatService;
        $this->committeeService = $committeeService;
        $this->userService = $userService;
        $this->chatGroupUserService = $chatGroupUserService;
        $this->chatHelper = $chatHelper;
        $this->notificationService = $notificationService;
        $this->notificationHelper = $notificationHelper;
        $this->emailHelper = $emailHelper;
    }

    public function show(int $chatGroupId){
        $user = $this->securityHelper->getCurrentUser();
        $chatGroup = $this->chatGroupService->getChatGroupDetailsByIdAndOrganizationId($chatGroupId,$user->organization_id,$user->id);
        if ($chatGroup) {
            $chatGroup->created_at = Carbon::parse($chatGroup->created_at)->addHours($user->organization->timeZone->diff_hours);
            $response = $this->chatService->getChatGroupRoom($user,$chatGroup);
            if ($response['is_success']) {
                $response['response']['chatRoom']['chat_name_ar'] = $chatGroup->chat_group_name_ar;
                $response['response']['chatRoom']['chat_name_en'] = $chatGroup->chat_group_name_en;
                $response['response']['chatGroup'] = $chatGroup;
                if($response['is_success']) {
                    return response()->json($response['response'], 200);
                }
            }
            return response()->json($response['response'], $response['resopnse_code'] );
        }
        return response()->json(['error' => 'Chat Group Not Found', 'error_ar' => 'المحادثة غير موجوده'], 404);
    }

    public function store(Request $request) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $errors = [];
        
        $chatGroupData = $this->chatGroupHelper->prepareChatGroupDataAtCreate($user,false,false,null,null,$data);
        $validator = Validator::make($chatGroupData, ChatGroup::rules('save'), ChatGroup::messages('save'));
        if ($validator->fails()) {
            $errors = array_merge($errors,array_values($validator->errors()->toArray()));
        }
        // validate number of users 
        // $validator = Validator::make($chatGroupData, ChatGroup::rules('users-number'));
        // if ($validator->fails()) {
        //     $errors = array_merge($errors,[[['message' => Lang::get('validation.custom.chat_group_users_ids.min',[],'en'),
        //     'message_ar' => Lang::get('validation.custom.chat_group_users_ids.min',[],'ar')]]]);
        // }
        // validate chat group name 
        // $error = $this->chatGroupService->validateChatGroupName($chatGroupData,$user->organization_id);
        // if ($error) {
        //     $errors = array_merge($errors,[[['message' => Lang::get('validation.custom.chat_group_name_ar.unique',[],'en'),
        //     'message_ar' => Lang::get('validation.custom.chat_group_name_ar.unique',[],'ar')]]]);
        // }
        if(count($errors) > 0){
            return response()->json([ "error" => $errors], 400);
        }
        // validate users ids 
        if(isset($chatGroupData['chat_group_users_ids'])){
            $hasError = $this->userService->getUsersMembersError($user->organization_id,$chatGroupData['chat_group_users_ids']);
            if ($hasError) {
                return response()->json(['error' => 'Chat group users must be in the same organization', 'error_ar' => 'يجب أن يكون أعضاء المحادثة من نفس المنظمة '], 404);
            }
        }
        return $this->createChatGroup($user,$chatGroupData);
    }

    public function createChatGroupForMeeting(Request $request,int $meetingId){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        $errors = [];
        if ($meeting) {
            $chatGroupData = $this->chatGroupHelper->prepareChatGroupDataAtCreate($user,false,false,null,null,$data);
            $validator = Validator::make($chatGroupData, ChatGroup::rules('save'), ChatGroup::messages('save'));
            if ($validator->fails()) {
                $errors = array_merge($errors,array_values($validator->errors()->toArray()));
            }
            // validate number of users 
            $validator = Validator::make($chatGroupData, ChatGroup::rules('users-number'));
            if ($validator->fails()) {
                $errors = array_merge($errors,[[['message' => Lang::get('validation.custom.chat_group_users_ids.min',[],'en'),
                'message_ar' => Lang::get('validation.custom.chat_group_users_ids.min',[],'ar')]]]);
            }
            if(count($errors) > 0){
                return response()->json([ "error" => $errors], 400);
            }
            // validate users ids 
            $hasError = $this->meetingService->getMeetingUsersMembersError($meeting,$chatGroupData['chat_group_users_ids']);
            if ($hasError) {
                return response()->json(['error' => 'Meeting chat group users are not valid', 'error_ar' => 'أعضاء الاجتماع غير صحيح '], 404);
            }
            return $this->createChatGroup($user,$chatGroupData);
        }
        return response()->json(['error' => 'Meeting Not Found', 'error_ar' => 'الاجتماع غير موجود'], 404);
    }

    public function createChatGroupForCommittee(Request $request,int $committeeId) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $committee = $this->committeeService->getById($committeeId);
        $errors = [];
        if ($committee) {
            $chatGroupData = $this->chatGroupHelper->prepareChatGroupDataAtCreate($user,false,false,null,null,$data);
            $validator = Validator::make($chatGroupData, ChatGroup::rules('save'), ChatGroup::messages('save'));
            if ($validator->fails()) {
                $errors = array_merge($errors,array_values($validator->errors()->toArray()));
            }
            // validate number of users 
            $validator = Validator::make($chatGroupData, ChatGroup::rules('users-number'));
            if ($validator->fails()) {
                $errors = array_merge($errors,[[['message' => Lang::get('validation.custom.chat_group_users_ids.min',[],'en'),
                'message_ar' => Lang::get('validation.custom.chat_group_users_ids.min',[],'ar')]]]);
            }
            if(count($errors) > 0){
                return response()->json([ "error" => $errors], 400);
            }
            // validate users ids 
            $hasError = $this->committeeService->getCommitteeUsersMembersError($committee,$chatGroupData['chat_group_users_ids']);
            if ($hasError) {
                return response()->json(['error' => 'Commitee chat group users are not valid', 'error_ar' => 'أعضاء اللجنة غير صحيح '], 404);
            }
            return $this->createChatGroup($user,$chatGroupData);
        }
        return response()->json(['error' => 'Committee Not Found', 'error_ar' => 'اللجنة غير موجود موجودة'], 404);
    }

    private function createChatGroup($user,$chatGroupData) {
        // create chat group for meeting
        $chatGroup = $this->chatGroupService->create($chatGroupData);
        // create chat room at chat App
        if ($user->chat_user_id) {
            $response = $this->chatService->createChatGroupRoom($user,$chatGroup);
            if ($response['is_success']) {
                // update meeting chat room id
                $this->chatGroupService->updateChatRoomId($chatGroup->id,['chat_room_id' => $response['response']['chatRoom']['id']]);
                $response['response']['chatRoom']['chat_name_ar'] = $chatGroup->chat_group_name_ar;
                $response['response']['chatRoom']['chat_name_en'] = $chatGroup->chat_group_name_en;
                $response['response']['chatRoom']['chat_group_logo'] = $chatGroup->chat_group_logo_id? $chatGroup->chatGroupLogo : null;
                $response['response']['chatRoom']['organization_logo'] = $user->organization->logoImage;
                $response['response']['chatGroup'] = $this->chatGroupService->getChatGroupDetailsByIdAndOrganizationId($chatGroup->id,$user->organization_id,$user->id)->load('chatGroupLogo');
                return response()->json($response['response'], 200);
            }
            return response()->json($response['response'], $response['resopnse_code'] );
        }
    }

    public function update(Request $request, int $chatGroupId){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $chatGroup = $this->chatGroupService->getGeneralChatGroupById($chatGroupId);
        $errors = [];
        if($chatGroup) {
            // check if current user is chat group creator
            if($user->id == $chatGroup->creator_id) {
                // validate data
                $chatGroupData = $this->chatGroupHelper->prepareChatGroupDataAtUpdate($data,$chatGroup);
                $validator = Validator::make($chatGroupData, ChatGroup::rules('update',$chatGroupId), ChatGroup::messages('update'));
                if ($validator->fails()) {
                    $errors = array_merge($errors,array_values($validator->errors()->toArray()));
                }
                // if(isset($chatGroupData['chat_group_users_ids'])) {
                //     // validate number of users 
                //     $validator = Validator::make($chatGroupData, ChatGroup::rules('users-number'));
                //     if ($validator->fails()) {
                //         $errors = array_merge($errors,[[['message' => Lang::get('validation.custom.chat_group_users_ids.min',[],'en'),
                //         'message_ar' => Lang::get('validation.custom.chat_group_users_ids.min',[],'ar')]]]);
                //     }
                // }
                // validate chat group name 
                // $error = $this->chatGroupService->validateChatGroupName($chatGroupData,$user->organization_id,$chatGroupId);
                // if ($error) {
                //     $errors = array_merge($errors,[[['message' => Lang::get('validation.custom.chat_group_name_ar.unique',[],'en'),
                //     'message_ar' => Lang::get('validation.custom.chat_group_name_ar.unique',[],'ar')]]]);
                // }
                if(count($errors) > 0){
                    return response()->json([ "error" => $errors], 400);
                }
                if(isset($chatGroupData['chat_group_users_ids'])){
                    // validate creator user in users ids
                    $userExist = $this->chatGroupHelper->checkCreatorUserAtUsersIds($user->id,$chatGroupData['chat_group_users_ids']);
                    if(!$userExist){
                        return response()->json(['error' => "You can't delete yourself from chat users", 'error_ar' => 'لايمكن حذفك من أعضاء المحادثة '], 404);
                    }
                    // validate users ids 
                    $hasError = $this->userService->getUsersMembersError($user->organization_id,$chatGroupData['chat_group_users_ids']);
                    if ($hasError) {
                        return response()->json(['error' => 'Chat group users must be in the same organization', 'error_ar' => 'يجب أن يكون أعضاء المحادثة من نفس المنظمة '], 404);
                    }
                }
                $this->updateChatGroup($chatGroupId,$user,$chatGroupData);
                return response()->json(['message' => 'Chat group updated successfully', 'message_ar' => 'تم تعديل المحادثة بنجاح'], 200);
            } else {
                return response()->json(['error' => "You don't have access", 'error_ar' => 'لا يمكن تعديل هذه المحادثة'], 404);
            }
        }
        return response()->json(['error' => 'Chat group Not Found', 'error_ar' => 'المحادثة الجماعية غير موجود موجوده'], 404);
    }

    public function updateChatGroupForMeeting(Request $request,int $meetingId, int $chatGroupId){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        $errors = [];
        $chatGroup = $this->chatGroupService->getChatGroupByIdAndMeetingId($chatGroupId,$meetingId);
        if($chatGroup) {
            // check if current user is chat group creator
            if($user->id == $chatGroup->creator_id) {
                // validate data
                $chatGroupData = $this->chatGroupHelper->prepareChatGroupDataAtUpdate($data,$chatGroup);
                $validator = Validator::make($chatGroupData, ChatGroup::rules('update',$chatGroupId), ChatGroup::messages('update'));
                if ($validator->fails()) {
                    $errors = array_merge($errors,array_values($validator->errors()->toArray()));
                }
                if(isset($chatGroupData['chat_group_users_ids'])) {
                    // validate number of users 
                    $validator = Validator::make($chatGroupData, ChatGroup::rules('users-number'));
                    if ($validator->fails()) {
                        $errors = array_merge($errors,[[['message' => Lang::get('validation.custom.chat_group_users_ids.min',[],'en'),
                        'message_ar' => Lang::get('validation.custom.chat_group_users_ids.min',[],'ar')]]]);
                    }
                }
                if(count($errors) > 0){
                    return response()->json([ "error" => $errors], 400);
                }
                if(isset($chatGroupData['chat_group_users_ids'])){
                    // validate creator user in users ids
                    $userExist = $this->chatGroupHelper->checkCreatorUserAtUsersIds($user->id,$chatGroupData['chat_group_users_ids']);
                    if(!$userExist){
                        return response()->json(['error' => "You can't delete yourself from chat users", 'error_ar' => 'لايمكن حذفك من أعضاء المحادثة '], 404);
                    }
                    // validate users ids 
                    $hasError = $this->meetingService->getMeetingUsersMembersError($meeting,$chatGroupData['chat_group_users_ids']);
                    if ($hasError) {
                        return response()->json(['error' => 'Meeting chat group users are not valid', 'error_ar' => 'أعضاء الاجتماع غير صحيح '], 404);
                    }
                }
                $this->updateChatGroup($chatGroupId,$user,$chatGroupData);
                return response()->json(['message' => 'Meeting chat group updated successfully', 'message_ar' => 'تم تعديل المحادثة بنجاح'], 200);
            } else {
                return response()->json(['error' => "You don't have access", 'error_ar' => 'لا يمكن تعديل هذه المحادثة'], 404);
            }
        }
        return response()->json(['error' => 'Chat group Not Found', 'error_ar' => 'المحادثة الجماعية غير موجود موجوده'], 404);
    }

    public function updateChatGroupForCommittee(Request $request,int $committeeId, int $chatGroupId){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $committee = $this->committeeService->getById($committeeId);
        $errors = [];
        $chatGroup = $this->chatGroupService->getChatGroupByIdAndCommitteeId($chatGroupId,$committeeId);
        if($chatGroup) {
            // check if current user is chat group creator
            if($user->id == $chatGroup->creator_id) {
                // validate data
                $chatGroupData = $this->chatGroupHelper->prepareChatGroupDataAtUpdate($data,$chatGroup);
                $validator = Validator::make($chatGroupData, ChatGroup::rules('update',$chatGroupId), ChatGroup::messages('update'));
                if ($validator->fails()) {
                    $errors = array_merge($errors,array_values($validator->errors()->toArray()));
                }
                if(isset($chatGroupData['chat_group_users_ids'])) {
                    // validate number of users 
                    $validator = Validator::make($chatGroupData, ChatGroup::rules('users-number'));
                    if ($validator->fails()) {
                        $errors = array_merge($errors,[[['message' => Lang::get('validation.custom.chat_group_users_ids.min',[],'en'),
                        'message_ar' => Lang::get('validation.custom.chat_group_users_ids.min',[],'ar')]]]);
                    }
                }
                if(count($errors) > 0){
                    return response()->json([ "error" => $errors], 400);
                }
                if(isset($chatGroupData['chat_group_users_ids'])){
                    // validate creator user in users ids
                    $userExist = $this->chatGroupHelper->checkCreatorUserAtUsersIds($user->id,$chatGroupData['chat_group_users_ids']);
                    if(!$userExist){
                        return response()->json(['error' => "You can't delete yourself from chat users", 'error_ar' => 'لايمكن حذفك من أعضاء المحادثة '], 404);
                    }
                    // validate users ids
                    $hasError = $this->committeeService->getCommitteeUsersMembersError($committee,$chatGroupData['chat_group_users_ids']);
                    if ($hasError) {
                        return response()->json(['error' => 'Commitee chat group users are not valid', 'error_ar' => 'أعضاء اللجنة غير صحيح '], 404);
                    }
                }
                $this->updateChatGroup($chatGroupId,$user,$chatGroupData);
                return response()->json(['message' => 'Commitee chat group updated successfully', 'message_ar' => 'تم تعديل المحادثة بنجاح'], 200);
            } else {
                return response()->json(['error' => "You don't have access", 'error_ar' => 'لا يمكن تعديل هذه المحادثة'], 404);
            }
        }
        return response()->json(['error' => 'Chat group Not Found', 'error_ar' => 'المحادثة الجماعية غير موجود موجوده'], 404);
    }

    private function updateChatGroup($chatGroupId,$user,$chatGroupData){
        // update chat group
        $chatGroup = $this->chatGroupService->getById($chatGroupId);
        $oldChatUsers = array_column($chatGroup->memberUsers->toArray(),'id');
        $this->chatGroupService->update($chatGroupId,$chatGroupData);
        $chatGroup = $this->chatGroupService->getById($chatGroupId);
        // update chat room at chat App
        if ($user->chat_user_id && $chatGroup->chat_room_id) {
            $this->chatService->updateChatGroupRoom($user,$chatGroup);
        }
        if(isset($chatGroupData['chat_group_users_ids'])){
            // fire event
            $deletedUsersIds = array_diff($oldChatUsers,$chatGroupData['chat_group_users_ids']);
            $deletedUsers = [];
            foreach ($deletedUsersIds as $key => $deletedUserId) {
                $deletedUsers[] = ['id' => $deletedUserId];
            }
            $this->chatService->sendUpdateChatUserNotification($user,$chatGroup,$deletedUsers);
        }
    }

    public function createIndividualChat(Request $request){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $chatGroupData = $this->chatGroupHelper->prepareIndividualChatDataAtCreate($user,$data);
        $validator = Validator::make($chatGroupData, ChatGroup::rules('save'), ChatGroup::messages('save-individual-chat'));
        if ($validator->fails()) {
            return response()->json([ "error" => array_values($validator->errors()->toArray())], 400);
        }
        // validate individual chat users are diffrent
        $isdiffrentUsers = $this->chatGroupHelper->checkIndividualChatUsers($chatGroupData['chat_group_users_ids']);
        if(!$isdiffrentUsers){
            return response()->json(['error' => "You can't create chat with yourself", 'error_ar' => 'لايمكن إضافه محادثة مع نفس الشخص'], 404);
        }
        // validate individual chat users in the same organization 
        $hasError = $this->userService->getUsersMembersError($user->organization_id,$chatGroupData['chat_group_users_ids']);
        if ($hasError) {
            return response()->json(['error' => 'Chat group users must be in the same organization', 'error_ar' => 'يجب أن يكون أعضاء المحادثة من نفس المنظمة '], 404);
        }
        return $this->createIndividualChatRoom($user, $chatGroupData,$data['member_user_id']);
    }

    private function createIndividualChatRoom($user, $chatGroupData,$memberUserId){
        $chatGroup = $this->chatGroupService->getIndividualChatIfExist($memberUserId,$user->id);
        if(!$chatGroup){
            // create chat group for meeting
            $chatGroup = $this->chatGroupService->create($chatGroupData);
        }
        // create chat room at chat App
        if ($user->chat_user_id) {
            $response = $this->chatService->createChatGroupRoom($user,$chatGroup);
            if ($response['is_success']) {
                // update meeting chat room id
                $this->chatGroupService->updateChatRoomId($chatGroup->id,['chat_room_id' => $response['response']['chatRoom']['id']]);
                $individualChatUser = $this->chatGroupUserService->getIndividualChatUser($chatGroup->id,$user->id);
                $response['response']['chatRoom']['chat_name_ar'] = $individualChatUser->user->name_ar;
                $response['response']['chatRoom']['chat_name_en'] = $individualChatUser->user->name;
                $response['response']['chatRoom']['chat_group_logo'] = $individualChatUser->user->profile_image_id? $individualChatUser->user->image : null;
                $response['response']['chatGroup'] = $this->chatGroupService->getChatGroupDetailsById($chatGroup->id,$user->organization_id,$user->id)->load('chatGroupLogo');
                return response()->json($response['response'], 200);
            }
            return response()->json($response['response'], $response['resopnse_code'] );
        }
    }

    public function updateIndividualChat(Request $request, int $chatGroupId){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $chatGroup = $this->chatGroupService->getGeneralChatGroupById($chatGroupId);
        if($chatGroup) {
            // check if current user is chat group creator
            if($user->id == $chatGroup->creator_id) {
                $chatGroupData = $this->chatGroupHelper->prepareIndividualChatDataAtCreate($user,$data);
                $validator = Validator::make($chatGroupData, ChatGroup::rules('save'), ChatGroup::messages('save-individual-chat'));
                if ($validator->fails()) {
                    return response()->json([ "error" => array_values($validator->errors()->toArray())], 400);
                }
                // validate individual chat users are diffrent
                $isdiffrentUsers = $this->chatGroupHelper->checkIndividualChatUsers($chatGroupData['chat_group_users_ids']);
                if(!$isdiffrentUsers){
                    return response()->json(['error' => "You can't create chat with yourself", 'error_ar' => 'لايمكن إضافه محادثة مع نفس الشخص'], 404);
                }
                // validate individual chat users in the same organization 
                $hasError = $this->userService->getUsersMembersError($user->organization_id,$chatGroupData['chat_group_users_ids']);
                if ($hasError) {
                    return response()->json(['error' => 'Chat group users must be in the same organization', 'error_ar' => 'يجب أن يكون أعضاء المحادثة من نفس المنظمة '], 404);
                }
                // update individual chat
                $this->updateChatGroup($chatGroupId,$user,$chatGroupData);
                return response()->json(['message' => 'Chat group updated successfully', 'message_ar' => 'تم تعديل المحادثة بنجاح'], 200);
            } else {
                return response()->json(['error' => "You don't have access", 'error_ar' => 'لا يمكن تعديل هذه المحادثة'], 404);
            }
        }
        return response()->json(['error' => 'Chat group Not Found', 'error_ar' => 'المحادثة الجماعية غير موجود موجوده'], 404);
    }

    public function getPagedList(Request $request){
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if(!$user){
            return response()->json(['error' => 'Don\'t have access!'],400);
        }
        return response()->json($this->chatGroupService->getPagedList($filter,$user->organization_id,$user->id,true,null),200);
    }

    public function getChatGroupsPagedList(Request $request){
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if(!$user){
            return response()->json(['error' => 'Don\'t have access!'],400);
        }
        return response()->json($this->chatGroupService->getPagedList($filter,$user->organization_id,$user->id,false,true),200);
    }

    public function getIndividualsChatPagedList(Request $request){
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if(!$user){
            return response()->json(['error' => 'Don\'t have access!'],400);
        }
        return response()->json($this->chatGroupService->getPagedList($filter,$user->organization_id,$user->id,false,false),200);
    }

    public function getChatHistory(Request $request, int $chatGroupId){
        $filter = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $chatGroup = $this->chatGroupService->getChatGroupDetailsById($chatGroupId,$user->id);
        if ($chatGroup && $chatGroup->chat_room_id) {
            $filter['SearchObject']['chat_room_id'] = $chatGroup->chat_room_id; 
            $response = ChatConnector::getMessagesHistory($filter);
            if ($response['is_success']) {
                $response['response']['Results'] = $this->chatHelper->prepareChatMessages($response['response']['Results'], $user);
                $response['response']['chat_name_ar'] = $chatGroup->chat_group_name_ar;
                $response['response']['chat_name_en'] = $chatGroup->chat_group_name_en;
                $response['response']['chat_group_logo_url'] = $chatGroup->is_group_chat? $chatGroup->chat_group_logo_url : ($chatGroup->memberUsers[0]['image']? $chatGroup->memberUsers[0]['image']['image_url'] : null );
                $response['response']['organization_logo_url'] = $chatGroup->organization->logoImage? $chatGroup->organization->logoImage->image_url : null;
                $response['response']['meeting_id'] = $chatGroup->meeting_id;
                $response['response']['committee_id'] = $chatGroup->committee_id;
                $response['response']['is_group_chat'] = $chatGroup->is_group_chat;
                $response['response']['can_edit_chat_group'] = $chatGroup->is_group_chat && !$chatGroup->committee_id && !$chatGroup->meeting_id && $user->id == $chatGroup->creator_id? true : false;
                $timeZoneDiff= $user->id == -1 ?  $user->meeting->timeZone->diff_hours : $user->organization->timeZone->diff_hours;
                $response['response']['created_at'] = Carbon::parse($chatGroup->created_at)->addHours($timeZoneDiff);

                return response()->json($response['response'], 200);
            } else {
                return response()->json($response['response'], $response['resopnse_code'] );
            }
        }
        return response()->json(['error' => 'Chat Not Found', 'error_ar' => 'المحادثة غير موجوده'], 404);
    }

    public function sendMessageInChat(Request $request, int $chatGroupId) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $chatGroup = $this->chatGroupService->getChatGroupDetailsById($chatGroupId,$user->id);
        $response = $this->chatService->sendMessageInChat($data['MessageText'], $user, $chatGroup);
        if($response['is_success']){
            $this->chatGroupService->UpdateChatMetaData($data['MessageText'],$chatGroupId,$user);
            // send notification
            $notificationData = $this->notificationHelper->prepareNotificationDataForChat($chatGroup,$user,config('chatGroupNotifications.sendMessage'),[]);
            $this->notificationService->sendNotification($notificationData);
            $dataForTemplate = [];
            $dataForTemplate["user_name_en"] = $user->name??$user->name_ar;
            $dataForTemplate["user_name_ar"] = $user->name_ar??$user->name;

            $chatGroupMembers=$chatGroup->memberUsers->toArray();
            
            foreach($chatGroupMembers as $chatGroupMember)
            {
                $this->emailHelper->sendNotificationChatMemberMail(
                    $chatGroupMember['email'],
                    $chatGroupMember['name_ar'],
                    $chatGroupMember['name'],
                    NotificationHelper::getNotificationData('notification.NotificationChatMemberMessageAr', $dataForTemplate),
                    NotificationHelper::getNotificationData('notification.NotificationChatMemberMessageEn', $dataForTemplate),
                    $chatGroupMember['language_id']
                );
            }

            return response()->json(['message' => 'Message send successfully', 'message_ar' => 'تم إرسال الرسالة بنجاح'], 200 );
        }
        return response()->json($response['response'], isset($response['resopnse_code'])? $response['resopnse_code'] : 500 );
    }

    public function addUsersToChatGroup(Request $request, int $chatGroupId){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $chatGroup = $this->chatGroupService->getById($chatGroupId);
        if($chatGroup) {
            // check if current user is chat group creator
            if($user->id == $chatGroup->creator_id) {
                // validate data
                $chatGroupData = $this->chatGroupHelper->prepareChatGroupDataAtUpdate($data,$chatGroup);
                $validator = Validator::make($chatGroupData, ChatGroup::rules('add-users',$chatGroupId), ChatGroup::messages('add-users'));
                if ($validator->fails()) {
                    return response()->json([ "error" => array_values($validator->errors()->toArray())], 400);
                }
                // validate users ids 
                $hasError = $this->userService->getUsersMembersError($user->organization_id,$chatGroupData['chat_group_users_ids']);
                if ($hasError) {
                    return response()->json(['error' => 'Chat group users must be in the same organization', 'error_ar' => 'يجب أن يكون أعضاء المحادثة من نفس المنظمة '], 404);
                }
                // validate users not exist into chat group
                $usersExist = $this->chatGroupService->checkUsersIfExistIntoChatError($chatGroup,$chatGroupData['chat_group_users_ids']);
                if ($usersExist) {
                    return response()->json(['error' => 'Chat group users already exist', 'error_ar' => '   أعضاء المحادثة موجوديون بالفعل'], 400);
                }
                // update chat group
                $this->chatGroupService->addUsersTochatGroup($chatGroupId,$chatGroupData);
                $chatGroup = $this->chatGroupService->getById($chatGroupId);
                // update chat room at chat App
                if ($user->chat_user_id && $chatGroup->chat_room_id) {
                    $this->chatService->updateChatGroupRoom($user,$chatGroup);
                }
                return response()->json(['message' => 'Users added successfully', 'message_ar' => 'تم إضافه المستخدمين بنجاح'], 200 );
            } else {
                return response()->json(['error' => "You don't have access", 'error_ar' => 'لا يمكن تعديل هذه المحادثة'], 404);
            }
        }
        return response()->json(['error' => 'Chat group Not Found', 'error_ar' => 'المحادثة الجماعية غير موجود موجوده'], 404);
    }

    public function sendAttachmentInChat(Request $request, int $chatGroupId){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $chatGroup = $this->chatGroupService->getById($chatGroupId);

        $response = $this->chatService->sendAttachmentInChat($data['attachment'],$user,$chatGroup);
        if($response['is_success']){
            $this->chatGroupService->UpdateChatMetaData($data['attachment']['attachemnt_name'],$chatGroupId,$user);
            return response()->json(['message' => 'Attachment send successfully', 'message_ar' => 'تم إرسال الملف بنجاح'], 200 );
        }
        return response()->json($response['response'], $response['resopnse_code'] );
    }

    public function getChatAttachments(Request $request, int $chatGroupId){
        $filter = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $chatGroup = $this->chatGroupService->getChatGroupDetailsByIdAndOrganizationId($chatGroupId,$user->organization_id,$user->id);
        if ($chatGroup && $chatGroup->chat_room_id) {
            $response = ChatConnector::getChatRoomAttachments($chatGroup->chat_room_id,$filter);
            if ($response['is_success']) {
                return response()->json($response['response'], 200);
            } else {
                return response()->json($response['response'], $response['resopnse_code'] );
            }
        }
        return response()->json(['error' => 'Chat Not Found', 'error_ar' => 'المحادثة غير موجوده'], 404);
    }

    public function deleteChatGroupUser(int $chatGroupId,int $userId){
        $user = $this->securityHelper->getCurrentUser();
        $chatGroup = $this->chatGroupService->getById($chatGroupId);
        $userData = $this->userService->getById($userId);
        if($chatGroup) {
            $chatGroupUser = $this->chatGroupUserService->getChatGroupUserByUserIdAndChatGroupId($chatGroupId, $userId);
            if($chatGroupUser) {
                if($chatGroup->creator_id == $user->id && $userId !== $user->id && !$chatGroup->meeting_id && !$chatGroup->committee_id && $userData->chat_user_id){ //&& $chatGroup->memberUsers->count() > 2
                    $response = ChatConnector::deleteUserAtChatRoom($chatGroup->chat_room_id,$userData->chat_user_id);
                    if ($response['is_success']) {
                        $this->chatGroupUserService->delete($chatGroupUser->id);
                        // fire event 
                        $chatGroup = $this->chatGroupService->getById($chatGroupId);
                        $this->chatService->sendUpdateChatUserNotification($user,$chatGroup,array(['id' => $userData->id]));
                        return response()->json(['message' => Lang::get('translation.chat-group-users.delete.success',[],'en'), 'message_ar' => Lang::get('translation.chat-group-users.delete.success',[],'ar')], 200);                    
                    }else {
                        return response()->json($response['response'], $response['resopnse_code'] );
                    }       
                } else {
                    return response()->json(['error' => Lang::get('translation.chat-group-users.delete.error',[],'en'), 'error_ar' => Lang::get('translation.chat-group-users.delete.error',[],'ar')], 400);
                }
            } else {
                return response()->json(['error' => Lang::get('translation.chat-group-users.not-fond',[],'en'), 'error_ar' => Lang::get('translation.chat-group-users.not-fond',[],'ar')], 400);
            }
        } else {
            return response()->json(['error' => Lang::get('translation.chat-group.not-fond',[],'en'), 'error_ar' => Lang::get('translation.chat-group.not-fond',[],'ar')], 400);
        }
    }
}