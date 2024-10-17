<?php

namespace Repositories;

class RequestRepository extends BaseRepository
{


    public function model()
    {
        return 'Models\Request';
    }

    public function getRequestsPagedList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId, $userId)
    {
        $query = $this->getRequestsQuery($searchObj, $organizationId, $userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getRequestBodyPagedList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId, $userId)
    {
        $query = $this->getRequestBodyQuery($searchObj, $organizationId, $userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    private function getRequestsQuery($searchObj, $organizationId, $userId)
    {
        
        if (isset($searchObj->request_type_id)) {
            $this->model = $this->model->where('requests.request_type_id', $searchObj->request_type_id);
        }




        return $this->model->selectRaw(
            "DISTINCT requests.id, requests.request_body,requests.created_by, requests.request_type_id, 
                DATE_FORMAT(requests.created_at, '%d/%m/%Y') AS created_at_formatted,
                DATE_FORMAT(requests.updated_at, '%d/%m/%Y') AS updated_at_formatted
                "
        )->where("created_by",$userId)->where("organization_id",$organizationId);
          
        
    }


    private function getRequestBodyQuery($searchObj, $organizationId, $userId)
    {
        if (isset($searchObj->request_type_id)) {
            $this->model = $this->model->where('requests.request_type_id', $searchObj->request_type_id);
        }

        if (isset($searchObj->committee_name_en)) {
            $this->model = $this->model->whereRaw("( JSON_UNQUOTE(JSON_EXTRACT(requests.request_body, '$.committee_name_en')) like ? )", array('%' . trim($searchObj->committee_name_en) . '%'));
        }
        if (isset($searchObj->committee_name_ar)) {
            $this->model = $this->model->whereRaw("( JSON_UNQUOTE(JSON_EXTRACT(requests.request_body, '$.committee_name_ar')) like ? )", array('%' . trim($searchObj->committee_name_ar) . '%'));
        }
        if (isset($searchObj->committee_code)) {
            $this->model = $this->model->whereRaw("(JSON_UNQUOTE(JSON_EXTRACT(requests.request_body, '$.committee_code')) like ? )", array('%' . trim($searchObj->committee_code) . '%'));
        }

        return $this->model->selectRaw("requests.id,requests.request_body")
                                ->where("created_by",$userId)->where("organization_id",$organizationId)->whereNull("is_approved");
          
        
    }

    public function getUsersRequestsByCommitteeId($committeeId,$user)
    {
        $usersList = $this->model->where('request_type_id', config("requestTypes.addUserToCommittee"))
        ->whereRaw('CAST(json_unquote(json_extract(`request_body`, "$.\"user_committee_id\"")) AS CHAR) = ?', [$committeeId])
        ->where('created_by',$user->id)
        ->where(function ($query) {
            $query->WhereNull('is_approved');
        })
        ->get(['request_body']);
        return $usersList;
    }

    public function getCommitteeRequestsPagesPagedList($pageNumber, $pageSize, $sortBy, $sortDirection, $organizationId, $requestTypeId)
    {
        $query = $this->getCommitteeRequestPagesQuery($organizationId, $requestTypeId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    private function getCommitteeRequestPagesQuery($organizationId, $requestTypeId)
    {
        return $this->model
            ->selectRaw("requests.id, requests.request_body, requests.organization_id, requests.request_type_id, evidence_document_id, evidence_document_url,is_approved")
        ->where("organization_id", $organizationId)
            ->where("request_type_id", $requestTypeId);
            //->whereNull("is_approved");
    }

    public function canRequestDeleteUser($id, $organizationId)
    {
        $request = $this->model
        ->where('requests.request_type_id', config('requestTypes.deleteUser'))
        ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(requests.request_body, "$.\"user_id\"")) = ?', $id)
        ->where('requests.is_approved', null)
        ->where('requests.organization_id', $organizationId)
        ->whereNull('requests.deleted_at')
        ->first();
        return $request;
    }
    public function getCommitteeRequestPagesQueryForExcel($organizationId, $requestTypeId)
    {
        return $this->model
        ->selectRaw("requests.id, requests.request_body, requests.organization_id, requests.request_type_id, evidence_document_id, evidence_document_url, is_approved, request_types.request_type_name_en")
        ->leftJoin('request_types', 'requests.request_type_id', '=', 'request_types.id')
        ->where("requests.organization_id", $organizationId)
        ->where("requests.request_type_id", $requestTypeId)
        ->get();
    }
}