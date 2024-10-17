<?php

namespace Helpers;

use Models\Approval;

class ApprovalHelper
{

    public function __construct()
    {
    }

    public function prepareApprovalMembers($membersIds)
    {
        $data = [];
        foreach ($membersIds as $memberId) {
            $data[] = ["member_id" => $memberId];
        }
        return $data;
    }

    public function prepareApprovalAttachmentAssigneesOnCreate($membersIds, $approvalId)
    {
        $data = [];
        foreach ($membersIds as $memberId) {
            $data[] = [
                "user_id" => $memberId,
                "status_id" => config("approvalStatuses.awaiting", 2),
                "approval_id" => $approvalId
            ];
        }
        return $data;
    }

    public function mapApprovalData(Approval $approval, $members)
    {
        $data = $approval->toArray();
        $data["members"] = $members;
        $data["created_by_obj"] = $approval->approvalSender;
        return $data;
    }

    public function prepareApprovalMembersUpdate($approvalMembers)
    {
        $data = [];
        foreach ($approvalMembers as $approvalMember) {
            $data[] = [
                "id" => $approvalMember['id'],
                "signature_x_upper_left" => $approvalMember['x'],
                "signature_y_upper_left" => $approvalMember['y'],
                "signature_page_number" => $approvalMember['slide']
            ];
        }

        return $data;
    }
}
