<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Connectors\ChatConnector;
use Services\CommitteeService;
use Services\MeetingService;
use Log;

class CreateChatRoom implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $chatRoomData;
    private $meeting;
    private $committee;
    private $isCommitteeChat;
    private $isMeetingChat;
    private $token;

    public function __construct($token,$chatRoomData,$committee,$meeting,$isCommitteeChat,$isMeetingChat)
    {
        $this->chatRoomData = $chatRoomData;
        $this->committee = $committee;
        $this->meeting = $meeting;
        $this->isCommitteeChat = $isCommitteeChat;
        $this->isMeetingChat = $isMeetingChat;
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @param CommitteeService $committeeService
     * 
     * @return void
     */
    public function handle(CommitteeService $committeeService, MeetingService $meetingService)
    {
        try{
            $chatResponse = ChatConnector::createChatRoom($this->chatRoomData,$this->token);
            if ($chatResponse['is_success']) {
                if($this->isCommitteeChat) {
                    // update chat_room_id for committee
                    $committeeService->updateChatRoomId($this->committee->id, ['chat_room_id' => $chatResponse['response']['chatRoom']['id']]);
                }
                if($this->isMeetingChat) {
                    // update chat_room_id for meeting
                    $meetingService->updateChatRoomId($this->meeting->id,['chat_room_id' => $chatResponse['response']['chatRoom']['id']]);
                }
                
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}