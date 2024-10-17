<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Connectors\ChatConnector;
use Services\MeetingGuestService;
use Log;

class RegiserGuestAtChatApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
   
    private $guest;


    public function __construct($guest)
    {
        $this->guest = $guest;

    }

    /**
     * Execute the job.
     *
     * @param MeetingGuestService $meetingGuestService
     * 
     * @return void
     */
    public function handle(MeetingGuestService $meetingGuestService)
    {
        try{
            // create user at chat app
            $userData = ['email' => $this->guest->email, 'username' => $this->guest->email ,'role_id' => config('chatRoles.client'),'app_id' => config('chat.chatAppId')];
            $registerResponse = ChatConnector::register($userData);
            if ($registerResponse['is_success']) {
                $chatUser = $registerResponse['response']['created'];
                // update chat_user_id for user
                $meetingGuestService->update($this->guest->id, ["chat_user_id" => $chatUser['id']]);
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}
