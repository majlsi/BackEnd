<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Helpers\EmailHelper;
use Services\UserService;
use Log;

class ShareFileEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $file;
    private $usersIds;
    public function __construct($file,$usersIds)
    {
        $this->file = $file;
        $this->usersIds = $usersIds;
    }

    /**
     * Execute the job.
     *
     * @param EmailHelper $emailHelper
     * @return void
     */
    public function handle(EmailHelper $emailHelper,UserService $userService)
    {
        try{
            $users = $userService->getUsersByIds($this->usersIds);
            foreach ($users as $key => $user) {
                $emailHelper->sendNewShareFileMail($user->email, $user->name_ar, $user->name, $this->file->file_name_ar, $this->file->file_name, $this->file->directoryOwner->name, $this->file->directoryOwner->name_ar ,$user->language_id);
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
    }
}
