<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Helpers\TaskHelper;
use Helpers\NotificationHelper;
use Helpers\EventHelper;
use Helpers\EmailHelper;
use Services\NotificationService;
use Log;

class SendNewTaskNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $task,$user;
   
    public function __construct($task,$user)
    {
        $this->task = $task;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @param CustomerOrderService $customerOrderService
     * @return void
     */
    public function handle(EventHelper $eventHelper, NotificationHelper $notificationHelper, TaskHelper $taskHelper, EmailHelper $emailHelper,
        NotificationService $notificationService)
    {
        try{
            $assignee = $this->task->assignee;

            $notificationData = $notificationHelper->prepareNewTaskNotificationData($this->task);
            $eventHelper->fireEvent($notificationData, 'App\Events\NewTaskNotificationEvent');
            $notification = $notificationHelper->prepareNotificationDataForTask($this->task,$this->user,config('taskNotifications.addTask'),[],[]);
            $notificationService->sendNotification($notification);
            $emailData = $taskHelper->prepareNewTaskEmailData($this->task);
            $emailHelper->sendNewTaskMail($assignee->email, $assignee->name_ar, $assignee->name, $emailData["serial_number"], $emailData["task_id"], $assignee->language_id);
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}
