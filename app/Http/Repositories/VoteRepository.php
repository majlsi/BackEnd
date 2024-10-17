<?php

namespace Repositories;

use Lang;

class VoteRepository extends BaseRepository
{
    public function model()
    {
        return 'Models\Vote';
    }

    public function getMeetingVotes($meetingId)
    {
        return $this->model->with("voteParticipants")->selectRaw('votes.*')
            ->where('votes.meeting_id', $meetingId)
            ->get();
    }

    public function getMeetingVoteDetails($voteId)
    {
        return $this->model->selectRaw('votes.*,
        CASE
        WHEN votes.is_started = 1 THEN true
                
        WHEN votes.is_started = 0 THEN false 
        
        WHEN ( ( (DATE_ADD(votes.vote_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) > UTC_TIMESTAMP() )OR (NOT (DATE_ADD(votes.vote_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) > UTC_TIMESTAMP() OR DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) < UTC_TIMESTAMP())) )
             AND (votes.vote_type_id = '.config('voteTypes.forSpecificTime').' ) )
             OR (votes.vote_type_id = '.config('voteTypes.duringMeeting').' ) 
            THEN   true
            ELSE false
            END as is_voted_now')
        ->leftJoin('meetings', 'meetings.id', 'votes.meeting_id')

        ->leftJoin('time_zones', 'time_zones.id', 'meetings.time_zone_id')
            ->where('votes.id', $voteId)
            ->first();
    }

    public function endAllMeetingVotes($meetingId)
    {
        $this->model->selectRaw('votes.*')
        ->where('votes.meeting_id', $meetingId)
        ->update(['is_started' => 0]);
    }

