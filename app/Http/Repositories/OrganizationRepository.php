<?php

namespace Repositories;

use Carbon\Carbon;

class OrganizationRepository extends BaseRepository
{


    public function model()
    {
        return 'Models\Organization';
    }

    public function getOrganizationData($organizationId)
    {
        return $this->model->selectRaw(
            'organizations.organization_number_of_users,
        (SELECT COUNT(id) from users WHERE users.organization_id = organizations.id  AND users.deleted_at IS NULL) 
        as users_number , 
        organizations.logo_id, 
        organizations.stakeholders_count, 
        (select count(id) from stakeholders
            where user_id in (
            select id from users
            where organization_id = organizations.id
            )) as stakeholders_number'
        )
            ->leftJoin('users', 'users.organization_id', 'organizations.id')
            ->where('users.organization_id', $organizationId)
            ->where('users.deleted_at', null)
            ->first();
    }

    public function getPagedOrgaizations($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection)
    {
        $query = $this->getAllOrgaizationsQuery($searchObj);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllOrgaizationsQuery($searchObj)
    {
        if (isset($searchObj->organization_name_en)) {
            $this->model = $this->model->whereRaw("(organization_name_en like ? )", array('%' . $searchObj->organization_name_en . '%'));
        }
        if (isset($searchObj->organization_name_ar)) {
            $this->model = $this->model->whereRaw("(organization_name_ar like ? )", array('%' . $searchObj->organization_name_ar . '%'));
        }
        if (isset($searchObj->organization_code)) {
            $this->model = $this->model->whereRaw("(organization_code like ? )", array('%' . trim($searchObj->organization_code) . '%'));
        }
        if (isset($searchObj->organization_name)) {
            $this->model = $this->model->whereRaw("(organization_name_ar like ? OR organization_name_en like ?)", array('%' . trim($searchObj->organization_name) . '%', '%' . trim($searchObj->organization_name) . '%'));
        }
        if (isset($searchObj->organization_phone)) {
            $this->model = $this->model->whereRaw("(organization_phone like ? )", array('%' . $searchObj->organization_phone . '%'));
        }
        if (isset($searchObj->email)) {
            $this->model = $this->model->whereRaw("(users.email like ? )", array('%' . trim($searchObj->email) . '%'));
        }
        if (isset($searchObj->user_name)) {
            $this->model = $this->model->whereRaw("(users.name like ? OR users.name_ar like ?)", array('%' . trim($searchObj->user_name) . '%', '%' . trim($searchObj->user_name) . '%'));
        }
        if (isset($searchObj->organization_number_of_users)) {
            $this->model = $this->model->where("organization_number_of_users", $searchObj->organization_number_of_users);
        }

        if (isset($searchObj->is_active)) {
            $this->model = $this->model->where("organizations.is_active", $searchObj->is_active);
        } else if ($searchObj->is_active == null) {
            $this->model = $this->model->where("organizations.is_active", $searchObj->is_active);
        }
        return $this->model->selectRaw('organizations.*,users.name,users.name_ar,users.email,images.original_image_url,
        TIMESTAMPDIFF(DAY,organizations.expiry_date_from,organizations.expiry_date_to) as licenseDuration')
            ->leftJoin('users', 'users.id', 'organizations.system_admin_id')
            ->leftJoin('images', 'images.id', 'organizations.logo_id');
    }

    public function activeDeactiveOrganization($organizationsIds, $isActive, $expiryDateFrom, $expiryDateTo, $numberOfUsers, $directory_quota, $isStakeholderEnabled, $stakeholdersCount)
    {
        for ($i = 0; $i < count($organizationsIds); $i++) {
            $query = $this->model
                ->find($organizationsIds[$i]);
            if ($expiryDateFrom && $expiryDateTo && $directory_quota) {
                $query = $query->update([
                    'is_active' => $isActive,
                    'expiry_date_from' => $expiryDateFrom,
                    'expiry_date_to' => $expiryDateTo,
                    'organization_number_of_users' => $numberOfUsers,
                    'directory_quota' => $directory_quota,
                    'is_stakeholder_enabled' => $isStakeholderEnabled,
                    'stakeholders_count' => $stakeholdersCount,
                ]);
            } elseif ($expiryDateFrom && $expiryDateTo) {
                $query = $query->update([
                    'is_active' => $isActive,
                    'expiry_date_from' => $expiryDateFrom,
                    'expiry_date_to' => $expiryDateTo,
                    'organization_number_of_users' => $numberOfUsers,
                    'is_stakeholder_enabled' => $isStakeholderEnabled,
                    'stakeholders_count' => $stakeholdersCount,
                ]);
            } elseif ($expiryDateFrom && !$expiryDateTo) {
                $expiryDateToCarbon = Carbon::parse($expiryDateFrom)->addDays(config('activateOrganiztion.num_days'));
                $expiryDateTo = $expiryDateToCarbon->format('y-m-d');
                $query = $query->update([
                    'is_active' => $isActive,
                    'expiry_date_from' => $expiryDateFrom,
                    'expiry_date_to' => $expiryDateTo,
                    'organization_number_of_users' => $numberOfUsers,
                    'is_stakeholder_enabled' => $isStakeholderEnabled,
                    'stakeholders_count' => $stakeholdersCount,
                ]);
            } else {
                $query = $query->update([
                    'is_active' => $isActive,
                    'organization_number_of_users' => $numberOfUsers,
                    'is_stakeholder_enabled' => $isStakeholderEnabled,
                    'stakeholders_count' => $stakeholdersCount,
                ]);
            }
        }
    }

    public function deactiveOrganizations($organizationsIds)
    {
        for ($i = 0; $i < count($organizationsIds); $i++) {
            $query = $this->model->find($organizationsIds[$i]);
            $query = $query->update(['is_active' => false]);
        }
    }

    public function getNumOfActiveOrganization()
    {
        return $this->model->selectRaw('COUNT(*) as num_of_active_organization')
            ->where('organizations.is_active', 1)
            ->first();
    }

    public function getNumOfInActiveOrganization()
    {
        return $this->model->selectRaw('COUNT(*) as num_of_inactive_organization')
            ->where('organizations.is_active', 0)
            ->first();
    }

    public function getNumOfNewOrganizationRequests()
    {
        return $this->model->selectRaw('COUNT(*) as num_of_new_organization_requests')
            ->whereNull('organizations.is_active')
            ->first();
    }

    public function getHighActiveOrganizations()
    {
        return $this->model->selectRaw('organizations.id,organizations.organization_name_en,organizations.organization_name_ar,organizations.expiry_date_to,images.original_image_url,
        (SELECT COUNT(id) from users WHERE users.organization_id = organizations.id  AND users.deleted_at IS NULL) as users_number,
        (SELECT COUNT(id) from meetings WHERE meetings.organization_id = organizations.id  AND meetings.deleted_at IS NULL) as meetings_number')
            ->leftJoin('images', 'images.id', 'organizations.logo_id')
            ->whereDate('organizations.expiry_date_to', '>=', Carbon::today())
            ->where('organizations.is_active', 1)
            ->orderBy('meetings_number', 'desc')
            ->limit(5)
            ->get();
    }

    public function getNumOfActiveAndInactiveOrganizations($numOfUsersStart, $numOfUsersEnd)
    {
        return $this->model->selectRaw('organizations.id,organizations.is_active')

            ->whereRaw("(SELECT COUNT(id) from users WHERE users.organization_id = organizations.id  AND users.deleted_at IS NULL) >= $numOfUsersStart AND 
     (SELECT COUNT(id) from users WHERE users.organization_id = organizations.id  AND users.deleted_at IS NULL) <= $numOfUsersEnd AND
     organizations.expiry_date_to >= NOW()
     ")

            ->get();
    }

    public function getExpiredOrganizations()
    {
        return $this->model->selectRaw('organizations.id,organizations.organization_name_ar,organizations.organization_name_en,DATE(organizations.expiry_date_to) as expiry_date_to,users.name as system_admin_name,users.name_ar as system_admin_name_ar,users.email as system_admin_email,users.language_id')
            ->leftJoin('users', 'users.id', 'organizations.system_admin_id')
            ->where('organizations.is_active', 1)
            ->whereRaw('DATE_SUB(expiry_date_to, INTERVAL ? DAY) = CURRENT_DATE()', array(config('organization.sub_days_number')))
            ->get();
    }

    public function getOrganizationDetails($organizationId)
    {
        return $this->model->selectRaw('organizations.*')
            ->where('organizations.id', $organizationId)
            ->first();
    }

    public function getOrganizationByStcCustomerRef($stcCustomerRef)
    {
        return $this->model
            ->where('stc_customer_ref', $stcCustomerRef)
            ->orderBy('id', 'desc')
            ->first();
    }
}
