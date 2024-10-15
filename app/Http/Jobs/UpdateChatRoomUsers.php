<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Connectors\ChatConnector;
use Log;

class UpdateChatRoomUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $chatRoomData;
    private $chatRoomId;
    private $token;

    public function __construct($token,$chatRoomId,$chatRoomData)
    {
        $this->chatRoomData = $chatRoomData;
        $this->chatRoomId = $chatRoomId;
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @param void
     * 
     * @return void
     */
    public function handle()
    {
        try{
            $chatResponse = ChatConnector::updateChatRoomUsers($this->chatRoomId,$this->chatRoomData,$this->token);
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}