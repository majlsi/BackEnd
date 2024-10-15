<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Helpers\UploadHelper;
use Log;

class HandleApprovalDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
   
    private $approval;
    private $isCreate;

    public function __construct($approval,$isCreate)
    {
        $this->approval = $approval;
        $this->isCreate = $isCreate;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            UploadHelper::convertApprovalDocumentToImages($this->approval);
            if(!$this->isCreate){
               
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}
