<?php

namespace Repositories;

use Models\Audit;

class AuditRepository extends BaseRepository {


    public function model() {
        return 'Models\Audit';
    }
    public function getAuditPagedList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId, $userId)
    {
        $query = $this->getAuditsQuery($searchObj, $organizationId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAuditsQuery($searchObj,$organizationId)
    {
        // Filter by user_id if provided

        $this->model = $this->model->selectRaw('audits.*, users.name, users.name_ar')
        ->leftJoin('users', 'users.id', 'audits.user_id')
        ->where('users.organization_id', $organizationId);

        $this->model->leftJoin('committees', function ($join) {
            $join->on('audits.auditable_id', '=', 'committees.id')
                 ->where('audits.auditable_type', '=', 'Models\Committee');
        });
    
        // Left join with users
        $this->model->leftJoin('users as auditable_users', function ($join) {
            $join->on('audits.auditable_id', '=', 'auditable_users.id')
                 ->where('audits.auditable_type', '=', 'Models\User');
        });
    
        $this->model->leftJoin('committee_users', function ($join) {
            $join->on('audits.auditable_id', '=', 'committee_users.id')
                 ->where('audits.auditable_type', '=', 'Models\CommitteeUser');
        });


        $this->model->leftJoin('meetings', function ($join) {
            $join->on('audits.auditable_id', '=', 'meetings.id')
                 ->where('audits.auditable_type', '=', 'Models\Meeting');
        });
        if (isset($searchObj->user_id)) {
            $this->model->where('audits.user_id', '=', $searchObj->user_id['id']);
        }

        if (isset($searchObj->event)) {
            $this->model->where('audits.event', '=', $searchObj->event);
        }

        if (isset($searchObj->model)) {
            $this->model->where('audits.auditable_type', 'LIKE', '%' . $searchObj->model . '%');

        }
        // Filter audits to include only those associated with committees or users
        $this->model->where(function ($query) {
            $query->whereNotNull('committees.id')
                  ->orWhereNotNull('auditable_users.id')
                  ->orWhereNotNull('committee_users.id')
                  ->orWhereNotNull('meetings.id');
        });

    // Select columns based on the model type
        $this->model->selectRaw('
            audits.*,
            users.name as user_name,
            users.name_ar as user_name_ar,
            SUBSTRING_INDEX(audits.auditable_type, "\\\\", -1) as model,
            CASE 
                WHEN audits.auditable_type = "Models\\\\Committee" THEN committees.committee_name_en
                WHEN audits.auditable_type = "Models\\\\User" THEN auditable_users.name
                WHEN audits.auditable_type = "Models\\\\CommitteeUser" THEN users.name
                WHEN audits.auditable_type = "Models\\\\Meeting" THEN meetings.meeting_title_en
                ELSE NULL 
            END AS data,
            CASE 
            WHEN audits.auditable_type = "Models\\\\Committee" THEN committees.committee_name_ar
            WHEN audits.auditable_type = "Models\\\\User" THEN auditable_users.name_ar
            WHEN audits.auditable_type = "Models\\\\CommitteeUser" THEN users.name_ar
            WHEN audits.auditable_type = "Models\\\\Meeting" THEN meetings.meeting_title_ar
            ELSE NULL 
            END AS data_ar
        ');

        return $this->model;

    }
}   