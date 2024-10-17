<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Helpers\UploadHelper;
use Services\DocumentService;
use Services\UserService;
use Log;

class HandleDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
   
    private $document;
    private $isCreate;

    public function __construct($document,$isCreate)
    {
        $this->document = $document;
        $this->isCreate = $isCreate;
    }

    /**
     * Execute the job.
     *
     * @param CustomerOrderService $customerOrderService
     * @return void
     */
    public function handle(UploadHelper $uploadHelper, DocumentService $documentService,UserService $userService)
    {
        try{
            UploadHelper::convertDocumentToImages($this->document);
            if(!$this->isCreate){
                $user = $userService->getById($this->document->added_by);
                $documentService->sendNotificationForChangeDocument($this->document,$user,config('documentNotification.editDocument'));
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}
