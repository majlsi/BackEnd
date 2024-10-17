<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;
use Services\UserService;
use Helpers\EmailHelper;

class SendEmailToAdminsForNewRegisteration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $created;
   
    public function __construct($created)
    {
        $this->created = $created;
    }

    /**
     * Execute the job.
     *
     * @param CustomerOrderService $customerOrderService
     * @return void
     */
    public function handle(UserService $userService, EmailHelper $emailHelper)
    {
        try{
            $admins = $userService->getAdminsUsers();
            $emailHelper->sendAdminsRegistrationMail($admins,$this->created); 
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}