    public function getPagedMeetingVotes($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $userId)
    {
        $query = $this->getAllMeetingVotesQuery($searchObj, $userId);

        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllMeetingVotesQuery($searchObj, $userId)
    {
        if (isset($searchObj->agenda_id)) {
            $this->model = $this->model->where('votes.agenda_id', $searchObj->agenda_id);
        }
        if (isset($searchObj->meeting_id)) {
            $this->model = $this->model->where('votes.meeting_id', $searchObj->meeting_id);
        }
        if (isset($searchObj->vote_type_id)) {
            $this->model = $this->model->where('votes.vote_type_id', $searchObj->vote_type_id);
        }
        if (isset($searchObj->agenda_name)) {
            $this->model = $this->model->whereRaw('(meeting_agendas.agenda_title_ar like ? OR meeting_agendas.agenda_title_en like ?)', ['%'.trim($searchObj->agenda_name).'%', '%'.trim($searchObj->agenda_name).'%']);
        }
        if (isset($searchObj->meeting_name)) {
            $this->model = $this->model->whereRaw('(meetings.meeting_title_ar like ? OR meetings.meeting_title_en like ?)', ['%'.trim($searchObj->meeting_name).'%', '%'.trim($searchObj->meeting_name).'%']);
        }
        if (isset($searchObj->vote_subject)) {
            $this->model = $this->model->whereRaw('(votes.vote_subject_ar like ? OR votes.vote_subject_en like ?)', ['%'.trim($searchObj->vote_subject).'%', '%'.trim($searchObj->vote_subject).'%']);
        }
        if (isset($searchObj->decision_type_id)) {
            $this->model = $this->model->where('votes.decision_type_id', $searchObj->decision_type_id);
        }
        if (isset($searchObj->is_secret)) {
            $this->model = $this->model->where('votes.is_secret', $searchObj->is_secret);
        }
        if (isset($searchObj->committee_id)) {
            $this->model = $this->model->where('votes.committee_id', $searchObj->committee_id);
        }
        if (isset($searchObj->vote_schedule_from) && isset($searchObj->vote_schedule_to)) {
            $this->model = $this->model->whereRaw(' NOT (date(votes.vote_schedule_from) > ? OR date(votes.vote_schedule_to) < ?)', [$searchObj->vote_schedule_to, $searchObj->vote_schedule_from]);
        } elseif (isset($searchObj->vote_schedule_from)) {
            $this->model = $this->model->whereDate('votes.vote_schedule_from', '>=', $searchObj->vote_schedule_from);
        } elseif (isset($searchObj->vote_schedule_to)) {
            $this->model = $this->model->whereDate('votes.vote_schedule_to', '<=', $searchObj->vote_schedule_to);
        }
        if (isset($searchObj->is_meeting_vote) && $searchObj->is_meeting_vote == true) {
            if (isset($searchObj->vote_result_status_id) && $searchObj->vote_result_status_id == config('voteStatuses.inprogress')) {
                $this->model = $this->model->whereRaw('((meetings.created_by != '.$userId.') AND (meeting_organisers.user_id != '.$userId.') AND (votes.is_secret = 1) AND ((meetings.meeting_status_id != '.config('meetingStatus.end').') 
                AND ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.notDecided').') != 0)))');
            } elseif (isset($searchObj->vote_result_status_id) && $searchObj->vote_result_status_id != config('voteStatuses.inprogress')) {
                $this->model = $this->model->where('votes.vote_result_status_id', $searchObj->vote_result_status_id)
                    ->whereRaw('((meetings.created_by = '.$userId.') OR (meeting_organisers.user_id = '.$userId.') OR (votes.is_secret = 0) OR (meetings.meeting_status_id = '.config('meetingStatus.end').') 
                    OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.notDecided').') = 0))');
            }
            $this->model = $this->model->selectRaw('DISTINCT votes.id,votes.agenda_id,votes.decision_due_date,votes.decision_type_id,votes.created_at,meeting_agendas.agenda_title_ar,meeting_agendas.agenda_title_en,
                meetings.meeting_title_ar, meetings.meeting_title_en,vote_types.vote_type_name_ar,votes.is_secret,votes.is_started,votes.meeting_id,votes.vote_type_id,votes.vote_subject_en,votes.vote_subject_ar,
                vote_types.vote_type_name_en,decision_types.decision_type_name_ar, decision_types.decision_type_name_en,
                (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.yes').') AS yes_votes,
                (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.no').') AS no_votes,
                (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.abstained').') AS abstained_votes,
                CASE WHEN ((meetings.created_by = '.$userId.') OR (meeting_organisers.user_id = '.$userId.') OR (votes.is_secret = 0) OR (meetings.meeting_status_id = '.config('meetingStatus.end').') 
                OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.notDecided').') = 0)) THEN votes.vote_result_status_id ELSE '.config('voteStatuses.inprogress').' END AS vote_result_status_id,
                CASE WHEN ((meetings.created_by = '.$userId.') OR (meeting_organisers.user_id = '.$userId.') OR (votes.is_secret = 0) OR (meetings.meeting_status_id = '.config('meetingStatus.end').') 
                OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.notDecided').') = 0)) THEN vote_result_statuses.vote_result_status_name_ar ELSE "'.Lang::get('translation.vote_result_status.in_progress', [], 'ar').'" END AS vote_result_status_name_ar,
                CASE WHEN ((meetings.created_by = '.$userId.') OR (meeting_organisers.user_id = '.$userId.') OR (meetings.meeting_status_id = '.config('meetingStatus.end').') 
                OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.notDecided').') = 0)) THEN vote_result_statuses.vote_result_status_name_en ELSE "'.Lang::get('translation.vote_result_status.in_progress', [], 'en').'" END AS vote_result_status_name_en')
                ->join('meetings', 'meetings.id', 'votes.meeting_id')
                ->join('meeting_agendas', 'meeting_agendas.id', 'votes.agenda_id')
                ->leftJoin('meeting_organisers', 'meeting_organisers.meeting_id', 'meetings.id')
                ->join('committees', 'committees.id', 'meetings.committee_id')
                ->join('committee_users', 'committee_users.committee_id', 'committees.id')
                ->leftJoin('vote_results AS voteResults', 'voteResults.vote_id', 'votes.id')
                ->whereNotIn('meetings.meeting_status_id', [config('meetingStatus.draft'), config('meetingStatus.cancel')])
                ->whereNotNull('votes.meeting_id')
                ->whereNull('meetings.related_meeting_id')
                ->whereRaw('(meeting_organisers.user_id = ? OR meetings.created_by = ? OR committees.committee_organiser_id = ? OR committees.committee_head_id = ? OR committee_users.user_id = ? OR voteResults.user_id = ?)', [$userId, $userId, $userId, $userId, $userId, $userId]);
        } elseif (isset($searchObj->is_meeting_vote) && $searchObj->is_meeting_vote == false) {
            if (isset($searchObj->is_my_circular_decision)) {
                $this->model = $this->model->where('votes.creator_id', $userId);
            } elseif (isset($searchObj->is_circular_decision_assign_to_me)) {
                $this->model = $this->model->where('voteResults.user_id', $userId)->where('votes.creator_id', '!=', $userId)
                    ->whereRaw('DATE_ADD(votes.vote_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()');
            } else {
                $this->model = $this->model->whereRaw('((voteResults.user_id = ? AND DATE_ADD(votes.vote_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) OR votes.creator_id = ?)', [$userId, $userId]);
            }
            if (isset($searchObj->vote_result_status_id) && $searchObj->vote_result_status_id == config('voteStatuses.inprogress')) {
                $this->model = $this->model->whereRaw('((votes.creator_id != '.$userId.') AND (votes.is_secret = 1) AND ((DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) >= UTC_TIMESTAMP()) 
                AND ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.notDecided').') != 0)))');
            } elseif (isset($searchObj->vote_result_status_id) && $searchObj->vote_result_status_id != config('voteStatuses.inprogress')) {
                $this->model = $this->model->where('votes.vote_result_status_id', $searchObj->vote_result_status_id)
                    ->whereRaw('((votes.creator_id = '.$userId.') OR (votes.is_secret = 0) OR (DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) 
                    OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.notDecided').') = 0))');
            }
            $this->model = $this->model->selectRaw('DISTINCT votes.id, votes.vote_type_id,votes.is_started,votes.vote_subject_ar,votes.vote_subject_en,votes.vote_schedule_from,votes.vote_schedule_to,
                votes.decision_type_id,votes.vote_description,votes.committee_id,votes.is_secret,votes.creator_id,committees.committee_name_en,committees.committee_name_ar,vote_types.vote_type_name_ar,
                vote_types.vote_type_name_en,decision_types.decision_type_name_ar, decision_types.decision_type_name_en,CASE WHEN votes.creator_id = '.$userId.' THEN 1 ELSE 0 END AS can_edit,
                CASE WHEN (DATE_ADD(votes.vote_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP() AND DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) >= UTC_TIMESTAMP()) THEN 1 ELSE 0 END AS can_vote,
                users.name AS creator_name,users.name_ar AS creator_name_ar,
                (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.yes').') AS yes_votes,
                (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.no').') AS no_votes,
                (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.abstained').') AS abstained_votes,
                CASE WHEN ((votes.creator_id = '.$userId.') OR (votes.is_secret = 0) OR (DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) 
                OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.notDecided').') = 0)) THEN votes.vote_result_status_id ELSE '.config('voteStatuses.inprogress').' END AS vote_result_status_id,
                CASE WHEN ((votes.creator_id = '.$userId.') OR (votes.is_secret = 0) OR (DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) 
                OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.notDecided').') = 0)) THEN vote_result_statuses.vote_result_status_name_ar ELSE "'.Lang::get('translation.vote_result_status.in_progress', [], 'ar').'" END AS vote_result_status_name_ar,
                CASE WHEN ((votes.creator_id = '.$userId.') OR (votes.is_secret = 0) OR (DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) 
                OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.notDecided').') = 0)) THEN vote_result_statuses.vote_result_status_name_en ELSE "'.Lang::get('translation.vote_result_status.in_progress', [], 'en').'" END AS vote_result_status_name_en')
                ->join('users', 'users.id', 'votes.creator_id')
                ->join('organizations', 'organizations.id', 'users.organization_id')
                ->join('time_zones', 'time_zones.id', 'organizations.time_zone_id')
                ->join('committees', 'committees.id', 'votes.committee_id')
                ->leftJoin('vote_results AS voteResults', 'voteResults.vote_id', 'votes.id')
                ->with(['attachments'])
                ->whereNull('votes.meeting_id');
        }

        $this->model = $this->model
            ->leftJoin('vote_types', 'vote_types.id', 'votes.vote_type_id')
            ->leftJoin('vote_result_statuses', 'vote_result_statuses.id', 'votes.vote_result_status_id')
            ->leftJoin('decision_types', 'decision_types.id', 'votes.decision_type_id');

        return $this->model;
    }

    public function getCountOfVotesThatUsedDecisionType($decisionTypeId)
    {
        return $this->model->where('decision_type_id', $decisionTypeId)
            ->count();
    }

    public function checkUserCanVote($decisionId)
    {
        return $this->model->selectRaw('CASE WHEN (DATE_ADD(votes.vote_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP() AND DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) >= UTC_TIMESTAMP()) THEN 1 ELSE 0 END AS can_vote')
            ->join('users', 'users.id', 'votes.creator_id')
            ->join('organizations', 'organizations.id', 'users.organization_id')
            ->join('time_zones', 'time_zones.id', 'organizations.time_zone_id')
            ->where('votes.id', $decisionId)
            ->first();
    }

    public function getCircularDecicion($id, $userId)
    {
        return $this->model->selectRaw('votes.id, votes.vote_type_id,votes.is_started,votes.vote_subject_ar,votes.vote_subject_en,votes.vote_schedule_from,votes.vote_schedule_to,votes.document_id,
                votes.decision_type_id,votes.vote_description,votes.committee_id,votes.is_secret,votes.creator_id,committees.committee_name_en,committees.committee_name_ar,vote_types.vote_type_name_ar,
                vote_types.vote_type_name_en,decision_types.decision_type_name_ar, decision_types.decision_type_name_en,CASE WHEN votes.creator_id = '.$userId.' THEN 1 ELSE 0 END AS can_edit,
                CASE WHEN (DATE_ADD(votes.vote_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP() AND DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) >= UTC_TIMESTAMP()) THEN 1 ELSE 0 END AS can_vote,
                users.name AS creator_name,users.name_ar AS creator_name_ar,
                (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.yes').') AS yes_votes,
                (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.no').') AS no_votes,
                (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.abstained').') AS abstained_votes,
                votes.creation_date,
                (SELECT vote_results.is_signed FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.user_id = '.$userId.') AS is_signed,
                (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id) AS votes_number,
                images.image_url AS creator_image_url,logos.image_url AS organization_image_url,
                CASE WHEN ((votes.creator_id = '.$userId.') OR (votes.is_secret = 0) OR (DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) 
                OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.notDecided').') = 0)) THEN votes.vote_result_status_id ELSE '.config('voteStatuses.inprogress').' END AS vote_result_status_id,
                CASE WHEN ((votes.creator_id = '.$userId.') OR (votes.is_secret = 0) OR (DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) 
                OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.notDecided').') = 0)) THEN vote_result_statuses.vote_result_status_name_ar ELSE "'.Lang::get('translation.vote_result_status.in_progress', [], 'ar').'" END AS vote_result_status_name_ar,
                CASE WHEN ((votes.creator_id = '.$userId.') OR (votes.is_secret = 0) OR (DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) 
                OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = '.config('voteStatuses.notDecided').') = 0)) THEN vote_result_statuses.vote_result_status_name_en ELSE "'.Lang::get('translation.vote_result_status.in_progress', [], 'en').'" END AS vote_result_status_name_en')
                ->join('users', 'users.id', 'votes.creator_id')
                ->leftJoin('images', 'images.id', 'users.profile_image_id')
                ->join('organizations', 'organizations.id', 'users.organization_id')
                ->leftJoin('images as logos', 'logos.id', 'organizations.logo_id')
                ->join('time_zones', 'time_zones.id', 'organizations.time_zone_id')
                ->join('committees', 'committees.id', 'votes.committee_id')
                ->leftJoin('vote_types', 'vote_types.id', 'votes.vote_type_id')
                ->leftJoin('decision_types', 'decision_types.id', 'votes.decision_type_id')
                ->leftJoin('vote_result_statuses', 'vote_result_statuses.id', 'votes.vote_result_status_id')
                ->with(['attachments', 'tasks', 'voters' => function ($query) use ($id) {
                    $query->selectRaw('DISTINCT users.id,users.name,users.name_ar,images.image_url,user_titles.user_title_name_ar,user_titles.user_title_name_en,
                        logos.image_url AS organization_image_url,vote_statuses.vote_status_name_ar,vote_statuses.vote_status_name_en,vote_statuses.id as vote_status_id')
                        ->join('organizations', 'organizations.id', 'users.organization_id')
                        ->leftJoin('images', 'images.id', 'users.profile_image_id')
                        ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
                        ->leftJoin('images as logos', 'logos.id', 'organizations.logo_id')
                        ->leftJoin('vote_results AS voteResults', function ($join) {
                            $join->on('voteResults.user_id', '=', 'users.id');
                        })
                        ->leftJoin('vote_statuses', 'vote_statuses.id', 'voteResults.vote_status_id')
                        ->where('voteResults.vote_id', $id);
                    // }
                }])
                ->whereNull('votes.meeting_id')
                ->where('votes.id', $id)->first();
    }

    public function getVoteDetails($voteId)
    {
        return $this->model->selectRaw('votes.*,vote_result_statuses.vote_result_status_name_ar,vote_result_statuses.vote_result_status_name_en,
            decision_types.decision_type_name_ar,decision_types.decision_type_name_en')
            ->join('vote_result_statuses', 'vote_result_statuses.id', 'votes.vote_result_status_id')
            ->join('decision_types', 'decision_types.id', 'votes.decision_type_id')
            ->where('votes.id', $voteId)
            ->first();
    }

    public function getStartedCircularDecisions()
    {
        return $this->model->selectRaw('votes.*')
            ->join('users', 'users.id', 'votes.creator_id')
            ->join('organizations', 'organizations.id', 'users.organization_id')
            ->join('time_zones', 'time_zones.id', 'organizations.time_zone_id')
            ->whereRaw('(DATE_ADD(votes.vote_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) = DATE_SUB(UTC_TIMESTAMP(), INTERVAL SECOND(UTC_TIMESTAMP()) SECOND))')
            ->whereNotNull('votes.committee_id')
            ->get();
    }

    public function getDecisionDataWithCanSendNotificationFlag($decisionId)
    {
        return $this->model->selectRaw('votes.*,
            CASE WHEN (DATE_ADD(votes.vote_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) THEN 1 ELSE 0 END AS can_send_notification')
            ->join('users', 'users.id', 'votes.creator_id')
            ->join('organizations', 'organizations.id', 'users.organization_id')
            ->join('time_zones', 'time_zones.id', 'organizations.time_zone_id')
            ->where('votes.id', $decisionId)
            ->first();
    }

    public function getMeetingDecisionsResultStatusStatisticsForUser($userId,$voteResultStatusId){
        $query = $this->model->selectRaw('distinct votes.*')
            ->join('meetings','meetings.id','votes.meeting_id')
            ->join('committees','committees.id','meetings.committee_id')
            ->join('committee_users','committee_users.committee_id','committees.id')
            ->leftJoin('meeting_organisers', 'meeting_organisers.meeting_id', 'meetings.id')
            ->leftJoin('vote_results AS voteResults', 'voteResults.vote_id', 'votes.id')
            ->whereNull('meetings.related_meeting_id')
            ->whereRaw('(meeting_organisers.user_id = ? OR meetings.created_by = ? OR committees.committee_organiser_id = ? OR committees.committee_head_id = ? OR committee_users.user_id = ? OR voteResults.user_id = ?)', array($userId,$userId,$userId,$userId,$userId,$userId))
            ->whereNotIn('meetings.meeting_status_id', [config("meetingStatus.draft"), config("meetingStatus.cancel")])
            ->whereNotIn('votes.vote_result_status_id', [config("voteResultStatuses.balanced")]);

        if($voteResultStatusId != config('voteResultStatuses.inprogress')){
            $query = $query->whereRaw('(votes.vote_result_status_id = '.$voteResultStatusId.' AND ((meetings.created_by = '.$userId.') OR (meeting_organisers.user_id = '.$userId.') OR (votes.is_secret = 0) OR (meetings.meeting_status_id = ' . config('meetingStatus.end') . ') OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = ' . config('voteResultStatuses.noVotesYet').') = 0)))');
        } else {
            $query = $query->whereRaw('(NOT((meetings.created_by = '.$userId.') OR (meeting_organisers.user_id = '.$userId.') OR (votes.is_secret = 0) OR (meetings.meeting_status_id = ' . config('meetingStatus.end') . ') OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = ' . config('voteResultStatuses.noVotesYet').') = 0)))');
        }
        return $query->get();
    }


    public function getMeetingDecisionsResultStatusStatisticsForOrganization($organization_id,$voteResultStatusId){
        $query = $this->model->selectRaw('distinct votes.*')
            ->join('meetings','meetings.id','votes.meeting_id')
            ->leftJoin('vote_results AS voteResults', 'voteResults.vote_id', 'votes.id')
            ->whereNull('meetings.related_meeting_id')
            ->whereRaw('(meetings.organization_id = ?)', array($organization_id))
            ->whereNotIn('meetings.meeting_status_id', [config("meetingStatus.draft"), config("meetingStatus.cancel") ])
            ->whereNotIn('votes.vote_result_status_id', [config("voteResultStatuses.balanced")]);

            $query = $query->whereRaw('votes.vote_result_status_id = '.$voteResultStatusId);
        
        return $query->get();
    }


    public function getMeetingDecisionsResultStatusStatisticsForCommittee($committee_id,$voteResultStatusId){
        $query = $this->model->selectRaw('distinct votes.*')
            ->join('meetings','meetings.id','votes.meeting_id')
            ->leftJoin('vote_results AS voteResults', 'voteResults.vote_id', 'votes.id')
            ->whereNull('meetings.related_meeting_id')
            ->whereRaw('(meetings.committee_id = ?)', array($committee_id))
            ->whereNotIn('meetings.meeting_status_id', [config("meetingStatus.draft"), config("meetingStatus.cancel")])
            ->whereNotIn('votes.vote_result_status_id', [config("voteResultStatuses.balanced")]);
            $query = $query->whereRaw('votes.vote_result_status_id = '.$voteResultStatusId);
        
        return $query->get();
    }

    public function getLimitOfMeetingDecisionsForUser($userId){
        return $this->model->selectRaw('DISTINCT votes.id,votes.decision_due_date,votes.decision_type_id,votes.created_at,
            votes.is_secret,votes.is_started,votes.meeting_id,votes.vote_type_id,votes.vote_subject_en,votes.vote_subject_ar,committees.committee_name_en,committees.committee_name_ar,
            CASE WHEN ((meetings.created_by = '.$userId.') OR (meeting_organisers.user_id = '.$userId.') OR (votes.is_secret = 0) OR (meetings.meeting_status_id = ' . config('meetingStatus.end') . ') 
                OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = ' . config('voteResultStatuses.noVotesYet').') = 0)) THEN votes.vote_result_status_id ELSE ' . config('voteStatuses.inprogress').' END AS vote_result_status_id,
                CASE WHEN ((meetings.created_by = '.$userId.') OR (meeting_organisers.user_id = '.$userId.') OR (votes.is_secret = 0) OR (meetings.meeting_status_id = ' . config('meetingStatus.end') . ') 
                OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = ' . config('voteResultStatuses.noVotesYet').') = 0)) THEN vote_result_statuses.vote_result_status_name_ar ELSE "' . Lang::get('translation.vote_result_status.in_progress',[],'ar').'" END AS vote_result_status_name_ar,
                CASE WHEN ((meetings.created_by = '.$userId.') OR (meeting_organisers.user_id = '.$userId.') OR (meetings.meeting_status_id = ' . config('meetingStatus.end') . ') 
                OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = ' . config('voteResultStatuses.noVotesYet').') = 0)) THEN vote_result_statuses.vote_result_status_name_en ELSE "' . Lang::get('translation.vote_result_status.in_progress',[],'en').'" END AS vote_result_status_name_en')
            ->join('meetings','meetings.id','votes.meeting_id')
            ->join('committees','committees.id','meetings.committee_id')
            ->join('committee_users','committee_users.committee_id','committees.id')
            ->leftJoin('meeting_organisers', 'meeting_organisers.meeting_id', 'meetings.id')
            ->leftJoin('vote_results AS voteResults', 'voteResults.vote_id', 'votes.id')
            ->leftJoin('vote_result_statuses','vote_result_statuses.id','votes.vote_result_status_id')
            ->whereNull('meetings.related_meeting_id')
            ->whereNotIn('meetings.meeting_status_id', [config("meetingStatus.draft"), config("meetingStatus.cancel")])
            ->whereNotIn('votes.vote_result_status_id', [config("voteResultStatuses.balanced")])
            ->whereRaw('(meeting_organisers.user_id = ? OR meetings.created_by = ? OR committees.committee_organiser_id = ? OR committees.committee_head_id = ? OR committee_users.user_id = ? OR voteResults.user_id = ?)', array($userId,$userId,$userId,$userId,$userId,$userId))
            ->limit(config('committeeDashboard.maxDecisionsNumberForMemberDashboard'))->orderBy('votes.id','desc')->get();
    }

    public function getLimitOfMeetingDecisionsForOrganization($organization_id){
        return $this->model->selectRaw('DISTINCT votes.id,votes.decision_due_date,votes.decision_type_id,votes.created_at,
            votes.is_secret,votes.is_started,votes.meeting_id,votes.vote_type_id,votes.vote_subject_en,votes.vote_subject_ar,committees.committee_name_en,committees.committee_name_ar,
             votes.vote_result_status_id , vote_result_statuses.vote_result_status_name_ar ,vote_result_statuses.vote_result_status_name_en ')
            ->join('meetings','meetings.id','votes.meeting_id')
            ->join('committees','committees.id','meetings.committee_id')
            ->leftJoin('vote_results AS voteResults', 'voteResults.vote_id', 'votes.id')
            ->leftJoin('vote_result_statuses','vote_result_statuses.id','votes.vote_result_status_id')
            ->whereNull('meetings.related_meeting_id')
            ->whereNotIn('meetings.meeting_status_id', [config("meetingStatus.draft"), config("meetingStatus.cancel")])
            ->whereNotIn('votes.vote_result_status_id', [config("voteResultStatuses.balanced")])
            ->whereRaw('(meetings.organization_id = ?)', array($organization_id))
            ->limit(config('committeeDashboard.maxDecisionsNumberForBoardDashboard'))->orderBy('votes.id','desc')->get();
    }

    public function getLimitOfMeetingDecisionsForCommitee($committee_id){
        return $this->model->selectRaw('DISTINCT votes.id,votes.decision_due_date,votes.decision_type_id,votes.created_at,
            votes.is_secret,votes.is_started,votes.meeting_id,votes.vote_type_id,votes.vote_subject_en,votes.vote_subject_ar,committees.committee_name_en,committees.committee_name_ar,
             votes.vote_result_status_id , vote_result_statuses.vote_result_status_name_ar ,vote_result_statuses.vote_result_status_name_en ')
            ->join('meetings','meetings.id','votes.meeting_id')
            ->join('committees','committees.id','meetings.committee_id')
            ->leftJoin('vote_results AS voteResults', 'voteResults.vote_id', 'votes.id')
            ->leftJoin('vote_result_statuses','vote_result_statuses.id','votes.vote_result_status_id')
            ->whereNull('meetings.related_meeting_id')
            ->whereNotIn('meetings.meeting_status_id', [config("meetingStatus.draft"), config("meetingStatus.cancel")])
            ->whereNotIn('votes.vote_result_status_id', [config("voteResultStatuses.balanced")])
            ->whereRaw('(meetings.committee_id = ?)', array($committee_id))
            ->limit(config('committeeDashboard.maxDecisionsNumberForCommitteeDashboard'))->orderBy('votes.id','desc')->get();
    }




    public function getCircularDecisionsResultStatusStatisticsForUser($userId,$voteResultStatusId){
        $query = $this->model->selectRaw('distinct votes.*')
            ->join('users','users.id','votes.creator_id')
            ->join('organizations','organizations.id','users.organization_id')
            ->join('time_zones','time_zones.id','organizations.time_zone_id')
            ->leftJoin('vote_results AS voteResults', 'voteResults.vote_id', 'votes.id')
            ->whereRaw('((voteResults.user_id = ? AND DATE_ADD(votes.vote_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) OR votes.creator_id = ?)', array($userId,$userId));
        if($voteResultStatusId != config('voteResultStatuses.inprogress')) {
            $query = $query->where('votes.vote_result_status_id',$voteResultStatusId)
                ->whereRaw('((votes.creator_id = '.$userId.') OR (votes.is_secret = 0) OR (DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) 
                OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = ' . config('voteResultStatuses.noVotesYet').') = 0))');
        } else {
            $query = $query->whereRaw('((votes.creator_id != '.$userId.') AND (votes.is_secret = 1) AND ((DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) >= UTC_TIMESTAMP()) 
                AND ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = ' . config('voteResultStatuses.noVotesYet').') != 0)))');
        }
        return $query->get();
    }


    public function getCircularDecisionsResultStatusStatisticsForOrganization($organization_id,$voteResultStatusId){
        $query = $this->model->selectRaw('distinct votes.*')
            ->join('users','users.id','votes.creator_id')
            ->join('organizations','organizations.id','users.organization_id')
            ->join('time_zones','time_zones.id','organizations.time_zone_id')
            ->leftJoin('vote_results AS voteResults', 'voteResults.vote_id', 'votes.id')
            ->whereRaw('users.organization_id = ?', array($organization_id));
            $query = $query->where('votes.vote_result_status_id',$voteResultStatusId);

        return $query->get();
    }


    public function getCircularDecisionsResultStatusStatisticsForCommittee($committee_id,$voteResultStatusId){
        $query = $this->model->selectRaw('distinct votes.*')
            ->leftJoin('vote_results AS voteResults', 'voteResults.vote_id', 'votes.id')
            ->whereRaw('votes.committee_id = ?', array($committee_id));
            $query = $query->where('votes.vote_result_status_id',$voteResultStatusId);

        return $query->get();
    }

    public function getLimitOfCircularDecisionsForUser($userId){
        return $this->model->selectRaw('DISTINCT votes.id, votes.vote_type_id,votes.is_started,votes.vote_subject_ar,votes.vote_subject_en,votes.vote_schedule_from,votes.vote_schedule_to,
            votes.decision_type_id,votes.vote_description,votes.committee_id,votes.is_secret,votes.creator_id,
            users.name AS creator_name,users.name_ar AS creator_name_ar,committees.committee_name_en,committees.committee_name_ar,
            CASE WHEN ((votes.creator_id = '.$userId.') OR (votes.is_secret = 0) OR (DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) 
            OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = ' . config('voteStatuses.notDecided').') = 0)) THEN votes.vote_result_status_id ELSE ' . config('voteStatuses.inprogress').' END AS vote_result_status_id,
            CASE WHEN ((votes.creator_id = '.$userId.') OR (votes.is_secret = 0) OR (DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) 
            OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = ' . config('voteStatuses.notDecided').') = 0)) THEN vote_result_statuses.vote_result_status_name_ar ELSE "' . Lang::get('translation.vote_result_status.in_progress',[],'ar').'" END AS vote_result_status_name_ar,
            CASE WHEN ((votes.creator_id = '.$userId.') OR (votes.is_secret = 0) OR (DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) 
            OR ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = ' . config('voteStatuses.notDecided').') = 0)) THEN vote_result_statuses.vote_result_status_name_en ELSE "' . Lang::get('translation.vote_result_status.in_progress',[],'en').'" END AS vote_result_status_name_en')
            ->join('users','users.id','votes.creator_id')
            ->join('committees','committees.id','votes.committee_id')
            ->join('organizations','organizations.id','users.organization_id')
            ->join('time_zones','time_zones.id','organizations.time_zone_id')
            ->leftJoin('vote_results AS voteResults', 'voteResults.vote_id', 'votes.id')
            ->leftJoin('vote_result_statuses','vote_result_statuses.id','votes.vote_result_status_id')
            ->whereRaw('((voteResults.user_id = ? AND DATE_ADD(votes.vote_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) OR votes.creator_id = ?)', array($userId,$userId))
            ->whereNull('votes.meeting_id')
            ->whereNotIn('votes.vote_result_status_id', [config("voteResultStatuses.balanced")])
            ->limit(config('committeeDashboard.maxDecisionsNumberForMemberDashboard'))->orderBy('votes.id','desc')->get();
    }

    public function getLimitOfCircularDecisionsForOrganization($organization_id){
        return $this->model->selectRaw('DISTINCT votes.id, votes.vote_type_id,votes.is_started,votes.vote_subject_ar,votes.vote_subject_en,votes.vote_schedule_from,votes.vote_schedule_to,
            votes.decision_type_id,votes.vote_description,votes.committee_id,votes.is_secret,votes.creator_id,
            users.name AS creator_name,users.name_ar AS creator_name_ar,committees.committee_name_en,committees.committee_name_ar,
            votes.vote_result_status_id ,
            vote_result_statuses.vote_result_status_name_ar ,
            vote_result_statuses.vote_result_status_name_en')
            ->join('users','users.id','votes.creator_id')
            ->join('committees','committees.id','votes.committee_id')
            ->leftJoin('vote_results AS voteResults', 'voteResults.vote_id', 'votes.id')
            ->leftJoin('vote_result_statuses','vote_result_statuses.id','votes.vote_result_status_id')
            ->whereRaw('(users.organization_id = ?)', array($organization_id))
            ->whereNull('votes.meeting_id')
            ->whereNotIn('votes.vote_result_status_id', [config("voteResultStatuses.balanced")])
            ->limit(config('committeeDashboard.maxDecisionsNumberForBoardDashboard'))->orderBy('votes.id','desc')->get();
    }

    public function getLimitOfCircularDecisionsForCommittee($committee_id){
        return $this->model->selectRaw('DISTINCT votes.id, votes.vote_type_id,votes.is_started,votes.vote_subject_ar,votes.vote_subject_en,votes.vote_schedule_from,votes.vote_schedule_to,
            votes.decision_type_id,votes.vote_description,votes.committee_id,votes.is_secret,votes.creator_id,
            users.name AS creator_name,users.name_ar AS creator_name_ar,committees.committee_name_en,committees.committee_name_ar,
            votes.vote_result_status_id ,
            vote_result_statuses.vote_result_status_name_ar ,
            vote_result_statuses.vote_result_status_name_en')
            ->join('users','users.id','votes.creator_id')
            ->join('committees','committees.id','votes.committee_id')
            ->leftJoin('vote_results AS voteResults', 'voteResults.vote_id', 'votes.id')
            ->leftJoin('vote_result_statuses','vote_result_statuses.id','votes.vote_result_status_id')
            ->whereNotIn('votes.vote_result_status_id', [config("voteResultStatuses.balanced")])
            ->whereRaw('(votes.committee_id = ?)', array($committee_id))
            ->whereNull('votes.meeting_id')
            ->limit(config('committeeDashboard.maxDecisionsNumberForCommitteeDashboard'))->orderBy('votes.id','desc')->get();
    }

    public function getCircularDecisionsHaveEndDateInThePast(){
        return $this->model->selectRaw('votes.*')
            ->join('users','users.id','votes.creator_id')
            ->join('organizations','organizations.id','users.organization_id')
            ->join('time_zones','time_zones.id','organizations.time_zone_id')
            ->where('organizations.enable_meeting_archiving',true)
            ->whereNull('votes.meeting_id')
            ->whereRaw('NOT (DATE_ADD(votes.vote_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP() AND DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) >= UTC_TIMESTAMP())')
            ->whereHas('attachments',function($q){
                $q->join('files','files.id','attachments.file_id')
                  ->whereNull('files.deleted_at')
                  ->whereNull('directory_id');
            })
            ->get();
    }
}   