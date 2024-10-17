<?php

namespace Repositories;

class RoleRepository extends BaseRepository
{

    /**
     * Determine the model of the repository
     *
     */
    public function model()
    {
        return 'Models\Role';
    }

    public function getOrganizationRoles($organizationId)
    {
        return $this->model->selectRaw('roles.*')
            ->where('roles.organization_id', $organizationId)
            ->orWhereRaw('roles.is_organization = 1 AND  roles.organization_id is NULL')
        ->where('can_assign', true)
        ->get();
    }

    public function getAdminRole()
    {
        return $this->model->selectRaw('*')
            ->whereNull('organization_id')
            ->where('is_organization', false)
            ->where('can_assign', 1)
            ->get();
    }

    public function filterRoles($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $roleId, $organizationId)
    {
        $query = $this->getAllRolesQuery($searchObj, $roleId, $organizationId);
        return $this->getPagedQueryResults($pageNumber,  $pageSize,  $query, $sortBy, $sortDirection);
    }

    public function getAllRolesQuery($searchObj, $roleId, $organizationId)
    {
        if (isset($searchObj->role_name)) {
            $this->model = $this->model->whereRaw("(role_name like ?)", array('%' . trim($searchObj->role_name) . '%'));
        }
        if (isset($searchObj->role_name_ar)) {
            $this->model = $this->model->whereRaw("(role_name_ar like ?)", array('%' . trim($searchObj->role_name_ar) . '%'));
        }
        if ($roleId == config('roles.admin')) {
            $this->model = $this->model->whereNull('organization_id');
        } else if ($organizationId) {
            $this->model = $this->model->where('organization_id', $organizationId)
                ->orWhereRaw('roles.is_organization = 1 AND  roles.organization_id is NULL');
        }

        $this->model = $this->model->selectRaw('roles.* , CASE WHEN (roles.is_system AND ? is not NULL) then 1 else 0 end as is_read_only', array($organizationId));
        return $this->model;
    }

    public function getRoleAccessRights($roleId)
    {
        return $this->model->selectRaw('rights.*')
        ->leftJoin('role_rights', 'roles.id', 'role_rights.role_id')
        ->leftJoin('rights', 'rights.id', 'role_rights.right_id')
        ->where('role_rights.role_id', $roleId)
            ->where('rights.in_menu', 1)
            ->get();
    }

    public function getMeetingRoles($organizationId)
    {
        return $this->model->selectRaw('roles.*')
            ->where('roles.organization_id', $organizationId)
            ->where('roles.is_meeting_role', 1)
            ->get();
    }
    public function getCountOfMemebersForOrganization($organizationId)
    {
        return $this->model->selectRaw('COUNT(users.id) as count , roles.id,roles.role_name, roles.role_name_ar')
            ->leftJoin('users', 'users.role_id', 'roles.id')
            ->where('users.organization_id', $organizationId)
            ->where('roles.can_assign', true)
            ->whereNull('users.deleted_at')
            ->groupBy(['roles.id', 'roles.role_name', 'roles.role_name_ar'])
            ->limit(config('committeeDashboard.maxRolesNumberForBoardDashboard'))
            ->get();
    }

    public function getCountOfMemebersForCommittee($committeeId)
    {
        return $this->model->selectRaw('COUNT(users.id) as count , roles.id,roles.role_name, roles.role_name_ar')
            ->leftJoin('users', 'users.role_id', 'roles.id')
            ->leftJoin('committee_users', 'committee_users.user_id', 'users.id')
            ->where('committee_users.committee_id', $committeeId)
            ->where('roles.can_assign', true)
            ->whereNull('users.deleted_at')
            ->groupBy(['roles.id', 'roles.role_name', 'roles.role_name_ar'])
            ->limit(config('committeeDashboard.maxRolesNumberForBoardDashboard'))
            ->get();
    }

    public function getRoleByCode($roleCode)
    {
        return $this->model
            ->where('role_code', $roleCode)
            ->first();
    }


    public function is_read_only($role_id, $organization_id)
    {
        return  $this->model->selectRaw('CASE WHEN (roles.is_system AND ? is not NULL) then 1 else 0 end as is_read_only', array($organization_id))
            ->where('organization_id', $organization_id)
            ->orWhereRaw('roles.is_organization = 1 AND  roles.organization_id is NULL')
            ->where('id', $role_id)
            ->first();
    }

    public function getAllRoleAccessRights($roleId)
    {
        return $this->model->selectRaw('rights.*')
            ->leftJoin('role_rights', 'roles.id', 'role_rights.role_id')
            ->leftJoin('rights', 'rights.id', 'role_rights.right_id')
            ->where('role_rights.role_id', $roleId)
            ->where('role_rights.deleted_at', null)
            ->get();
    }
}
