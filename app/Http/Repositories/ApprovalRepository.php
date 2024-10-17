<?php

namespace Repositories;

class ApprovalRepository extends BaseRepository
{


    public function model()
    {
        return 'Models\Approval';
    }

    public function getApprovalsPagedList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId, $userId)
    {
        $query = $this->getApprovalsQuery($searchObj, $organizationId, $userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    private function getApprovalsQuery($searchObj, $organizationId, $userId)
    {
        if (isset($searchObj->status_id)) {
            $this->model = $this->model->where('approvals.status_id', $searchObj->status_id);
        }

        if (isset($searchObj->committee_id)) {
            $this->model = $this->model->where('approvals.committee_id', $searchObj->committee_id);
        }

        if (isset($searchObj->includeMeetingApprovals) && !$searchObj->includeMeetingApprovals) {
            $this->model = $this->model->where('approvals.meeting_id', null);
        }

        if (isset($searchObj->approval_title)) {
            $this->model = $this->model->whereRaw('approvals.approval_title LIKE "%' . $searchObj->approval_title . '%"');
        }

        return $this->model->selectRaw(
            "DISTINCT approvals.id, approvals.created_by, approvals.approval_title, approvals.committee_id, approvals.status_id,
                users.name_ar AS creator_name_ar,users.name AS creator_name, 
                approval_statuses.approval_status_name_ar, approval_statuses.approval_status_name_en,
                DATE_FORMAT(approvals.created_at, '%d/%m/%Y') AS created_at_formatted,
                DATE_FORMAT(approvals.updated_at, '%d/%m/%Y') AS updated_at_formatted
                "
        )
            ->join('approval_statuses', 'approval_statuses.id', 'approvals.status_id')
            ->join('users', 'users.id', 'approvals.created_by')
        ->where('users.organization_id', $organizationId);
    }

    public function getMeetingApprovals($meetingId)
    {
        return $this->model
        ->select('approvals.*')
        ->with(['members' => function ($query) {
            $query->selectRaw( "approval_members.member_id,approval_members.approval_id,
            approval_members.id, users.name, users.name_ar, approval_members.signature_x_upper_left,
            approval_members.signature_y_upper_left, approval_members.signature_page_number,
            approval_members.is_signed, approval_members.signature_comment, approval_members.updated_at")
            ->join('users', 'users.id', 'approval_members.member_id');
        }])
        ->where('approvals.meeting_id', $meetingId)
        ->get();
    }

    public function getApprovalBySignatureDocumentId($documentId) {
        return $this->model->where('signature_document_id', $documentId)->orderBy('id', 'desc')->first();
    }

}
