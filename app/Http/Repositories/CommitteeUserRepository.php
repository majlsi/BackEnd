<?php

namespace Repositories;

class CommitteeUserRepository extends BaseRepository
{


    public function model()
    {
        return 'Models\CommitteeUser';
    }


    public function getUserCommittees($userId)
    {
        return $this->model->selectRaw('*')
            ->where('user_id', $userId)
            ->get();
    }

    public function getCommitteeUsers($committee_id)
    {
        return $this->model->selectRaw('users.id')
            ->Join('users', 'users.id', 'committee_users.user_id')
            ->where('committee_users.committee_id', $committee_id)
            ->whereNotNull('users.chat_user_id')
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function checkIfUserIsHeadOfCommittee($userId, $committeeId)
    {
        return $this->model->where('committee_users.user_id', $userId)
            ->where('committee_users.committee_id', $committeeId)
            ->where('is_head', 1)->first();
    }

    public function getCommitteeUsersWhosActiveNow($committeeId)
    {
        return $this->model->selectRaw('committee_users.*')
            ->join('committees', 'committees.id', 'committee_users.committee_id')
            ->join('organizations', 'organizations.id', 'committees.organization_id')
            ->join('time_zones', 'time_zones.id', 'organizations.time_zone_id')
            ->where('committees.id', $committeeId)
            ->where(function ($query) {
                $query->where('committees.committee_type_id', '=', config('committeeTypes.permanent'))
                    ->orWhere(function ($query) {
                        $query->whereRaw('CURDATE() BETWEEN committees.committee_start_date AND committees.committee_expired_date');
                    });
            })
            ->where('committee_users.deleted_at', null)
            ->whereRaw('(
                ((committee_users.committee_user_start_date IS NOT NULL AND DATE_ADD(committee_users.committee_user_start_date, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) OR committee_users.committee_user_start_date IS NULL )
                AND 
                ((committee_users.committee_user_expired_date IS NOT NULL AND DATE_ADD(committee_users.committee_user_expired_date, INTERVAL (time_zones.diff_hours * -1) HOUR) >= UTC_TIMESTAMP()) OR committee_users.committee_user_expired_date IS NULL))')
            ->get();
    }

    public function deleteByUserIdAndCommitteeId($userId, $committeeId)
    {
        $this->model->where('user_id', $userId)
            ->where('committee_id', $committeeId)
            ->where('deleted_at', null)
            ->delete();
    }


    //! get committeeUser by user_Id and committee_id
    public function getCommitteeUserId($userId, $committeeId)
    {
        return $this->model->selectRaw('*')
            ->where('user_id', $userId)
            ->where('committee_id', $committeeId)
            ->first();
    }


    public function getByIdOrNull($id)
    {
        return $this->model->find($id);
    }



    public function getPercentageOfEvaluations(int $organizationId)
    {


        $totalCommitteeUsers = $this->model->whereNotNull('evaluation_id')->count();

        $evaluations = $this->model->selectRaw('evaluations.*, 
        COUNT(committee_users.id) as number_of_members')
            ->rightJoin('committees', function ($join) use($organizationId) {
                $join->on('committee_users.committee_id', '=', 'committees.id')
                    ->where('committees.organization_id', '=', $organizationId);
            })
            ->rightJoin('evaluations', 'evaluations.id', '=', 'committee_users.evaluation_id')
            ->groupBy('evaluations.id')
            ->get();


        $evaluationsWithPercentage["evaluations"] = $evaluations->map(function ($evaluation) use ($totalCommitteeUsers) {
            $evaluation->percentage = $totalCommitteeUsers > 0 ? ($evaluation->number_of_members / $totalCommitteeUsers) * 100 : 0;
            return $evaluation;
        });

        $evaluationsWithPercentage['totalCommitteeUsers']=$totalCommitteeUsers;
        return $evaluationsWithPercentage;
    }


    public function getMostMemberParticipateInCommitteesQuery(int $organizationId)
    {
        return $this->model->selectRaw('users.*, 
        COUNT(committee_users.committee_id) as number_of_committees')
            ->join('users', 'users.id', '=', 'committee_users.user_id')
            ->join('committees', 'committees.id', '=', 'committee_users.committee_id')
            ->where('committees.organization_id', $organizationId)
            ->groupBy('users.id');
    }

    public function getPagedMostMemberParticipateInCommittees($pageNumber, $pageSize, $sortBy, $sortDirection, $organizationId)
    {
        $query = $this->getMostMemberParticipateInCommitteesQuery($organizationId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

}

