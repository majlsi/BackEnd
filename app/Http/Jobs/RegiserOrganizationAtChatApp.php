<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Connectors\ChatConnector;
use Services\UserService;
use Services\CommitteeService;
use Log;

class RegiserOrganizationAtChatApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
   
    private $user;
    private $organizationCommittees;

    public function __construct($user, $organizationCommittees)
    {
        $this->user = $user;
        $this->organizationCommittees = $organizationCommittees;
    }

    /**
     * Execute the job.
     *
     * @param UserService $userService
     * 
     * @return void
     */
    public function handle(UserService $userService, CommitteeService $committeeService)
    {
        try{
            // create user at chat app
            $userData = ['email' => $this->user->email, 'username' => $this->user->username,'role_id' => config('chatRoles.client'),'app_id' => config('chat.chatAppId')];
            $registerResponse = ChatConnector::register($userData);
            if ($registerResponse['is_success']) {
                $chatUser = $registerResponse['response']['created'];
                // update chat_user_id for user
                $userService->update($this->user->id, ["chat_user_id" => $chatUser['id']]);
                // create chat room for each committee
                // $loginData = ['username' => $this->user->username,'app_id' => config('chat.chatAppId')];
                // $loginResponse = ChatConnector::login($loginData);
                // if ($loginResponse['is_success']) {
                //     $token = $loginResponse['response']['token'];
                //     foreach ($this->organizationCommittees as $key => $organizationCommittee) {
                //         $chatRoomData = ['creator_user_name' => $this->user->name_ar,'app_id' => config('chat.chatAppId'),
                //         'users_ids'=> [],'chat_room_name' => config('chat.committeeChatName') . $organizationCommittee->id ];
                //         $chatResponse = ChatConnector::createChatRoom($chatRoomData,$token);
                //         if ($chatResponse['is_success']) {
                //             // update chat_room_id for committee
                //             $committeeService->updateChatRoomId($organizationCommittee->id, ['chat_room_id' => $chatResponse['response']['chatRoom']['id']]);
                //         }
                //     }
                // }
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}
