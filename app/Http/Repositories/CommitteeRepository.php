<?php

namespace Repositories;

use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Schema;

class CommitteeRepository extends BaseRepository
{


    public function model()
    {
        return 'Models\Committee';
    }

    public function getPagedCommitees($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId, $includeStakeholders)
    {
        $query = $this->getAllCommiteesQuery($searchObj, $organizationId, $includeStakeholders);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllCommiteesQuery($searchObj, $organizationId, $includeStakeholders)
    {
        if (isset($searchObj->committee_name_en)) {
            $this->model = $this->model->whereRaw("(committee_name_en like ? )", array('%' . trim($searchObj->committee_name_en) . '%'));
        }
        if (isset($searchObj->committee_name_ar)) {
            $this->model = $this->model->whereRaw("(committee_name_ar like ? )", array('%' . trim($searchObj->committee_name_ar) . '%'));
        }
        if (isset($searchObj->committee_code)) {
            $this->model = $this->model->whereRaw("(committee_code like ? )", array('%' . trim($searchObj->committee_code) . '%'));
        }
        if (isset($searchObj->committee_name)) {
            $this->model = $this->model->whereRaw("(committee_name_ar like ? OR committee_name_en like ?)", array('%' . trim($searchObj->committee_name) . '%', '%' . trim($searchObj->committee_name) . '%'));
        }
        if (isset($searchObj->committeee_members_count)) {
            $this->model = $this->model->where("committeee_members_count", $searchObj->committeee_members_count);
        }

        $this->model = $this->model->selectRaw('committees.*, users.name, users.name_ar,
            committee_types.committee_type_name_ar, committee_types.committee_type_name_en')
            ->leftJoin('users', 'users.id', 'committees.committee_head_id')
            ->leftJoin('committee_types', 'committees.committee_type_id', 'committee_types.id')
            ->where('committees.organization_id', $organizationId);

        if (!$includeStakeholders) {
            $this->model = $this->model
                ->whereRaw('(committees.committee_code NOT LIKE "' . config('committee.stakeholders') . '" or committees.committee_code is null )');
        }
        return $this->model;
    }

    //! get committee by committee type
    public function getPagedCommitteesByType($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId)
    {
        $query = $this->getAllCommiteesByTypeQuery($searchObj, $organizationId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }
    public function getAllCommiteesByTypeQuery($searchObj, $organizationId)
    {
        if (isset($searchObj->committee_name_en)) {
            $this->model = $this->model->whereRaw("(committee_name_en like ? )", array('%' . trim($searchObj->committee_name_en) . '%'));
        }
        if (isset($searchObj->committee_name_ar)) {
            $this->model = $this->model->whereRaw("(committee_name_ar like ? )", array('%' . trim($searchObj->committee_name_ar) . '%'));
        }
        if (isset($searchObj->committee_code)) {
            $this->model = $this->model->whereRaw("(committee_code like ? )", array('%' . trim($searchObj->committee_code) . '%'));
        }
        if (isset($searchObj->committee_name)) {
            $this->model = $this->model->whereRaw("(committee_name_ar like ? OR committee_name_en like ?)", array('%' . trim($searchObj->committee_name) . '%', '%' . trim($searchObj->committee_name) . '%'));
        }
        if (isset($searchObj->committeee_members_count)) {
            $this->model = $this->model->where("committeee_members_count", $searchObj->committeee_members_count);
        }

        if (isset($searchObj->committee_type_id)) {
            $this->model = $this->model->where("committee_type_id", $searchObj->committee_type_id);
        }

        $this->model = $this->model->selectRaw('committees.*,users.name,users.name_ar,committee_statuses.committee_status_name_ar,committee_statuses.committee_status_name_en')
            ->leftJoin('users', 'users.id', 'committees.committee_head_id')
            ->leftJoin('committee_statuses', 'committee_statuses.id', 'committees.committee_status_id')
            ->where('committees.organization_id', $organizationId);

        return $this->model;
    }
    public function getCommitteeDetails($id, $user)
    {
        /* return $this->model
            ->selectRaw('committees.*,
        CASE
            WHEN committees.committee_type_id != ?
                AND committees.committee_expired_date IS NOT NULL
                AND DATEDIFF(committees.committee_expired_date, ?) < 0
            THEN 1
            ELSE 0
        END AS isFreezed,
        CASE
            WHEN ? != committees.committee_head_id
            THEN 0
            ELSE 1
        END AS canRequestUnfreeze', [config('committeeTypes.permanent'), now()->startOfDay(), $user->id])
            ->where('committees.id', $id)
            ->with('committeeHead')
            ->with('committeeOrganiser')
            ->with('committeeResponsible')
            ->with('committeeStatus')
            ->with('committeeType')
            ->with('recommendations')
            ->with(['memberUsers' => function ($query) {
                $query->selectRaw('users.*,committee_users.committee_user_start_date,
                committee_users.committee_user_expired_date,committee_users.id as committee_user_id,committee_users.evaluation_id,evaluations.evaluation_name_en,evaluations.evaluation_name_ar,committee_users.evaluation_reason')->leftJoin('evaluations','committee_users.evaluation_id','=','evaluations.id');
            }])
        ->first(); */
        return $this->model
            ->selectRaw(
            'committees.*,
                CASE
                    WHEN committees.committee_type_id != ?
                        AND committees.committee_expired_date IS NOT NULL
                        AND DATEDIFF(committees.committee_expired_date, ?) < 0
                    THEN 1
                    ELSE 0
                END AS isFreezed,
                CASE WHEN ? != committees.committee_head_id THEN 0 ELSE 1 END AS canRequestUnfreeze,
                CASE
                    WHEN committees.committee_type_id = ?
                        AND (SELECT COUNT(*) FROM committee_final_output WHERE committee_final_output.committee_id = committees.id) > 0
                    THEN 0
                    ELSE 1
                END AS can_add_final_output',
                [
                    config('committeeTypes.permanent'), now()->startOfDay(),
                    $user->id, config('committeeTypes.temporary')
                ]
            )
            ->where('committees.id', $id)
            ->with('committeeHead')
            ->with('committeeOrganiser')
            ->with('committeeResponsible')
            ->with('committeeStatus')
            ->with('committeeType')
            ->with('committeeNature')
            ->with(['recommendations' => function ($query) {
                $query->leftJoin('committee_final_output', 'committee_final_output.id', '=', 'committee_recommendation.committee_final_output_id')
                    ->selectRaw("committee_recommendation.*, SUBSTRING(SUBSTRING_INDEX(committee_final_output.final_output_url, '/', -1),11) as committee_final_output_name, committee_final_output.id as committee_final_output_id");
            }])
            ->with('finalOutputs')
            ->with(['memberUsers' => function ($query) {
            $query->selectRaw(
                'users.*,committee_users.committee_user_start_date,
        committee_users.committee_user_expired_date,committee_users.id as committee_user_id,committee_users.evaluation_id,evaluations.evaluation_name_en,evaluations.evaluation_name_ar,committee_users.evaluation_reason')->leftJoin('evaluations','committee_users.evaluation_id','=','evaluations.id');
            }])
            ->first();
    }

    public function getOrganizationCommittees(int $organizationId)
    {
        return $this->model->where('committees.organization_id', $organizationId)->where(function ($query) {
            $query->where('committees.committee_type_id', '=', config('committeeTypes.permanent'))
                ->orWhere(function ($query) {
                    $query->whereRaw('CURDATE() BETWEEN committees.committee_start_date AND committees.committee_expired_date');
                });
        })->get();
    }
    public function getOrganizationNumOfCommittees(int $organizationId)
    {
        return $this->model->selectRaw('COUNT(*) as num_committees_per_organization')
            ->where('committees.organization_id', $organizationId)
            ->where(function ($query) {
                $query->where('committees.committee_type_id', '=', config('committeeTypes.permanent'))
                    ->orWhere(function ($query) {
                        $query->whereRaw('CURDATE() BETWEEN committees.committee_start_date AND committees.committee_expired_date');
                    });
            })
            ->first();
    }

    public function getCommitteeData($organizationId, $committeeId)
    {
        return $this->model->selectRaw('committees.committee_name_en,committees.committee_name_ar')
            ->where('committees.id', $committeeId)
            ->where('committees.organization_id', $organizationId)
            ->first();
    }

    public function getCommitteeByChatRoomId($chatRoomId)
    {
        return $this->model->select('*')
            ->where('chat_room_id', $chatRoomId)->first();
    }

    public function getCommitteesChatsPagedList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId, $userId)
    {
        $query = $this->getAllCommiteesChatsQuery($searchObj, $organizationId, $userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllCommiteesChatsQuery($searchObj, $organizationId, $userId)
    {
        if (isset($searchObj->committee_name)) {
            $this->model = $this->model->whereRaw("(committee_name_ar like ? OR committee_name_en like ?)", array('%' . trim($searchObj->committee_name) . '%', '%' . trim($searchObj->committee_name) . '%'));
        }
        return $this->model->selectRaw('distinct committees.*')
            ->leftJoin('committee_users', 'committee_users.committee_id', 'committees.id')
            ->where('committees.organization_id', $organizationId)
            ->whereNotNull('chat_room_id')
            ->whereRaw('(committee_head_id = ? OR committee_organiser_id =? OR committee_users.user_id = ?)', array($userId, $userId, $userId))
            ->distinct();
    }

    public function getAllUserCommittees($searchObj, $organizationId, $userId)
    {
        $query = $this->getAllUserCommitteesQuery($searchObj, $organizationId, $userId);
        return $query->get();
    }
    public function getAllUserCommitteesQuery($searchObj, $organizationId, $userId)
    {
        if (isset($searchObj->search_name)) {
            $this->model = $this->model->whereRaw("(committee_name_ar like ? OR committee_name_en like ?)", array('%' . trim($searchObj->search_name) . '%', '%' . trim($searchObj->search_name) . '%'));
        }
        $query = $this->model->selectRaw('distinct committees.*')
            ->join('organizations', 'organizations.id', 'committees.organization_id')
            ->join('time_zones', 'time_zones.id', 'organizations.time_zone_id')
            ->leftJoin('committee_users', 'committee_users.committee_id', 'committees.id');
        $query = $this->applySharedCommitteeConditions($query, $organizationId, $userId);

        return $query->distinct()->orderBy('id', 'DESC');
    }

    public function checkColumnExists($col)
    {
        if (Schema::hasColumn($this->model->getTable(), $col)) {
            return true;
        }
        return false;
    }

    public function getCommitteesWhichCurrentUserIsMemberOnIt($userId, $organizationId)
    {
        $query = $this->model->selectRaw('distinct committees.*')
            ->join('organizations', 'organizations.id', 'committees.organization_id')
            ->join('time_zones', 'time_zones.id', 'organizations.time_zone_id')
            ->leftJoin('committee_users', 'committee_users.committee_id', 'committees.id');
        return $this->applySharedCommitteeConditions($query, $organizationId, $userId);
    }


    public function getCountOfCommitteesForCurrentUser($userId, $organizationId)
    {
        $query = $this->model->selectRaw('Count(DISTINCT committees.id) as count')
            ->join('organizations', 'organizations.id', 'committees.organization_id')
            ->join('time_zones', 'time_zones.id', 'organizations.time_zone_id')
            ->leftJoin('committee_users', 'committee_users.committee_id', 'committees.id');
        $query = $this->applySharedCommitteeConditions($query, $userId, $organizationId);
        return $query->first()->count;
    }



    public function getCommitteesThatUserMemberOnIt($userId, $organizationId)
    {
        $query = $this->getCommitteesWhichCurrentUserIsMemberOnIt($userId, $organizationId);
        return $query->get();
    }

    public function getSystemCommittees()
    {
        return $this->model->selectRaw('*')
            ->where('is_system', 1)
            ->whereNull('organization_id')->get();
    }

    public function getLimitOfCommitteesThatCurrentUserOnIt($userId, $organizationId)
    {
        $query = $this->getCommitteesWhichCurrentUserIsMemberOnIt($userId, $organizationId);
        return $query->selectRaw('CASE WHEN committee_head_id = ' . $userId . ' THEN 1 ELSE 0 END AS is_head_of_committee,
            CASE WHEN committee_organiser_id = ' . $userId . ' THEN 1 ELSE 0 END AS is_organiser_of_committee,
            (SELECT committee_users.committee_user_start_date FROM committee_users WHERE committees.id = committee_users.committee_id AND committee_users.user_id = ' . $userId . ' ) AS committee_user_start_date,
            (SELECT committee_users.committee_user_expired_date FROM committee_users WHERE committees.id = committee_users.committee_id AND committee_users.user_id = ' . $userId . ' ) AS committee_user_expired_date')
            ->limit(config('committeeDashboard.maxCommitteesNumberForMemberDashboard'))->get();
    }


    public function getUserManagedCommittees($userId)
    {
        $query =  $this->model->selectRaw('distinct committees.*')
            ->join('organizations', 'organizations.id', 'committees.organization_id')
            ->join('time_zones', 'time_zones.id', 'organizations.time_zone_id')
            ->whereRaw('(committee_head_id = ? OR committee_organiser_id =?)', array($userId, $userId))
            ->whereRaw('(
            ((committees.committee_start_date IS NOT NULL AND DATE_ADD(committees.committee_start_date, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) OR committees.committee_start_date IS NULL )
            AND 
            ((committees.committee_expired_date IS NOT NULL AND DATE_ADD(committees.committee_expired_date, INTERVAL (time_zones.diff_hours * -1) HOUR) >= UTC_TIMESTAMP()) OR committees.committee_expired_date IS NULL))');

        return $query->get();
    }



    public function getCommitteesForOrganization($organizationId)
    {
        $query =  $this->model->selectRaw('distinct committees.*,users.name,users.name_ar')
            ->leftJoin('users', 'users.id', 'committees.committee_head_id')
            ->join('organizations', 'organizations.id', 'committees.organization_id')
            ->join('time_zones', 'time_zones.id', 'organizations.time_zone_id')
            ->where('committees.organization_id', $organizationId)
            ->whereRaw('(
            ((committees.committee_start_date IS NOT NULL AND DATE_ADD(committees.committee_start_date, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) OR committees.committee_start_date IS NULL )
            AND 
            ((committees.committee_expired_date IS NOT NULL AND DATE_ADD(committees.committee_expired_date, INTERVAL (time_zones.diff_hours * -1) HOUR) >= UTC_TIMESTAMP()) OR committees.committee_expired_date IS NULL))');

        return $query;
    }


    public function getCountOfCommitteesMembersForOrganization($organizationId)
    {
        $query =  $this->model->selectRaw('sum(committees.committeee_members_count) as committeee_members_count')
            ->join('organizations', 'organizations.id', 'committees.organization_id')
            ->join('time_zones', 'time_zones.id', 'organizations.time_zone_id')
            ->where('committees.organization_id', $organizationId)
            ->whereRaw('(
            ((committees.committee_start_date IS NOT NULL AND DATE_ADD(committees.committee_start_date, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) OR committees.committee_start_date IS NULL )
            AND 
            ((committees.committee_expired_date IS NOT NULL AND DATE_ADD(committees.committee_expired_date, INTERVAL (time_zones.diff_hours * -1) HOUR) >= UTC_TIMESTAMP()) OR committees.committee_expired_date IS NULL))');

        return $query->first();
    }

    public function getLimitOfOrganizationCommittees($organizationId)
    {
        $query = $this->getCommitteesForOrganization($organizationId);
        return $query->limit(config('committeeDashboard.maxCommitteesNumberForBoardDashboard'))->get();
    }

    public function getCommitteeByCode($code)
    {
        return $this->model->where('committee_code', $code)->where('is_system', 1)->first();
    }

    public function getNearedExpiredCommittees()
    {
        $tenDaysFromToday = now()->addDays(10)->format('Y-m-d');

        return $this->model->whereDate('committee_expired_date', '=', $tenDaysFromToday)->get();
    }

    public function applySharedCommitteeConditions($query, $organizationId, $userId)
    {
        return $query->where('committees.organization_id', $organizationId)
            ->where(function ($query) {
                $query->where('committees.committee_type_id', '=', config('committeeTypes.permanent'))
                    ->orWhere(function ($query) {
                        $query->whereRaw('CURDATE() BETWEEN committees.committee_start_date AND committees.committee_expired_date');
                    });
            })
            ->whereRaw('(committee_head_id = ? OR committee_organiser_id =? OR committee_users.user_id = ?)', array($userId, $userId, $userId))
            ->whereRaw(
                '(
                ((committees.committee_start_date IS NOT NULL AND DATE(committees.committee_start_date) <= CURDATE()) OR committees.committee_start_date IS NULL )
                AND
                ((committees.committee_expired_date IS NOT NULL AND DATE(committees.committee_expired_date) >= CURDATE()) OR committees.committee_expired_date IS NULL))'
            )
            ->whereRaw(
                '(
                ((committee_users.committee_user_start_date IS NOT NULL AND DATE(committee_users.committee_user_start_date) <= CURDATE()) OR committee_users.committee_user_start_date IS NULL )
                AND
                ((committee_users.committee_user_expired_date IS NOT NULL AND DATE(committee_users.committee_user_expired_date) >= CURDATE()) OR committee_users.committee_user_expired_date IS NULL))'
            );
    }

    public function getOrganizationNumOfPermanentCommittees(int $organizationId)
    {
        return $this->model->selectRaw('COUNT(id) as num_permanent_committees_per_organization')
            ->where('committees.organization_id', $organizationId)
            ->where(function ($query) {
                $query->where('committees.committee_type_id', '=', config('committeeTypes.permanent'));
            })
            ->first();
    }

    public function getOrganizationNumOfTemporaryCommittees(int $organizationId)
    {
        return $this->model->selectRaw('COUNT(id) as num_temporary_committees_per_organization')
            ->where('committees.organization_id', $organizationId)
            ->where(function ($query) {
                $query->where('committees.committee_type_id', '=', config('committeeTypes.temporary'));
            })
            ->first();
    }

    public function getNumberOfStandingCommitteeMembers(int $organizationId)
    {
        return $this->model->selectRaw('SUM(committeee_members_count) as num_of_standing_committee_member')
            ->where('committees.organization_id', $organizationId)
            ->where('committee_status_id', 2)
            ->orWhere('committee_status_id', 3)
            ->first();
    }
    public function getNumberOfFreezedCommitteeMembers(int $organizationId)
    {
        return $this->model->selectRaw('SUM(committeee_members_count) as num_of_Freezed_committee_member')
            ->where('committees.organization_id', $organizationId)
            ->where('committee_status_id', 4)
            ->orWhere('committee_status_id', 5)
            ->first();
    }

    public function getCommitteeDaysPassed(int $organizationId)
    {
        return $this->model->selectRaw(' * , DATEDIFF(NOW(), committee_start_date) as days_passed')
            ->where('organization_id', $organizationId)
            ->where('committee_type_id', config('committeeTypes.temporary'))
            ->whereRaw('DATEDIFF(committee_expired_date, NOW()) > 0')
            ->orderByDesc('days_passed')
            ->get();
    }


        //! committee passed Days
        public function getAllCommiteesPassedDaysQuery($organizationId)
        {
    
            $this->model = $this->model->selectRaw(' * , DATEDIFF(NOW(), committee_start_date) as days_passed')
                ->where('organization_id', $organizationId)
                ->where('committee_type_id', config('committeeTypes.temporary'))
                ->whereRaw('DATEDIFF(committee_expired_date, NOW()) > 0');
    
    
            return $this->model;
        }
        public function getPagedCommiteesPassedDays($pageNumber, $pageSize, $sortBy, $sortDirection, $organizationId)
        {
            $query = $this->getAllCommiteesPassedDaysQuery($organizationId);
            return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
        }
    
    
    
    
        //! committee remain percentage to finish
        public function getCommitteeRemainPercentageToFinishQuery(int $organizationId)
        {
            return $this->model->selectRaw('*, 
            CAST((DATEDIFF(committee_expired_date, NOW()) / DATEDIFF(committee_expired_date, committee_start_date)) * 100 as unsigned) as remain_to_finished')
                ->where('organization_id', $organizationId)
                ->where('committee_type_id', config('committeeTypes.temporary'))
                ->whereRaw('DATEDIFF(committee_expired_date, NOW()) > 0')
                ->whereRaw('(DATEDIFF(committee_expired_date, NOW()) / DATEDIFF(committee_expired_date, committee_start_date)) < 0.2');
        }
        public function getPagedCommitteeRemainPercentageToFinish($pageNumber, $pageSize, $sortBy, $sortDirection, $organizationId)
        {
            $query = $this->getCommitteeRemainPercentageToFinishQuery($organizationId);
            return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
        }
    
    
    
        //! Number Of Committees According To Committee Decision Responsible
        public function getNumberOfCommitteesAccordingToCommitteeDecisionResponsibleQuery(int $organizationId)
        {
            return $this->model->selectRaw('users.*, 
            COUNT(committees.id) as number_of_committees')
                ->join('users', 'users.id', '=', 'committees.decision_responsible_user_id')
                ->where('committees.organization_id', $organizationId)
                ->groupBy('decision_responsible_user_id');
        }
        public function getPagedNumberOfCommitteesAccordingToCommitteeDecisionResponsible($pageNumber, $pageSize, $sortBy, $sortDirection, $organizationId)
        {
            $query = $this->getNumberOfCommitteesAccordingToCommitteeDecisionResponsibleQuery($organizationId);
            return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
        }

    public function getTotalNumberOfCommittees(int $organizationId)
    {
        return $this->model->where('organization_id', $organizationId)
            ->whereNotNull('committee_status_id')
            ->count();
    }

    public function getExpiredCommittees()
    {
        return $this->model->whereRaw(
            'DATE_ADD(committee_expired_date, INTERVAL 1 DAY) = ?',
            [now()->format('Y-m-d')]
        )->get();
    }

    public function getFileTypeOfFinalOutput($fileTypeId) {
        return DB::table('file_types')->where('id', $fileTypeId)->first();
    }

    public function getPagedMyCommittees(
        $pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId,
        $includeStakeholders, $userId
    )
    {
        $query = $this->getMyCommiteesQuery($searchObj, $organizationId, $userId, $includeStakeholders);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getMyCommiteesQuery($searchObj, $organizationId, $userId, $includeStakeholders)
    {
        if (isset($searchObj->committee_name_en)) {
            $this->model = $this->model->whereRaw("(committee_name_en like ? )", array('%' . trim($searchObj->committee_name_en) . '%'));
        }
        if (isset($searchObj->committee_name_ar)) {
            $this->model = $this->model->whereRaw("(committee_name_ar like ? )", array('%' . trim($searchObj->committee_name_ar) . '%'));
        }
        if (isset($searchObj->committee_code)) {
            $this->model = $this->model->whereRaw("(committee_code like ? )", array('%' . trim($searchObj->committee_code) . '%'));
        }
        if (isset($searchObj->committee_name)) {
            $this->model = $this->model->whereRaw("(committee_name_ar like ? OR committee_name_en like ?)", array('%' . trim($searchObj->committee_name) . '%', '%' . trim($searchObj->committee_name) . '%'));
        }
        if (isset($searchObj->committeee_members_count)) {
            $this->model = $this->model->where("committeee_members_count", $searchObj->committeee_members_count);
        }

        $this->model = $this->model->selectRaw('committees.*, users.name, users.name_ar,
            committee_types.committee_type_name_ar, committee_types.committee_type_name_en')
            ->leftJoin('users', 'users.id', 'committees.committee_head_id')
            ->leftJoin('committee_types', 'committees.committee_type_id', 'committee_types.id')
            ->where('committees.organization_id', $organizationId)
            ->whereExists(function ($query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('committee_users as cu')
                    ->whereRaw('cu.committee_id = committees.id')
                    ->where('cu.user_id', $userId);
            })
            ->groupBy('committees.id');

        if (!$includeStakeholders) {
            $this->model = $this->model
                ->whereRaw('(committees.committee_code NOT LIKE "' . config('committee.stakeholders') . '" or committees.committee_code is null )');
        }
        return $this->model;
    }
}
