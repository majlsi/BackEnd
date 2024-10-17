<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Models\VoteResult;
use Models\CommitteeUser;

class UpdateValueOfDecisionWeightAtVoteResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $voteResults = VoteResult::get();
        foreach ($voteResults as $key => $voteResult) {
            $committeId = null;
            if($voteResult['vote']['meeting_id']){
                $committeId = $voteResult['vote']['meeting']['committee_id'];
            } else if($voteResult['vote']['committee_id']){
                $committeId = $voteResult['vote']['committee_id'];
            }
            if($committeId){
                $isHeadOfCommittee = CommitteeUser::where('committee_users.user_id',$voteResult['user_id'])
                    ->where('committee_users.committee_id',$committeId)->where('is_head',1)->first()? true : false;
                $data['decision_weight'] = $isHeadOfCommittee? config('decisionWeight.HeadDecisionWeight') : config('decisionWeight.participantDecisionWeight');
                VoteResult::where('id',$voteResult['id'])->update($data);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
