<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Connectors\ChatConnector;
use Helpers\ChatHelper;
use Helpers\SecurityHelper;
use Services\UserService;
use Services\MeetingService;
use Services\CommitteeService;
use Services\ChatService;
use Services\ChatGroupService;

class ChatRoomController extends Controller
{

    private $chatHelper;
    private $securityHelper;
    private $userService;
    private $meetingService;
    private $committeeService;
    private $chatService;
    private $chatGroupService;

    public function __construct(ChatHelper $chatHelper,
                                SecurityHelper $securityHelper,
                                UserService $userService, MeetingService $meetingService,
                                CommitteeService $committeeService, ChatService $chatService,
                                ChatGroupService $chatGroupService)
    {
        $this->chatHelper = $chatHelper;
        $this->securityHelper = $securityHelper;
        $this->userService = $userService;
        $this->meetingService = $meetingService;
        $this->committeeService = $committeeService;
        $this->chatService = $chatService;
        $this->chatGroupService = $chatGroupService;
    }

    public function show($id) {
        

    }


    public function getPagedList(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        if ($user->chat_user_id) {
                $data['SearchObject'] = $this->chatHelper->prepareSearchObject($data['SearchObject'],$user);
                $response = ChatConnector::getChatRooms($data);
                if(is_array($response) && !$response['is_success']){
                    return response()->json($response['response'], $response['resopnse_code'] );
                }
                $response['response']['Results'] = $this->chatHelper->prepareChatRooms($response['response']['Results']);
 
                return response()->json($response['response'], 200);
        } else {
            return response()->json(['error' => "You don't have user at chat app", 'error_ar' => " ليس لديك حساب محادثة !"], 404);
        }     
    }

    public function getMeetingsChatsPagedList(Request $request) {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();

        return response()->json($this->meetingService->getMeetingsChatsPagedList($filter,$user->organization_id,$user->id),200);
    }

    public function getCommitteesChatsPagedList(Request $request) {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();

        return response()->json($this->committeeService->getCommitteesChatsPagedList($filter,$user->organization_id,$user->id),200);
    }

    public function getMeetingChatHistory(Request $request,$meetingId){
        $filter = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        if ($meeting && $meeting->chat_room_id) {
            $filter['SearchObject']['chat_room_id'] = $meeting->chat_room_id; 
            $response = ChatConnector::getMessagesHistory($filter);
            if ($response['is_success']) {
                $response['response']['Results'] = $this->chatHelper->prepareChatMessages($response['response']['Results'],$user);
                $response['response']['chat_name_ar'] = $meeting->meeting_title_ar;
                $response['response']['chat_name_en'] = $meeting->meeting_title_en;
                return response()->json($response['response'], 200);
            } else {
                return response()->json($response['response'], $response['resopnse_code'] );
            }
        }
        return response()->json(['error' => 'Meeting Not Found', 'error_ar' => 'الاجتماع غير موجود'], 404);
    }

    public function getCommitteeChatHistory(Request $request,$committeeId){
        $filter = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $committee = $this->committeeService->getById($committeeId);
        if ($committee && $committee->chat_room_id) {
            $filter['SearchObject']['chat_room_id'] = $committee->chat_room_id; 
            $response = ChatConnector::getMessagesHistory($filter);
            if ($response['is_success']) {
                $response['response']['Results'] = $this->chatHelper->prepareChatMessages($response['response']['Results'], $user);
                $response['response']['chat_name_ar'] = $committee->committee_name_ar;
                $response['response']['chat_name_en'] = $committee->committee_name_en;
                return response()->json($response['response'], 200);
            } else {
                return response()->json($response['response'], $response['resopnse_code'] );
            }
        }
        return response()->json(['error' => 'Committee Not Found', 'error_ar' => 'اللجنة غير موجوده'], 404);
    }

    public function createMeetingChat(Request $request,$meetingId){
        $response = [];
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        if ($meeting) {
            $chatGroup = $this->chatGroupService->getChatGroupByMeetingId($meeting->id);
            if(!$chatGroup) {
                // create chat room for meeting
                $response = $this->chatService->createMeetingChatRoom($user,$meeting);
                if ($response['is_success']) {
                    // update meeting chat room id
                    $this->meetingService->updateChatRoomId($meeting->id,['chat_room_id' => $response['response']['chatRoom']['id']]);
                    // create chat group for meeting if not exist
                    $chatGroup = $this->chatGroupService->createMeetingChatGroupIfNotExist($user,$meeting,$response['response']['chatRoom']['id']);
                    $response['response']['chatRoom']['chat_name_ar'] = $meeting->meeting_title_ar;
                    $response['response']['chatRoom']['chat_name_en'] = $meeting->meeting_title_en;
                    $response['response']['chatGroup'] = $this->chatGroupService->getChatGroupDetailsById($chatGroup->id,$user->organization_id,$user->id)->load('chatGroupLogo');
                    return response()->json($response['response'], 200);
                }
                return response()->json($response['response'], $response['resopnse_code'] );
            } else {
                $response = $this->chatService->getChatGroupRoom($user,$chatGroup);
                if ($response['is_success']) {
                    $response['response']['chatRoom']['chat_name_ar'] = $meeting->meeting_title_ar;
                    $response['response']['chatRoom']['chat_name_en'] = $meeting->meeting_title_en;
                    $response['response']['chatGroup'] = $chatGroup;
                    if($response['is_success']) {
                        return response()->json($response['response'], 200);
                    }
                }
                return response()->json($response['response'], $response['resopnse_code'] );
            }
        }
        return response()->json(['error' => 'Meeting Not Found', 'error_ar' => 'الاجتماع غير موجود'], 404);

    }

