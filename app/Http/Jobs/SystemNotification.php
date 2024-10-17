<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Services\NotificationService;
use Helpers\NotificationHelper;
use Log;

class SystemNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $vote;
    private $user;
    private $notificationType;

   
    public function __construct($vote, $user, $notificationType)
    {
        $this->vote = $vote;
        $this->user = $user;
        $this->notificationType = $notificationType;
    }

    /**
     * Execute the job.
     *
     * @param NotificationService $notificationService
     * @return void
     */
    public function handle(NotificationService $notificationService, NotificationHelper $notificationHelper)
    {
        try{
            $notificationData = $notificationHelper->prepareNotificationDataForMeetingDecision($this->vote,$this->user,$this->notificationType,[]);
            $notificationService->sendNotification($notificationData);
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}
