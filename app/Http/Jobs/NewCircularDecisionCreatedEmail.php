<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Helpers\EmailHelper;
use Log;

class NewCircularDecisionCreatedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $circularDecision;
    private $newVoters;

    public function __construct($circularDecision,$newVoters)
    {
        $this->circularDecision = $circularDecision;
        $this->newVoters = $newVoters;
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
            foreach ($this->circularDecision->voters as $key => $voter) {
                if($voter->id != $this->circularDecision->creator->id &&( $this->newVoters == null ||in_array($voter->id, $this->newVoters))){
                    $emailHelper->sendNewCircularDecisionCreatedMail($voter->email, $voter->name_ar, $voter->name, $this->circularDecision->vote_subject_ar, $this->circularDecision->vote_subject_en, $this->circularDecision->creator->name, $this->circularDecision->creator->name_ar,$this->circularDecision->id ,$voter->language_id);
                }
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}
