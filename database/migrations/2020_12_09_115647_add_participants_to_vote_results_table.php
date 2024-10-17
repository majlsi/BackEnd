<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParticipantsToVoteResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $meetings = DB::table('meetings')->whereNull('related_meeting_id')->get();
        foreach ($meetings as $key => $meeting) {
            $votes = DB::table('votes')->where('meeting_id',$meeting->id)->get();
            foreach ($votes as $key => $vote) {
                $participants = DB::table('meeting_participants')->where('meeting_id',$meeting->id)->get()->toArray();
                $voteResults = DB::table('vote_results')->where('vote_id',$vote->id)->get()->toArray();
            
                $participantsIds = array_column($participants,'user_id');
                $voteResultsIds = array_column($voteResults,'user_id');

                $diffToBeAdded = array_values(array_diff($participantsIds, $voteResultsIds));
                $voteResults = [];
                foreach ($diffToBeAdded as $key => $value) {
                    $isHeadOfCommittee = DB::table('committee_users')->where('committee_users.user_id',$value)
                        ->where('committee_users.committee_id',$meeting->committee_id)
                        ->where('is_head',1)->first()? config('decisionWeight.HeadDecisionWeight') : config('decisionWeight.participantDecisionWeight');
            
                    $voteResults[$key]['user_id'] = $value;
                    $voteResults[$key]['vote_id'] = $vote->id;
                    $voteResults[$key]['vote_status_id'] = config('voteStatuses.notDecided');
                    $voteResults[$key]['decision_weight'] = $isHeadOfCommittee;
                }
                if(count($voteResults) > 0){
                    DB::table('vote_results')->insert($voteResults); 
                }
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
        Schema::table('vote_results', function (Blueprint $table) {
            //
        });
    }
}
