<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Helpers\StcHelper;
use Services\StcEventService;
use Log;

class WebhookCallBack implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
   
    private $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     *
     * @param UserService $userService
     * 
     * @return void
     */
    public function handle(StcHelper $stcHelper,StcEventService $stcEventService)
    {
        try{
            $this->event['status'] = 'success';

            $response = $stcHelper->sendEventCallBack($this->event);
            if(isset($response['is_success'])){
                $eventExist = $stcEventService->getEventByEventId($this->event['event_id']);
                if($eventExist){
                    $stcEventService->update($eventExist->id,['status' => $response['is_success']? 'success' : 'error']);
                }
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}