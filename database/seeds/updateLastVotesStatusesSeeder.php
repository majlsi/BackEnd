<?php

use Illuminate\Database\Seeder;
use Models\Vote;
use Models\VoteResult;

class updateLastVotesStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $votes = Vote::whereNull('vote_result_status_id')->get();
        foreach ($votes as $key => $vote) {
            $yesVotes = VoteResult::where('vote_results.vote_status_id',config('voteStatuses.yes'))->where('vote_results.vote_id',$vote->id)->count('vote_results.id');
            $noVotes = VoteResult::where('vote_results.vote_status_id',config('voteStatuses.no'))->where('vote_results.vote_id',$vote->id)->count('vote_results.id');

            if ($yesVotes == $noVotes) {
                $yesVotes = VoteResult::where('vote_results.vote_status_id',config('voteStatuses.yes'))->where('vote_results.vote_id',$vote->id)->sum('vote_results.decision_weight');
                $noVotes = VoteResult::where('vote_results.vote_status_id',config('voteStatuses.no'))->where('vote_results.vote_id',$vote->id)->sum('vote_results.decision_weight');
            
                $yesVotes = $yesVotes? $yesVotes : 0;
                $noVotes = $noVotes? $noVotes : 0;
            }
            $status = ($yesVotes == $noVotes)? config('voteResultStatuses.balanced') : (($yesVotes > $noVotes)? config('voteResultStatuses.approved') : config('voteResultStatuses.rejected'));
            DB::table('votes')->where('id', $vote['id'])
                ->update(['vote_result_status_id' => $status]);
        } 
    }
}        