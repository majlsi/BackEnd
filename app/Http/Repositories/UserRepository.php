<?php

namespace Repositories;

class UserRepository extends BaseRepository
{

    /**
     * Determine the model of the repository
     *
     */
    public function model()
    {
        return 'Models\User';
    }

    public function login($email, $password)
    {
        $user = $this->model->selectRaw("*")
            ->where('email', $email)
            ->where('password', $password)
            ->first();

        return $user;
    }

    public function getUserByEmail($email)
    {
        $user = $this->model->selectRaw("users.*,organizations.is_active as organization_is_active,organizations.expiry_date_to")
            ->where('email', $email)
            ->leftJoin('organizations', 'organizations.id', 'users.organization_id')->first();
        return $user;
    }

    public function getUserByUsername($username)
    {
        $user = $this->model->selectRaw("*")
            ->where('username', $username)->first();
        return $user;
    }

    public function getUsertByProviderAndUid($provider, $id)
    {
        return $this->model
            ->where('oauth_provider', $provider)
            ->where('oauth_uid', $id)->first();
    }

    public function filteredUsers($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $roleId, $organizationId = null)
    {
        $query = $this->getAllUserQuery($searchObj, $roleId, $organizationId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllUserQuery($searchObj, $roleId, $organizationId)
    {
        if (isset($searchObj->name)) {
            $this->model = $this->model->whereRaw("(name like ? OR name_ar like ?)", array('%' . trim($searchObj->name) . '%', '%' . trim($searchObj->name) . '%'));
        }
        if (isset($searchObj->email)) {
            $this->model = $this->model->whereRaw("(email like ?)", array('%' . trim($searchObj->email) . '%'));
        }
        if (isset($searchObj->role_id)) {
            $this->model = $this->model->where('role_id', '=', $searchObj->role_id);
        }
        if (isset($searchObj->organization_id)) {
            $this->model = $this->model->where('organization_id', '=', $searchObj->organization_id);
        }
        $this->model = $this->model->selectRaw('users.*,roles.role_name,roles.role_name_ar,organizations.organization_name_en')
            ->leftJoin('roles', 'roles.id', 'users.role_id')
            ->leftJoin('organizations', 'organizations.id', 'users.organization_id');

        if ($roleId == config('roles.admin')) {
            $this->model = $this->model->whereNull('users.organization_id');
        } else {
            if (isset($searchObj->is_participant)) {
                $this->model = $this->model->where('users.organization_id', $organizationId)
                    ->where('roles.can_assign', 0)
                    ->where('roles.organization_id', '!=', null);
            } else {
                $this->model = $this->model->where('users.organization_id', $organizationId)
                    ->where('roles.can_assign', 1);
            }
        }
        return $this->model;
    }

    public function getAdminUserForOrganization($organizationsIds)
    {
        return $this->model->selectRaw('users.*,organizations.is_active as organization_is_active')
            ->join('organizations', 'organizations.system_admin_id', 'users.id')
            ->whereIn('users.organization_id', $organizationsIds)
            ->get();
    }

    public function getOrganizationUsers($organizationId)
    {
        return $this->model->selectRaw('users.*')
            ->where('users.organization_id', $organizationId)
            ->get();
    }

    public function getOrganizationUsersWithStakeholders($organizationId, $includeStakeholders, $name)
    {
        $query = $this->model->selectRaw('users.*,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
        user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
        nicknames.nickname_ar,nicknames.nickname_en,images.image_url')
            ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
            ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
            ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id')
            ->leftJoin('images', 'images.id', 'users.profile_image_id')

            ->with('role')
            ->where('users.organization_id', $organizationId);

        if ($name) {
            $this->model = $this->model->whereRaw("(name like ? OR name_ar like ?)", array('%' . $name . '%', '%' . $name . '%'));
        }
        if (!$includeStakeholders) {
            $stakeholdersIds = $this->model->selectRaw('users.id')
                ->join('stakeholders', 'stakeholders.user_id', 'users.id')
                ->where('users.organization_id', $organizationId)
                ->where('users.deleted_at', Null)
                ->where('stakeholders.deleted_at', Null)
                ->get();
            $stakeholdersIds = $stakeholdersIds->pluck('id')->toArray();
            $query = $query->whereNotIn('users.id', $stakeholdersIds);
        }
        return $query->get();
    }
    public function getMatchedOrganizationUsers($organizationId, $name)
    {
        $this->model = $this->model->selectRaw('users.*,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
        user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
        nicknames.nickname_ar,nicknames.nickname_en,images.image_url')
            ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
            ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
            ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id')
            ->leftJoin('images', 'images.id', 'users.profile_image_id')

            ->with('role')
            ->where('users.organization_id', $organizationId);
        if ($name) {
            $this->model = $this->model->whereRaw("(name like ? OR name_ar like ?)", array('%' . $name . '%', '%' . $name . '%'));
        }
        return $this->model->get();
    }

    public function activeDeactiveUser($userId, $isActive)
    {
        $this->model
            ->find($userId)
            ->update(['is_active' => $isActive]);
    }

    public function getUserDetails($userId)
    {
        return $this->model
            ->selectRaw('users.*,rights.right_url,
            CASE WHEN images.image_url IS NULL
            THEN (SELECT img.image_url from organizations left join images as img on organizations.logo_id = img.id where users.organization_id = organizations.id)
            ELSE images.image_url
            END as image_url
            ,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
            user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
            nicknames.nickname_ar,nicknames.nickname_en')
            ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
            ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
            ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id')
            ->leftJoin('images', 'images.id', 'users.profile_image_id')
            ->leftJoin('rights', 'rights.id', 'users.main_page_id')
            ->where('users.id', $userId)
            ->with('organization.timeZone', 'organization.logoImage')
            ->first();
    }

    public function searchOrganizationUsersAndCommittees($organizationId, $name)
    {
        $this->model = $this->model->selectRaw('distinct users.*')
            ->leftJoin('committee_users', 'committee_users.user_id', 'users.id')
            ->leftJoin('committees', 'committees.id', 'committee_users.committee_id')
            ->where('users.organization_id', $organizationId);
        if ($name) {
            $this->model = $this->model->whereRaw("(users.name like ? OR users.name_ar like ?) OR (committees.committee_name_en like ? OR committees.committee_name_ar like ?)", array('%' . trim($name) . '%', '%' . trim($name) . '%', '%' . trim($name) . '%', '%' . trim($name) . '%'));
        }
        return $this->model->get();
    }

    public function getOrganizationActiveUsersNum($organizationId)
    {
        return $this->model->selectRaw('COUNT(*) as num_active_users_per_organization')
            ->where('users.organization_id', $organizationId)
            ->whereNotNull('users.last_login')
            ->first();
    }

    public function getOrganizationInActiveUsersNum($organizationId)
    {
        return $this->model->selectRaw('COUNT(*) as num_inactive_users_per_organization')
            ->where('users.organization_id', $organizationId)
            ->whereNull('users.last_login')
            ->first();
    }

    public function getOrganizationNumOfUsers($organizationId)
    {
        return $this->model->selectRaw('COUNT(*) as num_users_per_organization')
            ->where('users.organization_id', $organizationId)
            ->first();
    }




    public function getLimitOfOrganizationMembers($organizationId)
    {
        return $this->model->selectRaw('users.*,
        CASE WHEN images.image_url IS NULL
        THEN (SELECT img.image_url from organizations left join images as img on organizations.logo_id = img.id where users.organization_id = organizations.id)
        ELSE images.image_url
        END as image_url, roles.id,roles.role_name, roles.role_name_ar')
            ->join('roles', 'roles.id', 'users.role_id')
            ->leftJoin('images', 'images.id', 'users.profile_image_id')
            ->where('users.organization_id', $organizationId)
            ->where('roles.can_assign', true)
            ->limit(config('committeeDashboard.maxMembersNumberForBoardDashboard'))
            ->get();
    }


    public function getLimitOfCommitteeMembers($committee_id)
    {
        return $this->model->selectRaw('users.*,
        CASE WHEN images.image_url IS NULL
        THEN (SELECT img.image_url from organizations left join images as img on organizations.logo_id = img.id where users.organization_id = organizations.id)
        ELSE images.image_url
        END as image_url, roles.id,roles.role_name, roles.role_name_ar')
            ->join('roles', 'roles.id', 'users.role_id')
            ->leftJoin('images', 'images.id', 'users.profile_image_id')
            ->leftJoin('committee_users', 'committee_users.user_id', 'users.id')
            ->where('committee_users.committee_id', $committee_id)
            ->limit(config('committeeDashboard.maxMembersNumberForCommitteeDashboard'))
            ->get();
    }



    public function getAdminsUsers()
    {
        return $this->model->selectRaw('users.*')
            ->where('users.role_id', config('roles.admin'))
            ->get();
    }

    public function getByChatUserId($chatUserId)
    {
        return $this->model->where('users.chat_user_id', $chatUserId)
            ->first();
    }

    public function getUsersWithoutChatUserId()
    {
        return $this->model->selectRaw('users.*')
            ->whereNull('chat_user_id')
            ->get();
    }

    public function filteredOrganizationUsersPagedList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId, $currentUserId)
    {
        $query = $this->getAllOrganizationUsersQuery($searchObj, $organizationId, $currentUserId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    private function getAllOrganizationUsersQuery($searchObj, $organizationId, $currentUserId)
    {
        if (isset($searchObj->search_name)) {
            $this->model = $this->model->whereRaw("(name like ? OR name_ar like ?)", array('%' . trim($searchObj->search_name) . '%', '%' . trim($searchObj->search_name) . '%'));
        }

        return $this->model->selectRaw('users.*,CASE WHEN users.profile_image_id THEN images.image_url ELSE organization_images.image_url END AS profile_image_url')
            ->leftJoin('images', 'images.id', 'users.profile_image_id')
            ->join('organizations', 'organizations.id', 'users.organization_id')
            ->leftJoin('images as organization_images', 'organization_images.id', 'organizations.logo_id')
            ->where('users.organization_id', $organizationId)
            ->where('users.id', '!=', $currentUserId)
            ->with(['Image']);
    }

    public function getOrganizationUsersList($params, $organizationId, $userId)
    {
        $query = $this->getAllOrganizationUsersQuery($params, $organizationId, $userId);
        return $query->get();
    }

    public function getCommitteeUsersWhosActiveNow($committeeId, $currentUserId)
    {
        return $this->model->selectRaw('users.*')
            ->join('committee_users', 'committee_users.user_id', 'users.id')
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
            ->whereRaw('(
                ((committee_users.committee_user_start_date IS NOT NULL AND DATE_ADD(committee_users.committee_user_start_date, INTERVAL (time_zones.diff_hours * -1) HOUR) <= UTC_TIMESTAMP()) OR committee_users.committee_user_start_date IS NULL )
                AND 
                ((committee_users.committee_user_expired_date IS NOT NULL AND DATE_ADD(committee_users.committee_user_expired_date, INTERVAL (time_zones.diff_hours * -1) HOUR) >= UTC_TIMESTAMP()) OR committee_users.committee_user_expired_date IS NULL))')
            ->with('image')
            ->get();
    }

    public function  getOrganizationByEmail($email)
    {
        return $this->model->selectRaw('organizations.*')
            ->join('organizations', 'organizations.id', 'users.organization_id')
            ->where('users.email', $email)
            ->first();
    }

    public function getUsersByIds($usersIds)
    {
        return $this->model->whereIn('id', $usersIds)->get();
    }

    public function activateDeactivateUsers($ids, $activate)
    {
        return $this->model->whereIn('id', $ids)->update(['is_active' => $activate]);
    }
    public function getAllDeActiveAndBlockedUsers($organizationId)
    {
        return $this->model->selectRaw('users.*, roles.id, roles.role_name, roles.role_name_ar')
            ->where('users.organization_id', $organizationId)
            ->leftJoin('roles', 'roles.id', 'users.role_id')
            ->where(function ($query) {
                $query->where('users.is_active', 0)
                      ->orWhere('users.is_blocked', 1);
            })
            ->get();
    }
}