    public function createCommitteeChat(Request $request,$committeeId){
        $user = $this->securityHelper->getCurrentUser();
        $committee = $this->committeeService->getById($committeeId);
        if ($committee) {
            // create chat room for committee
            $response = $this->chatService->createCommitteeChatRoom($user,$committee);
            if ($response['is_success']) {
                // update committee chat room id
                $this->committeeService->updateChatRoomId($committee->id,['chat_room_id' => $response['response']['chatRoom']['id']]);
                // create chat group for committee if not exist
                $chatGroup = $this->chatGroupService->createCommitteeChatGroupIfNotExist($user,$committee,$response['response']['chatRoom']['id']);
                $response['response']['chatRoom']['chat_name_ar'] = $committee->committee_name_ar;
                $response['response']['chatRoom']['chat_name_en'] = $committee->committee_name_en;
                $response['response']['chatGroup'] = $this->chatGroupService->getChatGroupDetailsById($chatGroup->id,$user->organization_id,$user->id)->load('chatGroupLogo');
                return response()->json($response['response'], 200);
            }
            return response()->json($response['response'], $response['resopnse_code'] );
        }
        return response()->json(['error' => 'Committee Not Found', 'error_ar' => 'اللجنة غير موجوده'], 404);
    }

       public function getRooms(Request $request){

        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        $committeesList = $this->committeeService->getAllUserCommittees($filter,$user->organization_id,$user->id);
        $meetingsList = $this->meetingService->getUserMeetingsPagedList($filter,$user->organization_id,$user->id);
        $usersList = $this->userService->getOrganizationUsersList($filter, $user->organization_id,$user->id);
        return response()->json(['usersList' => $usersList,'committeesList' => $committeesList,'meetingsList' => $meetingsList],200);
        }

       public function sendMessageInMeeting(Request $request,$meetingId){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        
        $response = $this->chatService->sendMessageInMeeting($data['MessageText'],$user,$meeting);
        try{
            $this->meetingService->UpdateChatMetaData($data['MessageText'],$meetingId,$user);

        }catch (\Exception $e) {
            report($e);
            return response($e, 200);
        }

        return response()->json($response, 200 );
       }

        public function sendMessageInCommittee(Request $request,$committeeId){

        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $committee = $this->committeeService->getById($committeeId);
        
        $response = $this->chatService->sendMessageInCommittee($data['MessageText'],$user,$committee);
        try{
            $this->committeeService->UpdateChatMetaData($data['MessageText'],$committeeId,$user);
        }catch (\Exception $e) {
            report($e);
            return response($e, 200);
        }

        return response()->json($response, 200);
       }

    public function createUsersAtChatApp(){
        // get users don't have chat_user_id
        $users = $this->userService->getUsersWithoutChatUserId();
        $this->chatService->createChatUsersForRegisterUsersWithoutChatUserId($users);
        return response()->json('Chat users created successfully', 200);
    }

    public function sendAttachmentInMeeting(Request $request,$meetingId){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        $response = $this->chatService->sendAttachmentInMeeting($data['attachment'],$user,$meeting);
        if($response['is_success']){
            $this->meetingService->UpdateChatMetaData($data['attachment']['attachemnt_name'],$meetingId,$user);
            return response()->json(['message' => 'Attachment send successfully', 'message_ar' => 'تم إرسال الملف بنجاح'], 200 );
        }
        return response()->json($response['response'], $response['resopnse_code'] );
    }

    public function sendAttachmentInCommittee(Request $request,$committeeId){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $committee = $this->committeeService->getById($committeeId);
        
        $response = $this->chatService->sendAttachmentInCommittee($data['attachment'],$user,$committee);
        if($response['is_success']){
            $this->committeeService->UpdateChatMetaData($data['attachment']['attachemnt_name'],$committeeId,$user);
            return response()->json(['message' => 'Attachment send successfully', 'message_ar' => 'تم إرسال الملف بنجاح'], 200 );
        }
        return response()->json($response['response'], $response['resopnse_code'] );
    }

    public function getMeetingChatAttachments(Request $request,$meetingId){
        $filter = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $meeting = $this->meetingService->getById($meetingId);
        if ($meeting && $meeting->chat_room_id) {
            $response = ChatConnector::getChatRoomAttachments($meeting->chat_room_id,$filter);
            if ($response['is_success']) {
                return response()->json($response['response'], 200);
            } else {
                return response()->json($response['response'], $response['resopnse_code'] );
            }
        }
        return response()->json(['error' => 'Meeting Not Found', 'error_ar' => 'الإجتماع غير موجود'], 404);
    }

    public function getCommitteeChatAttachments(Request $request,$committeeId){
        $filter = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $committee = $this->committeeService->getById($committeeId);
        if ($committee && $committee->chat_room_id) {
            $response = ChatConnector::getChatRoomAttachments($committee->chat_room_id,$filter);
            if ($response['is_success']) {
                return response()->json($response['response'], 200);
            } else {
                return response()->json($response['response'], $response['resopnse_code'] );
            }
        }
        return response()->json(['error' => 'Committee Not Found', 'error_ar' => 'اللجنة غير موجوده'], 404);
    }
}