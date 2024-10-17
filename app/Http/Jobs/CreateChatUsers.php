<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Connectors\ChatConnector;
use Services\UserService;
use Log;

class CreateChatUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
   
    private $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    /**
     * Execute the job.
     *
     * @param UserService $userService
     * 
     * @return void
     */
    public function handle(UserService $userService)
    {
        try{
            foreach ($this->users as $key => $user) {
                // create user at chat app
                $userData = ['email' => $user->email, 'username' => $user->username,'role_id' => config('chatRoles.client'),'app_id' => config('chat.chatAppId')];
                $registerResponse = ChatConnector::register($userData);
                if ($registerResponse['is_success']) {
                    $chatUser = $registerResponse['response']['created'];
                    // update chat_user_id for user
                    $userService->update($user->id, ["chat_user_id" => $chatUser['id']]);
                }
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}