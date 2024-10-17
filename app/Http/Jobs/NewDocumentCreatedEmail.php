<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Helpers\EmailHelper;
use Log;

class NewDocumentCreatedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $document;
    private $documentUsers;

    public function __construct($document,$documentUsers)
    {
        $this->document = $document;
        $this->documentUsers = $documentUsers;
    }

    /**
     * Execute the job.
     *
     * @param EmailHelper $emailHelper
     * @return void
     */
    public function handle(EmailHelper $emailHelper)
    {
        try{
            foreach ($this->document->reviewres as $key => $reviewer) {
                if($reviewer->id != $this->document->creator->id && ($this->documentUsers == null||in_array($reviewer->id, $this->documentUsers))){
                    $emailHelper->sendNewDocumentCreatedMail($reviewer->email, $reviewer->name_ar, $reviewer->name, $this->document->document_subject_ar, $this->document->creator->name, $this->document->creator->name_ar,$this->document->id ,$reviewer->language_id);
                }
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}
