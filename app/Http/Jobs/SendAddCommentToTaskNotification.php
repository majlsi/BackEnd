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

class SendAddCommentToTaskNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $task;
    private $userIds;
    private $user;
    private $users;
   
    public function __construct($task,$userIds, $user, $users)
    {
        $this->task = $task;
        $this->userIds = $userIds;
        $this->user = $user;
        $this->users = $users;
    }

    /**
     * Execute the job.
     *
     * @param EventHelper $eventHelper
     * @return void
     */
    public function handle(EventHelper $eventHelper, NotificationHelper $notificationHelper, TaskHelper $taskHelper, EmailHelper $emailHelper,
        NotificationService $notificationService)
    {
        try{
            $notificationData = $notificationHelper->prepareAddCommentToTaskNotificationData($this->task, $this->userIds, $this->user);
            $eventHelper->fireEvent($notificationData, 'App\Events\TaskStatusChangedNotificationEvent');
            $notification = $notificationHelper->prepareNotificationDataForTask($this->task,$this->user,config('taskNotifications.addComment'),$this->userIds,[]);
            $notificationService->sendNotification($notification);
            $emailData = $taskHelper->prepareAddCommentToTaskEmailData($this->task, $this->user);
            foreach ($this->users as $organiser) {
                $emailHelper->sendAddCommentToTaskMail($organiser->email, $organiser->name_ar, $organiser->name, $emailData["serial_number"], $emailData["changed_by_name_en"], $emailData["changed_by_name_ar"], $emailData["task_id"], $organiser->language_id);
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}
