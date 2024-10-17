<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;
use Helpers\UploadHelper;

class HandleAttachments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
   
    private $attachments;
    public function __construct($attachments)
    {
        $this->attachments = $attachments;
    }

    /**
     * Execute the job.
     *
     * @param CustomerOrderService $customerOrderService
     * @return void
     */
    public function handle(UploadHelper $uploadHelper)
    {
        try{
            UploadHelper::convertAttachmentsToImages($this->attachments);
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}
