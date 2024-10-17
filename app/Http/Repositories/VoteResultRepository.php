<?php

namespace Repositories;

class VoteResultRepository extends BaseRepository
{

    public function model()
    {
        return 'Models\VoteResult';
    }

    public function checkVoteBefore($user, $voteId)
    {
        if ($user->id != -1) {
            return $this->model->selectRaw('*')
                //->where('meeting_id', $meetingId)
                ->where('user_id', $user->id)
                ->where('vote_id', $voteId)
            ->first();
        } else {
            return $this->model->selectRaw('*')
                //->where('meeting_id', $meetingId)
                ->where('meeting_guest_id', $user->meeting_guest_id)
                ->where('vote_id', $voteId)
                ->first();
        }
    }

    public function countVoteResults($voteId)
    {
        return $this->model->selectRaw(" distinct (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_status_id = " . config('voteStatuses.yes')
            . " AND vote_results.vote_id = $voteId) as yes_votes,
        (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_status_id = " . config('voteStatuses.no')
            . " AND vote_results.vote_id =  $voteId) as no_votes,
        (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_status_id = " . config('voteStatuses.abstained')
            . " AND vote_results.vote_id =  $voteId) as abstained_votes
        ")
            ->where('vote_id', $voteId)
            ->get();

    }

    public function countVoteResultsWithWeight($voteId){
        return $this->model->selectRaw(" distinct (SELECT SUM(vote_results.decision_weight) FROM vote_results WHERE vote_results.vote_status_id = " . config('voteStatuses.yes')
            . " AND vote_results.vote_id = $voteId) as yes_votes,
        (SELECT SUM(vote_results.decision_weight) FROM vote_results WHERE vote_results.vote_status_id = " . config('voteStatuses.no')
            . " AND vote_results.vote_id =  $voteId) as no_votes,
        (SELECT SUM(vote_results.decision_weight) FROM vote_results WHERE vote_results.vote_status_id =  " . config('voteStatuses.abstained')
            . " AND vote_results.vote_id =  $voteId) as abstained_votes
        ")
            ->where('vote_id', $voteId)
            ->get();
    }

    public function voteResults($meetingId, $voteId)
    {
        return $this->model->selectRaw("users.*,meeting_guests.*, vote_statuses.*,images.*,organization_image.image_url as organization_image_url
            ,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
            user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
            nicknames.nickname_ar,nicknames.nickname_en")
            ->leftJoin('vote_statuses', 'vote_statuses.id', 'vote_results.vote_status_id')
            ->join('votes', 'votes.id', 'vote_results.vote_id')
            ->join('meeting_agendas', 'meeting_agendas.id', 'votes.agenda_id')
            ->leftJoin('meeting_guests', function ($join) use ($voteId) {
                $join->on('meeting_guests.id', '=', 'vote_results.meeting_guest_id')
                    ->on('meeting_guests.meeting_id', '=', 'votes.meeting_id')
                    ->where('votes.id', $voteId);
            })
            ->leftJoin('meeting_participants', function ($join) use ($voteId) {
                $join->on('meeting_participants.meeting_id', '=', 'votes.meeting_id')
            ->on('meeting_participants.user_id', '=', 'vote_results.user_id')
            ->where('vote_id', $voteId);
            })
            ->leftJoin('users', 'users.id', 'vote_results.user_id')
            ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
            ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
            ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id')
            ->leftJoin('organizations', 'organizations.id', 'users.organization_id')
            ->leftJoin('images', 'images.id', 'users.profile_image_id')
            ->leftJoin('images as organization_image', 'organization_image.id', 'organizations.logo_id')
            ->where('votes.id', $voteId)
            ->get();
    }

}
