<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Models\Vote;
use Models\CommitteeUser;

class MoveVoteUsersDataToVoteResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $voteStatus = DB::table('vote_statuses')->where('id',config('voteStatuses.notDecided'))->first();
        if(!$voteStatus){
            $voteStatus = ['id' => 4, 'vote_status_name_ar' => 'لم يتم القرار بعد', 'vote_status_name_en' => 'Not decided Yet','icon_class_name'=>'fa-minus','color_class_name'=>'btn-warning'];
            DB::table('vote_statuses')->insert([$voteStatus]); 
        }
        $voteUsers = DB::table('vote_users')->get();
        foreach ($voteUsers as $key => $voteUser) {
            $votResult = DB::table('vote_results')->where('vote_id', $voteUser->vote_id)
                ->where('user_id', $voteUser->user_id)->first();
            if(!$votResult){
                $vote = Vote::where('id',$voteUser->vote_id)->first();
                $isHeadOfCommittee = CommitteeUser::where('committee_users.committee_id',$vote['committee_id'])
                    ->where('user_id',$voteUser->user_id)->where('is_head',1)->first();
                $decisionWeight = $isHeadOfCommittee? config('decisionWeight.HeadDecisionWeight') : config('decisionWeight.participantDecisionWeight');
                $data = ['user_id' => $voteUser->user_id, 'vote_id' => $voteUser->vote_id,
                    'decision_weight' => $decisionWeight, 'vote_status_id' => config('voteStatuses.notDecided')];
                DB::table('vote_results')->insert([$data]); 
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

    }
}
