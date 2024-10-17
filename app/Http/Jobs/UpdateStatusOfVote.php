<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Helpers\VoteHelper;
use Services\VoteResultService;
use Services\VoteService;
use Log;

class UpdateStatusOfVote implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
   
    private $voteId;

    public function __construct($voteId)
    {
        $this->voteId = $voteId;
    }

    /**
     * Execute the job.
     *
     * @param VoteHelper $voteHelper
     * @param VoteResultService $voteResultService
     * @param VoteService $voteService
     * 
     * @return void
     */
    public function handle(VoteHelper $voteHelper, VoteResultService $voteResultService,VoteService $voteService)
    {
        try{
            $voteCountResult = $voteResultService->countVoteResults($this->voteId);
            $data = $voteHelper->prepareVoteResultStatus($voteCountResult);
            $voteService->update($this->voteId,$data); 
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
    }
}